<?php

namespace Knowledge\Service\Ingestion;

use Knowledge\Domain\ValueObject\Source;
use WP_Error;

class HtmlFetcher {
	public function fetch( Source $source ): string {
		$args = [
			'timeout'     => 120, // Increased to 120s to handle slow redirects/checks
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
}
