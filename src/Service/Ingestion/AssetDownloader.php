<?php

namespace Knowledge\Service\Ingestion;

use Knowledge\Infrastructure\FileProxyController;
use DOMDocument;
use DOMXPath;

class AssetDownloader {

	private ?string $featured_image_candidate = null;

	public function get_featured_image_candidate(): ?string {
		return $this->featured_image_candidate;
	}

	public function download_and_replace( string $html, string $source_url ): string {
		$this->featured_image_candidate = null;

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
			
			// Capture first successful image as featured candidate
			if ( $this->featured_image_candidate === null ) {
				$this->featured_image_candidate = $file_path;
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
			'timeout'   => 15,
			'sslverify' => false,
			'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
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
