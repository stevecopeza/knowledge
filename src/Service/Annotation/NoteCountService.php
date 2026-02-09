<?php

namespace Knowledge\Service\Annotation;

class NoteCountService {

	public function init(): void {
		// Handle Note Meta Updates (Source assignment)
		add_action( 'updated_post_meta', [ $this, 'on_meta_change' ], 10, 4 );
		add_action( 'added_post_meta', [ $this, 'on_meta_change' ], 10, 4 );

		// Handle Note Deletion
		add_action( 'before_delete_post', [ $this, 'on_before_delete' ] );
	}

	/**
	 * Triggered when post meta is added or updated.
	 */
	public function on_meta_change( $meta_id, $object_id, $meta_key, $meta_value ) {
		if ( '_kb_note_source' !== $meta_key ) {
			return;
		}

		// Ensure it's a kb_note
		if ( 'kb_note' !== get_post_type( $object_id ) ) {
			return;
		}

		$this->update_article_count( (string) $meta_value );
	}

	/**
	 * Triggered before a post is deleted.
	 */
	public function on_before_delete( $post_id ) {
		if ( 'kb_note' !== get_post_type( $post_id ) ) {
			return;
		}

		$version_uuid = get_post_meta( $post_id, '_kb_note_source', true );
		if ( $version_uuid ) {
			// Exclude this post from the count
			$this->update_article_count( (string) $version_uuid, $post_id );
		}
	}

	/**
	 * Recalculate and update the note count for the article associated with the version.
	 *
	 * @param string $version_uuid The UUID of the version.
	 * @param int|null $exclude_note_id Optional note ID to exclude (for deletion).
	 */
	private function update_article_count( string $version_uuid, ?int $exclude_note_id = null ): void {
		// 1. Find the Version Post
		$version_posts = get_posts( [
			'post_type'  => 'kb_version',
			'meta_key'   => '_kb_version_uuid',
			'meta_value' => $version_uuid,
			'fields'     => 'ids',
			'numberposts' => 1,
		] );

		if ( empty( $version_posts ) ) {
			return;
		}

		$version_id = $version_posts[0];
		$article_id = wp_get_post_parent_id( $version_id );

		if ( ! $article_id ) {
			return;
		}

		// 2. Check if this is the Current Version of the Article
		$current_version_uuid = get_post_meta( $article_id, '_kb_current_version_uuid', true );

		// Only update if this is the current version
		if ( $current_version_uuid !== $version_uuid ) {
			return;
		}

		// 3. Count Notes for this Version
		$args = [
			'post_type'   => 'kb_note',
			'post_status' => [ 'publish', 'private' ], // Include private notes
			'meta_key'    => '_kb_note_source',
			'meta_value'  => $version_uuid,
			'fields'      => 'ids',
			'posts_per_page' => -1, // Count all
		];

		if ( $exclude_note_id ) {
			$args['post__not_in'] = [ $exclude_note_id ];
		}

		$query = new \WP_Query( $args );
		$count = $query->found_posts;

		// 4. Update Article Meta
		update_post_meta( $article_id, '_kb_note_count', $count );
	}
}
