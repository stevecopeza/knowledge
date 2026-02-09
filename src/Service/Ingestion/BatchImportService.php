<?php

namespace Knowledge\Service\Ingestion;

class BatchImportService {

	public static function process_queue(): void {
		$instance = new self();
		$dispatched = $instance->process_next_batch();

		// If items were dispatched, schedule the next batch run
		if ( $dispatched > 0 ) {
			if ( ! wp_next_scheduled( 'knowledge_process_import_queue' ) ) {
				// Schedule next batch in 2 minutes to allow current batch to process
				wp_schedule_single_event( time() + 120, 'knowledge_process_import_queue' );
			}
		}
	}

	public static function watchdog(): void {
		// Check if any jobs are processing
		$jobs = get_posts( [
			'post_type'      => 'kb_import_job',
			'post_status'    => 'processing',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		] );

		// If no processing jobs, check pending
		if ( empty( $jobs ) ) {
			$jobs = get_posts( [
				'post_type'      => 'kb_import_job',
				'post_status'    => 'pending',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			] );
		}

		if ( ! empty( $jobs ) ) {
			// If jobs exist but queue processor is NOT scheduled, restart it
			if ( ! wp_next_scheduled( 'knowledge_process_import_queue' ) ) {
				wp_schedule_single_event( time(), 'knowledge_process_import_queue' );
				error_log( 'BatchImportService Watchdog: Restarted stalled import queue.' );
			}
		}
	}

	public function create_job( array $urls, int $user_id ): int {
		// 1. Deduplicate input list (internal)
		$urls = array_values( array_unique( $urls ) );
		$total_input = count( $urls );

		// 2. Global Deduplication (Check against DB)
		$urls = $this->filter_existing_urls( $urls );
		$unique_count = count( $urls );
		$skipped_count = $total_input - $unique_count;

		if ( empty( $urls ) ) {
			throw new \Exception( "All $total_input URLs were duplicates of existing articles." );
		}

		// Create Job
		$job_id = wp_insert_post( [
			'post_type'   => 'kb_import_job',
			'post_title'  => 'Import Job - ' . date( 'Y-m-d H:i:s' ) . " ($unique_count items)",
			'post_status' => 'pending', // Use status to track lifecycle
			'post_author' => $user_id,
		] );

		if ( is_wp_error( $job_id ) ) {
			throw new \Exception( 'Failed to create import job: ' . $job_id->get_error_message() );
		}

		// Store URL list in file to avoid meta limits
		$this->save_job_data( $job_id, $urls );

		// Initialize Counters
		update_post_meta( $job_id, '_kb_import_total', $unique_count );
		update_post_meta( $job_id, '_kb_import_processed', 0 );
		update_post_meta( $job_id, '_kb_import_failed', 0 );
		update_post_meta( $job_id, '_kb_import_skipped_duplicates', $skipped_count );
		
		return $job_id;
	}

