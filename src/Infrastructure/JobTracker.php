<?php

namespace Knowledge\Infrastructure;

class JobTracker {
	private const OPTION_KEY = 'knowledge_active_jobs';
	private const TIMEOUT    = 600; // 10 minutes

	/**
	 * Mark a job as started.
	 *
	 * @param string $job_id      Unique identifier for the job.
	 * @param string $type        Type of job (e.g., 'Ingestion', 'Embedding').
	 * @param string $description Human-readable description.
	 */
	public static function start( string $job_id, string $type, string $description ): void {
		$jobs = get_option( self::OPTION_KEY, [] );
		if ( ! is_array( $jobs ) ) {
			$jobs = [];
		}

		// Clean stale jobs while we are here
		$jobs = self::cleanup( $jobs );

		$jobs[ $job_id ] = [
			'type'        => $type,
			'description' => $description,
			'start_time'  => time(),
		];

		update_option( self::OPTION_KEY, $jobs, false ); // autoload=false
	}

	/**
	 * Mark a job as completed.
	 *
	 * @param string $job_id Unique identifier for the job.
	 */
	public static function complete( string $job_id ): void {
		$jobs = get_option( self::OPTION_KEY, [] );
		if ( ! is_array( $jobs ) ) {
			return;
		}

		if ( isset( $jobs[ $job_id ] ) ) {
			unset( $jobs[ $job_id ] );
			update_option( self::OPTION_KEY, $jobs, false );
		}
	}

	/**
	 * Get list of currently active jobs.
	 *
	 * @return array
	 */
	public static function get_active_jobs(): array {
		$jobs = get_option( self::OPTION_KEY, [] );
		if ( ! is_array( $jobs ) ) {
			return [];
		}
		// Return cleaned list (and save cleanup if needed)
		return self::cleanup( $jobs );
	}

	/**
	 * Remove stale jobs.
	 *
	 * @param array $jobs List of jobs.
	 * @return array Cleaned list.
	 */
	private static function cleanup( array $jobs ): array {
		$now     = time();
		$changed = false;
		foreach ( $jobs as $id => $job ) {
			if ( $now - ( $job['start_time'] ?? 0 ) > self::TIMEOUT ) {
				unset( $jobs[ $id ] );
				$changed = true;
			}
		}

		if ( $changed ) {
			update_option( self::OPTION_KEY, $jobs, false );
		}

		return $jobs;
	}
}
