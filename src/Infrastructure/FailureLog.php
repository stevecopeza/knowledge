<?php

namespace Knowledge\Infrastructure;

class FailureLog {
	private const OPTION_KEY = 'knowledge_ingestion_failures';
	private const MAX_LOGS   = 50;

	/**
	 * Log a failed ingestion attempt.
	 *
	 * @param string $url   The URL that failed.
	 * @param string $error The error message.
	 */
	public static function log( string $url, string $error ): void {
		$failures = get_option( self::OPTION_KEY, [] );
		if ( ! is_array( $failures ) ) {
			$failures = [];
		}

		// Prepend new failure
		array_unshift( $failures, [
			'id'        => wp_generate_uuid4(),
			'url'       => $url,
			'error'     => $error,
			'timestamp' => time(),
		] );

		// Trim to max logs
		if ( count( $failures ) > self::MAX_LOGS ) {
			$failures = array_slice( $failures, 0, self::MAX_LOGS );
		}

		update_option( self::OPTION_KEY, $failures, false );
	}

	/**
	 * Get all recorded failures.
	 *
	 * @return array
	 */
	public static function get_failures(): array {
		$failures = get_option( self::OPTION_KEY, [] );
		return is_array( $failures ) ? $failures : [];
	}

	/**
	 * Dismiss a specific failure log.
	 *
	 * @param string $id Failure ID.
	 */
	public static function dismiss( string $id ): void {
		$failures = self::get_failures();
		foreach ( $failures as $key => $failure ) {
			if ( isset( $failure['id'] ) && $failure['id'] === $id ) {
				unset( $failures[ $key ] );
				break;
			}
		}
		update_option( self::OPTION_KEY, array_values( $failures ), false );
	}
	
	/**
	 * Clear all failure logs.
	 */
	public static function clear_all(): void {
		delete_option( self::OPTION_KEY );
	}
}
