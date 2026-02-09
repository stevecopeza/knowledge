<?php

namespace Knowledge\Service\Annotation;

class AnnotationService {

	private string $storage_dir;

	public function __construct() {
		$this->storage_dir = KNOWLEDGE_DATA_PATH . '/annotations';
	}

	/**
	 * Save annotation target data to filesystem.
	 *
	 * @param int   $note_id The WP Post ID of the note.
	 * @param array $target  The target data (selector, source, etc.).
	 * @return bool True on success.
	 */
	public function save_target( int $note_id, array $target ): bool {
		// Check if UUID exists
		$uuid = get_post_meta( $note_id, '_kb_annotation_uuid', true );
		if ( ! $uuid ) {
			$uuid = wp_generate_uuid4();
			update_post_meta( $note_id, '_kb_annotation_uuid', $uuid );
		}

		// Save source to meta for querying
		if ( isset( $target['source'] ) ) {
			update_post_meta( $note_id, '_kb_note_source', $target['source'] );
		}

		$data = [
			'id'         => $uuid,
			'note_id'    => $note_id,
			'target'     => $target,
			'updated_at' => current_time( 'mysql' ),
		];

		$file = $this->storage_dir . '/' . $uuid . '.json';
		return (bool) file_put_contents( $file, wp_json_encode( $data, JSON_PRETTY_PRINT ) );
	}

	/**
	 * Get annotation target data.
	 *
	 * @param int $note_id The WP Post ID.
	 * @return array|null The target data or null if not found.
	 */
	public function get_target( int $note_id ): ?array {
		$uuid = get_post_meta( $note_id, '_kb_annotation_uuid', true );
		if ( ! $uuid ) {
			return null;
		}

		$file = $this->storage_dir . '/' . $uuid . '.json';
		if ( ! file_exists( $file ) ) {
			return null;
		}

		$data = json_decode( file_get_contents( $file ), true );
		return is_array( $data ) ? $data : null;
	}

	/**
	 * Delete annotation data.
	 *
	 * @param int $note_id The WP Post ID.
	 */
	public function delete_target( int $note_id ): void {
		$uuid = get_post_meta( $note_id, '_kb_annotation_uuid', true );
		if ( $uuid ) {
			$file = $this->storage_dir . '/' . $uuid . '.json';
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}
	}
}
