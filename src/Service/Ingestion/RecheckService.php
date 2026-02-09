<?php

namespace Knowledge\Service\Ingestion;

class RecheckService {

	/**
	 * Handle AJAX request for rechecking an article.
	 */
	public static function handle_ajax(): void {
		check_ajax_referer( 'knowledge_recheck_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$url = isset( $_POST['url'] ) ? esc_url_raw( $_POST['url'] ) : '';

		if ( empty( $url ) ) {
			wp_send_json_error( 'Invalid URL.' );
		}

		$result = self::prioritize_url( $url, get_current_user_id() );

		if ( $result['status'] === 'moved' ) {
			wp_send_json_success( 'Moved to top of queue.' );
		} else {
			wp_send_json_success( 'Added to top of queue.' );
		}
	}

	/**
	 * Prioritize a URL in the ingestion queue.
	 *
	 * @param string $url The URL to recheck.
	 * @param int $user_id The user requesting the recheck.
	 * @return array Result status.
	 */
	public static function prioritize_url( string $url, int $user_id ): array {
		$crons = _get_cron_array();
		$found_key = false;
		$found_timestamp = 0;
		$found_args = [];

		// 1. Search existing cron events
		if ( ! empty( $crons ) ) {
			foreach ( $crons as $timestamp => $cronhooks ) {
				foreach ( $cronhooks as $hook => $keys ) {
					if ( $hook === 'knowledge_async_ingest' ) {
						foreach ( $keys as $k => $data ) {
							// $data['args'][0] is the URL
							if ( isset( $data['args'][0] ) && $data['args'][0] === $url ) {
								$found_key = $k;
								$found_timestamp = $timestamp;
								$found_args = $data['args'];
								break 3; // Stop searching
							}
						}
					}
				}
			}
		}

		if ( $found_key !== false ) {
			// 2. If found, unschedule and reschedule for NOW
			wp_unschedule_event( $found_timestamp, 'knowledge_async_ingest', $found_args );
			
			// Reschedule immediately (time() - 1 ensures it's in the past/immediate)
			wp_schedule_single_event( time(), 'knowledge_async_ingest', $found_args );
			
			return [ 'status' => 'moved' ];
		}

		// 3. If not found, schedule new
		// We use $user_id and job_id=0 (manual)
		wp_schedule_single_event( time(), 'knowledge_async_ingest', [ $url, $user_id, 0 ] );

		return [ 'status' => 'added' ];
	}
}
