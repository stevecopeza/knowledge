<?php

namespace Knowledge\Service\Ingestion;

use DOMDocument;
use DOMXPath;

class ContentNormalizer {
	public function normalize( string $html ): array {
		error_log( "ContentNormalizer: Starting normalization. HTML length: " . strlen( $html ) );
		if ( empty( $html ) ) {
			return [ 'title' => 'Untitled', 'content' => '' ];
		}

		$dom = new DOMDocument();
		// Suppress warnings for malformed HTML
		libxml_use_internal_errors( true );
		error_log( "ContentNormalizer: Loading HTML..." );
		// Hack to force UTF-8 encoding for DOMDocument without using deprecated mb_convert_encoding
		if ( strpos( $html, '<?xml encoding' ) === false ) {
			$html = '<?xml encoding="UTF-8">' . $html;
		}
		$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		error_log( "ContentNormalizer: HTML Loaded." );
		libxml_clear_errors();

		$xpath = new DOMXPath( $dom );

		// Extract Title
		$title_node = $xpath->query( '//title' )->item( 0 );
		$title      = $title_node ? $title_node->textContent : 'Untitled';

		// Remove unwanted tags
		$removals = $xpath->query( '//script | //style | //noscript | //iframe | //header | //footer | //nav | //aside | //form | //object | //embed' );
		foreach ( $removals as $node ) {
			$node->parentNode->removeChild( $node );
		}

		// Remove elements with common noise classes/IDs
		$noise_patterns = [
			'share', 'social', 'related', 'comment', 'sidebar', 'advert', 'promo', 'newsletter', 
			'cookie', 'copyright', 'popup', 'banner', 'widget', 'navigation', 'menu', 'outbrain', 'taboola'
		];
		
		$conditions = [];
		foreach ( $noise_patterns as $pattern ) {
			// Match class or id containing the pattern (case-insensitive simulation not easy in pure XPath 1.0 without php functions, 
			// so we assume standard lowercase/hyphenated naming)
			$conditions[] = "contains(@class, '$pattern')";
			$conditions[] = "contains(@id, '$pattern')";
		}
		$noise_xpath = "//*[" . implode( ' or ', $conditions ) . "]";
		
		$noise_nodes = $xpath->query( $noise_xpath );
		foreach ( $noise_nodes as $node ) {
			// Protect root and main content elements from accidental deletion
			if ( in_array( $node->nodeName, [ 'body', 'html', 'article', 'main' ] ) ) {
				continue;
			}
			// Verify it's still in the DOM (might have been removed as child of another removed node)
			if ( ! $node->parentNode ) {
				continue;
			}
			
			if ( ! $node instanceof \DOMElement ) {
				continue;
			}

			// Safety Check: Don't remove nodes with significant text content unless they are clearly comments/ads.
			// This prevents deleting content wrappers that happen to have classes like 'has-sidebar' or 'with-banner'.
			$text_length = strlen( trim( $node->textContent ) );
			$class_id    = $node->getAttribute( 'class' ) . ' ' . $node->getAttribute( 'id' );
			
			// High confidence noise (always delete)
			$is_safe_to_delete = false;
			if ( 
				stripos( $class_id, 'comment' ) !== false || 
				stripos( $class_id, 'outbrain' ) !== false || 
				stripos( $class_id, 'taboola' ) !== false ||
				stripos( $class_id, 'cookie' ) !== false
			) {
				$is_safe_to_delete = true;
			}

			// If it has lots of text and isn't high-confidence noise, preserve it.
			// 300 chars is roughly 40-50 words. Banners/Menus are usually shorter.
			if ( ! $is_safe_to_delete && $text_length > 300 ) {
				continue;
			}

			$node->parentNode->removeChild( $node );
		}

		// Extract Content
		// Priority: Select the candidate with the most text content from <article> or <main>.
		// Fallback to <body> if no candidates found.
		$candidates = [];
		
		$articles = $dom->getElementsByTagName( 'article' );
		foreach ( $articles as $node ) {
			$candidates[] = $node;
		}
		
		$mains = $dom->getElementsByTagName( 'main' );
		foreach ( $mains as $node ) {
			$candidates[] = $node;
		}

		// Fallback: Look for divs with common content IDs/Classes
		// This is necessary for sites (like Migrationology) that don't use <article> or <main>
		$common_ids = [ 'content', 'main', 'main-content', 'primary', 'post-content', 'entry-content' ];
		foreach ( $common_ids as $id ) {
			$node = $dom->getElementById( $id );
			if ( $node ) {
				$candidates[] = $node;
			}
		}

		$common_classes = [ 'entry-content', 'post-content', 'article-content', 'post-body', 'page-section' ];
		foreach ( $common_classes as $class ) {
			$nodes = $xpath->query( "//div[contains(concat(' ', normalize-space(@class), ' '), ' $class ')]" );
			foreach ( $nodes as $node ) {
				$candidates[] = $node;
			}
		}
		
		error_log( "ContentNormalizer: Found " . count( $candidates ) . " candidates." );

		$best_node = null;
		$max_length = 0;

		foreach ( $candidates as $node ) {
			$length = strlen( trim( $node->textContent ) );
			if ( $length > $max_length ) {
				$max_length = $length;
				$best_node = $node;
			}
		}

		if ( ! $best_node ) {
			error_log( "ContentNormalizer: No best node found. Fallback to body." );
			$best_node = $dom->getElementsByTagName( 'body' )->item( 0 );
		}
		
		if ( ! $best_node ) {
			error_log( "ContentNormalizer: No content found even in body." );
			return [ 'title' => $title, 'content' => '' ];
		}

		// Minimum Viability Check
		$text_content = trim( $best_node->textContent );
		$word_count   = str_word_count( $text_content );
		$p_count      = $xpath->query( './/p', $best_node )->length;

		error_log( "ContentNormalizer: Selected Best Node. Words: $word_count, Paragraphs: $p_count" );

		// Thresholds: 200 words OR 3 paragraphs (lenient to allow short updates, but strict enough to block empty shells)
		// Actually, let's require BOTH some substance. 
		// "If extracted content does not meet minimum thresholds (length, paragraph count)... normalisation fails."
		// Let's say < 100 words is definitely fail. < 2 paragraphs is fail.
		if ( $word_count < 100 || $p_count < 2 ) {
			error_log( "ContentNormalizer: Failed Minimum Viability." );
			throw new \Exception( "Ingestion Failed: Content too short ($word_count words, $p_count paragraphs). Minimum requirements not met." );
		}

		// Save cleaned HTML
		$content = $dom->saveHTML( $best_node );

		// Extract Metadata
		$metadata = [
			'author'      => $this->get_meta_content( $xpath, 'name', 'author' ),
			'description' => $this->get_meta_content( $xpath, 'name', 'description' ) ?? $this->get_meta_content( $xpath, 'property', 'og:description' ),
			'published'   => $this->get_meta_content( $xpath, 'property', 'article:published_time' ),
			'image'       => $this->get_meta_content( $xpath, 'property', 'og:image' ),
		];
		
		error_log( "ContentNormalizer: Normalization complete." );

		return [
			'title'    => trim( $title ),
			'content'  => $content,
			'metadata' => $metadata,
		];
	}

	private function get_meta_content( DOMXPath $xpath, string $attr, string $value ): ?string {
		$nodes = $xpath->query( "//meta[@{$attr}='{$value}']" );
		if ( $nodes->length > 0 ) {
			$node = $nodes->item( 0 );
			if ( $node instanceof \DOMElement ) {
				return $node->getAttribute( 'content' );
			}
		}
		return null;
	}
}