	private function filter_existing_urls( array $urls ): array {
		global $wpdb;

		if ( empty( $urls ) ) {
			return [];
		}

		// Sanitize and escape for SQL
		$placeholders = implode( ',', array_fill( 0, count( $urls ), '%s' ) );
		
		// We want to find which of these URLs ALREADY exist in postmeta
		// Note: We are checking EXACT match. Normalization (trim) should happen before this if needed.
		// Ideally, we should normalize the input $urls first.
		$normalized_urls = array_map( function($u) { 
			return rtrim( trim( $u ), '/' ); 
		}, $urls );

		$query = $wpdb->prepare( "
			SELECT meta_value 
			FROM $wpdb->postmeta 
			WHERE meta_key = '_kb_source_url' 
			AND meta_value IN ($placeholders)
		", $normalized_urls );

		$existing = $wpdb->get_col( $query );

		if ( empty( $existing ) ) {
			return $normalized_urls;
		}

		// Diff: Return only those NOT in $existing
		return array_values( array_diff( $normalized_urls, $existing ) );
	}


	public function process_next_batch( int $limit = 10 ): int {
		// Find oldest processing job first
		$jobs = get_posts( [
			'post_type'      => 'kb_import_job',
			'post_status'    => 'processing',
			'posts_per_page' => 1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'fields'         => 'ids',
		] );

		// If no processing job, find pending
		if ( empty( $jobs ) ) {
			$jobs = get_posts( [
				'post_type'      => 'kb_import_job',
				'post_status'    => 'pending',
				'posts_per_page' => 1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'fields'         => 'ids',
			] );
		}

		if ( empty( $jobs ) ) {
			return 0;
		}

		$job_id = $jobs[0];
		
		// Mark as processing if pending
		if ( get_post_status( $job_id ) === 'pending' ) {
			wp_update_post( [ 'ID' => $job_id, 'post_status' => 'processing' ] );
		}

		$data = $this->get_job_data( $job_id );
		if ( empty( $data ) ) {
			// Error: No data found, mark failed
			 wp_update_post( [ 'ID' => $job_id, 'post_status' => 'failed' ] );
			 return 0;
		}

		$processed_count = (int) get_post_meta( $job_id, '_kb_import_processed', true );
		$user_id = (int) get_post_field( 'post_author', $job_id );

		// Slice next batch
		$batch = array_slice( $data, $processed_count, $limit );
		
		if ( empty( $batch ) ) {
			// Done
			 wp_update_post( [ 'ID' => $job_id, 'post_status' => 'publish' ] ); // publish = completed
			 return 0;
		}

		$dispatched = 0;
		foreach ( $batch as $url ) {
			// Dispatch Async Ingestion
			// We pass job_id as 3rd arg to track failures
			if ( ! wp_next_scheduled( 'knowledge_async_ingest', [ $url, $user_id, $job_id ] ) ) {
				// Stagger slightly to avoid thundering herd on AI provider if concurrent
				wp_schedule_single_event( time() + $dispatched, 'knowledge_async_ingest', [ $url, $user_id, $job_id ] );
				$dispatched++;
			}
		}

		// Update Progress
		$new_processed = $processed_count + count( $batch );
		update_post_meta( $job_id, '_kb_import_processed', $new_processed );

		// If we reached the end
		if ( $new_processed >= count( $data ) ) {
			wp_update_post( [ 'ID' => $job_id, 'post_status' => 'publish' ] );
		}

		return $dispatched;
	}

	public function delete_job( int $job_id ): void {
		// Delete Data File
		$data_file = $this->get_file_path( $job_id );
		if ( file_exists( $data_file ) ) {
			unlink( $data_file );
		}

		// Delete Error Log
		$error_file = KNOWLEDGE_DATA_PATH . '/imports/job_' . $job_id . '_errors.json';
		if ( file_exists( $error_file ) ) {
			unlink( $error_file );
		}

		// Delete Post
		wp_delete_post( $job_id, true );
	}

	public function log_failure( int $job_id, string $url, string $error ): void {
		$failures = (int) get_post_meta( $job_id, '_kb_import_failed', true );
		update_post_meta( $job_id, '_kb_import_failed', $failures + 1 );

		$log_file = KNOWLEDGE_DATA_PATH . '/imports/job_' . $job_id . '_errors.json';
		
		$entry = [
			'url'       => $url,
			'error'     => $error,
			'timestamp' => time(),
		];
		
		$current_errors = [];
		if ( file_exists( $log_file ) ) {
			$current_errors = json_decode( file_get_contents( $log_file ), true ) ?: [];
		}
		$current_errors[] = $entry;
		file_put_contents( $log_file, json_encode( $current_errors, JSON_PRETTY_PRINT ) );
	}

	private function get_file_path( int $job_id ): string {
		return KNOWLEDGE_DATA_PATH . '/imports/job_' . $job_id . '.json';
	}

	private function save_job_data( int $job_id, array $data ): void {
		file_put_contents( $this->get_file_path( $job_id ), json_encode( $data ) );
	}

	private function get_job_data( int $job_id ): array {
		$path = $this->get_file_path( $job_id );
		if ( ! file_exists( $path ) ) {
			return [];
		}
		$json = file_get_contents( $path );
		return json_decode( $json, true ) ?: [];
	}
}
