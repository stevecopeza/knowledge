<?php

namespace Knowledge\Service\Ingestion;

use Knowledge\Infrastructure\FileProxyController;
use DOMDocument;
use DOMXPath;

class AssetDownloader {

	private ?string $featured_image_candidate = null;
	private float $best_candidate_score = -1.0;

	public function get_featured_image_candidate(): ?string {
		return $this->featured_image_candidate;
	}

	public function download_and_replace( string $html, string $source_url ): string {
		$this->featured_image_candidate = null;
		$this->best_candidate_score = -1.0;

		if ( empty( $html ) ) {
			return '';
		}

		$dom = new DOMDocument();
		// Suppress warnings for malformed HTML
		libxml_use_internal_errors( true );
		// Hack to force UTF-8
		$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );
		libxml_clear_errors();

		$xpath  = new DOMXPath( $dom );
		$images = $xpath->query( '//img' );

		if ( $images->length === 0 ) {
			error_log( "AssetDownloader: No images found." );
			return $html;
		}

		error_log( "AssetDownloader: Found " . $images->length . " images." );

		foreach ( $images as $index => $img ) {
			if ( ! $img instanceof \DOMElement ) {
				continue;
			}
			
			if ( $index % 5 === 0 ) {
				error_log( "AssetDownloader: Processing image #$index" );
			}

			$src = $img->getAttribute( 'src' );
			
			// Handle Lazy Loading
			// Check for empty, data URI, base64, or placeholder
			if ( empty( $src ) || strpos( $src, 'data:' ) === 0 || strpos( $src, 'base64' ) !== false || strpos( $src, 'placeholder' ) !== false ) {
				$candidate = $img->getAttribute( 'data-src' );
				if ( ! empty( $candidate ) ) {
					$src = $candidate;
				} else {
					$candidate = $img->getAttribute( 'data-lazy-src' );
					if ( ! empty( $candidate ) ) {
						$src = $candidate;
					}
				}
			}

			// If still a data URI after checking candidates, skip it (it's likely an inline placeholder or icon)
			if ( strpos( $src, 'data:' ) === 0 ) {
				if ( $index % 5 === 0 ) {
					error_log( "AssetDownloader: Skipping data URI for image #$index" );
				}
				continue;
			}

			if ( empty( $src ) ) {
				continue;
			}

			// Remove srcset to prevent browser from loading external images
			$img->removeAttribute( 'srcset' );
			$img->removeAttribute( 'sizes' );

			// Resolve relative URLs
			$absolute_url = $this->resolve_url( $src, $source_url );

			$img->setAttribute('data-debug-src', $src);
			$img->setAttribute('data-debug-resolved', $absolute_url);

			// Download Image
			$image_data = $this->fetch_image_with_error( $absolute_url, $img );
			if ( ! $image_data ) {
				continue;
			}

			// Hash & Save
			$hash      = md5( $image_data['content'] );
			$ext       = $this->get_extension( $image_data['mime'] );
			$filename  = $hash . $ext;
			$media_dir = KNOWLEDGE_DATA_PATH . '/media';
			$file_path = $media_dir . '/' . $filename;

			if ( ! file_exists( $media_dir ) ) {
				wp_mkdir_p( $media_dir );
			}

			if ( ! file_exists( $file_path ) ) {
				file_put_contents( $file_path, $image_data['content'] );
			}
			
			// Intelligent Featured Image Selection
			$score = $this->calculate_image_score( $file_path, $img, $index );
			if ( $score > $this->best_candidate_score ) {
				$this->best_candidate_score = $score;
				$this->featured_image_candidate = $file_path;
				error_log( "AssetDownloader: New featured candidate: $filename (Score: $score)" );
			}

			// Replace SRC with Proxy URL
			// Path relative to KNOWLEDGE_DATA_PATH
			$proxy_url = FileProxyController::get_url( 'media/' . $filename );
			$img->setAttribute( 'src', $proxy_url );
			$img->setAttribute( 'data-original-src', $absolute_url );
		}

