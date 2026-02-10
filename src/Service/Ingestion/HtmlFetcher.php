<?php

namespace Knowledge\Service\Ingestion;

use Knowledge\Domain\ValueObject\Source;
use WP_Error;

class HtmlFetcher {
	public function fetch( Source $source ): string {
		$args = $this->get_request_args();

		$response = wp_remote_get( $source->get_url(), $args );

		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( 'Failed to fetch URL: ' . $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code !== 200 ) {
			throw new \RuntimeException( "Failed to fetch URL. HTTP Code: {$code}" );
		}

		return wp_remote_retrieve_body( $response );
	}

	public function resolve_canonical_url( Source $source ): string {
		$args = $this->get_request_args();
		$args['method'] = 'HEAD'; // Use HEAD to save bandwidth

		$response = wp_remote_head( $source->get_url(), $args );

		if ( is_wp_error( $response ) ) {
			// If HEAD fails (some servers block it), fallback to GET
			$response = wp_remote_get( $source->get_url(), $args );
		}

		if ( is_wp_error( $response ) ) {
			return $source->get_url(); // Fallback to original
		}

		// WP_Http doesn't easily expose the final URL directly in the array response
		// unless we look at the 'response' object if available, or history.
		// Standard WP `wp_remote_get` with `redirection` => 5 follows automatically.
		// But getting the *final* URL requires checking the Requests response.
		
		// For now, we rely on the fact that if we use `http_api_debug` or similar, we might see it.
		// Actually, `wp_remote_retrieve_header( $response, 'Location' )` only works if we *didn't* follow redirects.
		// But we WANT to follow them to find the destination.
		
		// Better approach: Use `wp_safe_remote_get` logic but inspect the transport?
		// No, simplest is to use `Requests` directly or checking `http_response` object if accessible.
		
		// Let's rely on a separate HEAD request that follows redirects and we check the final "effective" URL?
		// WP abstraction hides this. 
		
		// Alternative: We assume the user provided URL is the source unless we explicitly see a redirect.
		// Actually, `wp_remote_get` returns 'http_response' key which is a `WP_HTTP_Requests_Response`.
		// We can get the URL from there.
		
		$http_response = $response['http_response'] ?? null;
		if ( $http_response instanceof \WP_HTTP_Requests_Response ) {
			$raw_response = $http_response->get_response_object();
			if ( $raw_response instanceof \WpOrg\Requests\Response ) {
				return $raw_response->url;
			}
			// Older WP versions might use \Requests_Response
			if ( class_exists( '\Requests_Response' ) && is_a( $raw_response, '\Requests_Response' ) ) {
				return $raw_response->url;
			}
		}

		return $source->get_url();
	}

	private function get_request_args(): array {
		return [
			'timeout'     => 120,
			'redirection' => 5,
			'user-agent'  => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
			'headers'     => [
				'Accept'             => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
				'Accept-Language'    => 'en-US,en;q=0.9',
				'Sec-Ch-Ua'          => '"Chromium";v="122", "Not(A:Brand";v="24", "Google Chrome";v="122"',
				'Sec-Ch-Ua-Mobile'   => '?0',
				'Sec-Ch-Ua-Platform' => '"macOS"',
				'Sec-Fetch-Dest'     => 'document',
				'Sec-Fetch-Mode'     => 'navigate',
				'Sec-Fetch-Site'     => 'none',
				'Sec-Fetch-User'     => '?1',
				'Upgrade-Insecure-Requests' => '1',
			],
		];
	}
}
