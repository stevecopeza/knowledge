<?php

namespace Knowledge\Service\AI;

use Knowledge\Domain\Version;
use Knowledge\Domain\ValueObject\Source;

class EmbeddingJob {

	/**
	 * Schedule the embedding job.
	 *
	 * @param string $uuid    Version UUID.
	 * @param string $title   Title (unused here, passed by hook).
	 * @param string $content Content (unused here).
	 * @param array  $metadata Metadata.
	 */
	public static function schedule( string $uuid, string $title, string $content, array $metadata ): void {
		if ( ! wp_next_scheduled( 'knowledge_generate_embeddings', [ $uuid ] ) ) {
			wp_schedule_single_event( time(), 'knowledge_generate_embeddings', [ $uuid ] );
		}
	}

	/**
	 * Process the embedding job.
	 *
	 * @param string $uuid Version UUID.
	 */
	public static function process( string $uuid ): void {
		try {
			// 1. Reconstruct Version from Filesystem
			$version_dir = KNOWLEDGE_DATA_PATH . '/versions/' . $uuid;
			if ( ! file_exists( $version_dir ) ) {
				throw new \RuntimeException( "Version directory not found: $version_dir" );
			}

			// Track Job
			$job_id = 'embedding_' . $uuid;
			\Knowledge\Infrastructure\JobTracker::start( $job_id, 'Embedding Generation', "Generating embeddings for Version $uuid" );

			try {
				$meta_path = $version_dir . '/metadata.json';
				if ( ! file_exists( $meta_path ) ) {
					throw new \RuntimeException( "Version metadata not found: $meta_path" );
				}
	
				$metadata = json_decode( file_get_contents( $meta_path ), true );
				if ( ! is_array( $metadata ) ) {
					throw new \RuntimeException( "Invalid metadata for version: $uuid" );
				}
	
				$source_url = $metadata['source_url'] ?? '';
				$title      = $metadata['title'] ?? 'Untitled';
				$hash       = $metadata['hash'] ?? '';
				
				// Note: We don't easily have article_id here without DB lookup, 
				// but EmbeddingGenerator doesn't need it. We pass 0.
				$version = new Version(
					$uuid,
					0,
					new Source( $source_url ),
					$title,
					$version_dir . '/content.html',
					$hash
				);
	
				// 2. Initialize Services
				$client  = new OllamaClient();
				
				if ( ! $client->is_available() ) {
					error_log( "EmbeddingJob: Ollama is not available. Skipping embedding for $uuid." );
					return;
				}
	
				$chunker = new ChunkingService();
				$store   = new VectorStore();
				$gen     = new EmbeddingGenerator( $client, $chunker );
	
				// 3. Generate & Save
				error_log( "EmbeddingJob: Generating embeddings for $uuid..." );
				$data = $gen->generate_for_version( $version );
				
				$store->save( $data );
				
				error_log( "EmbeddingJob: Successfully saved embeddings for $uuid." );

			} finally {
				\Knowledge\Infrastructure\JobTracker::complete( $job_id );
			}

		} catch ( \Exception $e ) {
			error_log( "EmbeddingJob Error for $uuid: " . $e->getMessage() );
			// Ideally reschedule with backoff, but for MVP we log and fail.
		}
	}
}