		return $dom->saveHTML( $dom->getElementsByTagName( 'body' )->item( 0 ) );
	}

	private function resolve_url( string $src, string $base_url ): string {
		if ( parse_url( $src, PHP_URL_SCHEME ) != '' ) {
			return $src;
		}
		// Simple relative resolution (MVP)
		return rtrim( $base_url, '/' ) . '/' . ltrim( $src, '/' );
	}

	private function fetch_image_with_error( string $url, \DOMElement $img ): ?array {
		$response = wp_remote_get( $url, [
			'timeout'   => 30,
			'sslverify' => false,
			'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
			'headers'     => [
				'Accept'             => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
				'Accept-Language'    => 'en-US,en;q=0.9',
				'Sec-Ch-Ua'          => '"Chromium";v="122", "Not(A:Brand";v="24", "Google Chrome";v="122"',
				'Sec-Ch-Ua-Mobile'   => '?0',
				'Sec-Ch-Ua-Platform' => '"macOS"',
				'Sec-Fetch-Dest'     => 'image',
				'Sec-Fetch-Mode'     => 'no-cors',
				'Sec-Fetch-Site'     => 'cross-site',
			],
		] );

		if ( is_wp_error( $response ) ) {
			$img->setAttribute('data-download-error', $response->get_error_message());
			return null;
		}

		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$img->setAttribute('data-download-error', 'HTTP ' . wp_remote_retrieve_response_code( $response ));
			return null;
		}

		$content = wp_remote_retrieve_body( $response );
		$mime    = wp_remote_retrieve_header( $response, 'content-type' );

		return [
			'content' => $content,
			'mime'    => $mime,
		];
	}

	private function fetch_image( string $url ): ?array {
        // Deprecated, use fetch_image_with_error
        return null;
    }

	private function calculate_image_score( string $file_path, \DOMElement $img, int $index ): float {
		if ( ! file_exists( $file_path ) ) {
			return 0.0;
		}

		$size = @getimagesize( $file_path );
		if ( ! $size ) {
			return 0.0;
		}

		$width  = $size[0];
		$height = $size[1];
		$area   = $width * $height;

		// 1. Minimum Size Threshold (skip tiny icons/tracking pixels)
		if ( $width < 200 || $height < 150 ) {
			return 0.0;
		}

		$score = (float) $area;

		// 2. Square/Avatar Penalty
		// Avatars are often square (1:1). Penalize if aspect ratio is close to 1.
		// Allow some tolerance (e.g., 0.9 to 1.1)
		$aspect_ratio = $width / $height;
		if ( $aspect_ratio >= 0.9 && $aspect_ratio <= 1.1 ) {
			$score *= 0.1; // Heavy penalty for square images
		}

		// 3. Portrait Penalty (Optional)
		// Extremely tall images might be sidebars or infographics, but let's just slightly penalize
		if ( $height > $width * 2 ) {
			$score *= 0.5;
		}

		// 4. Keyword Penalty
		// Check src, class, alt for "avatar", "logo", "icon", "user", "author"
		$keywords = [ 'avatar', 'logo', 'icon', 'user', 'author', 'profile' ];
		$haystack = strtolower( $img->getAttribute( 'src' ) . ' ' . $img->getAttribute( 'alt' ) . ' ' . $img->getAttribute( 'class' ) );
		
		foreach ( $keywords as $keyword ) {
			if ( strpos( $haystack, $keyword ) !== false ) {
				$score *= 0.0; // Kill it immediately
				break;
			}
		}

		// 5. Position Weight (Tie-breaker)
		// Earlier images are slightly better, but size dominates.
		// Subtracting index ensures that if two images are identical size, the first one wins.
		$score -= $index;

		return max( 0.0, $score );
	}

	private function get_extension( string $mime ): string {
		$map = [
			'image/jpeg' => '.jpg',
			'image/png'  => '.png',
			'image/gif'  => '.gif',
			'image/webp' => '.webp',
			'image/svg+xml' => '.svg',
		];
		return $map[ $mime ] ?? '.jpg';
	}
}
