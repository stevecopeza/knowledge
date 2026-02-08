<?php

namespace Knowledge\Service\Forking;

class ForkService {

	public function create_fork( int $version_id ): int {
		$version = get_post( $version_id );
		if ( ! $version || 'kb_version' !== $version->post_type ) {
			throw new \InvalidArgumentException( 'Invalid Version ID.' );
		}

		$version_uuid = get_post_meta( $version_id, '_kb_version_uuid', true );
		if ( ! $version_uuid ) {
			throw new \RuntimeException( 'Version missing UUID.' );
		}

		$file_path = KNOWLEDGE_DATA_PATH . '/versions/' . $version_uuid . '/content.html';
		if ( ! file_exists( $file_path ) ) {
			throw new \RuntimeException( 'Version content file not found.' );
		}

		$content = file_get_contents( $file_path );
		$title   = 'Fork of ' . $version->post_title;

		// Create Fork Post
		$fork_id = wp_insert_post( [
			'post_title'   => $title,
			'post_content' => $content, // Load content into editor
			'post_type'    => 'kb_fork',
			'post_status'  => 'draft',
			'meta_input'   => [
				'_kb_parent_version_id'   => $version_id,
				'_kb_parent_version_uuid' => $version_uuid,
			],
		] );

		if ( is_wp_error( $fork_id ) ) {
			throw new \RuntimeException( 'Failed to create Fork post.' );
		}

		return $fork_id;
	}

	public function save_fork_to_disk( int $fork_id ): void {
		$fork = get_post( $fork_id );
		if ( ! $fork ) {
			return;
		}

		// Generate UUID if missing
		$uuid = get_post_meta( $fork_id, '_kb_fork_uuid', true );
		if ( ! $uuid ) {
			$uuid = wp_generate_uuid4();
			update_post_meta( $fork_id, '_kb_fork_uuid', $uuid );
		}

		$dir = KNOWLEDGE_DATA_PATH . '/forks/' . $uuid;
		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		// Write content
		file_put_contents( $dir . '/content.html', $fork->post_content );
		
		// Write metadata
		$metadata = [
			'uuid' => $uuid,
			'parent_version_uuid' => get_post_meta( $fork_id, '_kb_parent_version_uuid', true ),
			'updated_at' => current_time( 'mysql' ),
		];
		file_put_contents( $dir . '/metadata.json', wp_json_encode( $metadata, JSON_PRETTY_PRINT ) );
	}
}
