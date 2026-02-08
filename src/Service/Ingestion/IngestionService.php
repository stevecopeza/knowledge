<?php

namespace Knowledge\Service\Ingestion;

use Knowledge\Domain\ValueObject\Source;
use Knowledge\Domain\Version;
use Knowledge\Service\Storage\StorageEngine;

class IngestionService {
	private HtmlFetcher $fetcher;
	private ContentNormalizer $normalizer;
	private AssetDownloader $asset_downloader;
	private StorageEngine $storage;

	public function __construct() {
		$this->fetcher          = new HtmlFetcher();
		$this->normalizer       = new ContentNormalizer();
		$this->asset_downloader = new AssetDownloader();
		$this->storage          = new StorageEngine();
	}

	public function ingest_url( string $url ): Version {
		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 300 ); // 5 minutes
		}

		error_log( "IngestionService: Starting ingestion for URL: $url" );

		$source = new Source( $url );

		// 1. Fetch
		error_log( "IngestionService: Fetching HTML..." );
		$raw_html = $this->fetcher->fetch( $source );
		error_log( "IngestionService: Fetched HTML length: " . strlen( $raw_html ) );

		// 2. Normalize
		error_log( "IngestionService: Normalizing content..." );
		$data    = $this->normalizer->normalize( $raw_html );
		$title   = $data['title'];
		$content = $data['content'];
		error_log( "IngestionService: Content normalized. Title: $title" );

		// 3. Download Assets (Images)
		error_log( "IngestionService: Downloading assets..." );
		$content = $this->asset_downloader->download_and_replace( $content, $url );
		error_log( "IngestionService: Assets downloaded." );

		// 4. Store
		error_log( "IngestionService: Storing version..." );
		$featured_image = $this->asset_downloader->get_featured_image_candidate();
		
		$version = $this->storage->store( $source, $title, $content, $data['metadata'] ?? [], $featured_image );
		error_log( "IngestionService: Version stored. UUID: " . $version->get_uuid() );
		
		// 5. Schedule AI Analysis
		if ( function_exists( 'wp_schedule_single_event' ) ) {
			$result = wp_schedule_single_event( time(), 'knowledge_ai_analyze_article', [ $version->get_uuid(), $version->get_article_id() ] );
			if ( $result ) {
				error_log( "IngestionService: AI Analysis scheduled successfully for Version UUID: " . $version->get_uuid() );
			} else {
				error_log( "IngestionService: Failed to schedule AI Analysis for Version UUID: " . $version->get_uuid() );
			}
		} else {
			error_log( "IngestionService: wp_schedule_single_event function not found." );
		}
		
		return $version;
	}
}
