<?php

namespace Knowledge\Service\Storage;

use Knowledge\Domain\Article;
use Knowledge\Domain\Version;
use Knowledge\Domain\ValueObject\Source;

class StorageEngine {

	public function store( Source $source, string $title, string $content, array $extra_metadata = [], ?string $featured_image_path = null, int $author_id = 0 ): Version {
		$hash = md5( $content );

		// 1. Check for Existing Article
		$article_id = $this->find_existing_article( $source );
		
		if ( $article_id ) {
			// Check for duplicate content (Idempotency)
			$existing_version = $this->find_identical_version( $article_id, $hash );
			if ( $existing_version ) {
				return $existing_version;
			}
		} else {
			// Create New Article
			$article_id = $this->create_article( $title, $source, $author_id );
		}
		
		// 1.5 Handle Featured Image (if available and not set)
		if ( $featured_image_path && ! has_post_thumbnail( $article_id ) ) {
			$this->set_featured_image( $article_id, $featured_image_path );
		}

		// 2. Save to Disk (Atomic Write)
		$uuid     = wp_generate_uuid4();
		$temp_dir = KNOWLEDGE_DATA_PATH . '/temp/' . $uuid;
		$final_dir = KNOWLEDGE_DATA_PATH . '/versions/' . $uuid;
		
		if ( ! wp_mkdir_p( $temp_dir ) ) {
			throw new \RuntimeException( 'Failed to create temp directory.' );
		}

		$filename = 'content.html';
		$temp_path = $temp_dir . '/' . $filename;
		
		if ( false === file_put_contents( $temp_path, $content ) ) {
			throw new \RuntimeException( 'Failed to write version file to temp.' );
		}
		
		// Save Metadata
		$metadata = array_merge( [
			'uuid'       => $uuid,
			'source_url' => $source->get_url(),
			'hash'       => $hash,
			'created_at' => current_time( 'mysql' ),
			'title'      => $title,
		], $extra_metadata );
		
		file_put_contents( $temp_dir . '/metadata.json', wp_json_encode( $metadata, JSON_PRETTY_PRINT ) );

		// Atomic Move
		if ( ! rename( $temp_dir, $final_dir ) ) {
			throw new \RuntimeException( 'Failed to move version from temp to final storage.' );
		}

		$path = $final_dir . '/' . $filename;

		// 3. Create Version Post
		$version_args = [
			'post_title'  => "Version " . date( 'Y-m-d H:i:s' ),
			'post_type'   => 'kb_version',
			'post_status' => 'publish', // Internal status
			'post_parent' => $article_id,
			'meta_input'  => [
				'_kb_version_uuid' => $uuid,
				'_kb_source_url'   => $source->get_url(),
				'_kb_content_hash' => $hash,
				'_kb_file_path'    => $uuid . '/' . $filename,
			],
		];

		if ( $author_id > 0 ) {
			$version_args['post_author'] = $author_id;
		}

		$version_id = wp_insert_post( $version_args );

		if ( is_wp_error( $version_id ) ) {
			throw new \RuntimeException( 'Failed to create Version post.' );
		}

		// 4. Index Content (Trigger Search Indexer)
		do_action( 'kb_version_created', $uuid, $title, $content, $metadata );

		return new Version(
			$uuid,
			$article_id,
			$source,
			$title,
			$path,
			$hash
		);
	}

	private function find_existing_article( Source $source ): ?int {
		$query = new \WP_Query( [
			'post_type'  => 'kb_article',
			'meta_key'   => '_kb_source_url',
			'meta_value' => $source->get_url(),
			'fields'     => 'ids',
			'posts_per_page' => 1,
		] );

		return $query->posts[0] ?? null;
	}

	private function create_article( string $title, Source $source, int $author_id = 0 ): int {
		$args = [
			'post_title'  => $title,
			'post_type'   => 'kb_article',
			'post_status' => 'publish',
			'meta_input'  => [
				'_kb_source_url' => $source->get_url(),
			],
		];

		if ( $author_id > 0 ) {
			$args['post_author'] = $author_id;
		}

		$article_id = wp_insert_post( $args );

		if ( is_wp_error( $article_id ) ) {
			throw new \RuntimeException( 'Failed to create Article post.' );
		}

		return $article_id;
	}

	private function find_identical_version( int $article_id, string $hash ): ?Version {
		$query = new \WP_Query( [
			'post_type'      => 'kb_version',
			'post_parent'    => $article_id,
			'meta_key'       => '_kb_content_hash',
			'meta_value'     => $hash,
			'posts_per_page' => 1,
		] );

		if ( empty( $query->posts ) ) {
			return null;
		}

		$post_id = $query->posts[0]->ID;
		$uuid    = get_post_meta( $post_id, '_kb_version_uuid', true );
		$url     = get_post_meta( $post_id, '_kb_source_url', true );
		$rel_path = get_post_meta( $post_id, '_kb_file_path', true );
		$path    = KNOWLEDGE_DATA_PATH . '/versions/' . $rel_path;
		$title   = get_the_title( $article_id ); // Use Article title

		return new Version(
			$uuid,
			$article_id,
			new Source( $url ),
			$title,
			$path,
			$hash
		);
	}

	private function set_featured_image( int $article_id, string $file_path ): void {
		if ( ! file_exists( $file_path ) ) {
			return;
		}

		// Read file content
		$file_content = file_get_contents( $file_path );
		$filename     = basename( $file_path );

		// Use WordPress native upload mechanism to sideload into Media Library
		$upload = wp_upload_bits( $filename, null, $file_content );

		if ( ! empty( $upload['error'] ) ) {
			error_log( 'StorageEngine: Failed to upload featured image: ' . $upload['error'] );
			return;
		}

		$wp_filetype = wp_check_filetype( $filename, null );
		$attachment = [
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		];

		$attach_id = wp_insert_attachment( $attachment, $upload['file'], $article_id );

		if ( is_wp_error( $attach_id ) ) {
			error_log( 'StorageEngine: Failed to create attachment: ' . $attach_id->get_error_message() );
			return;
		}

		// Generate attachment metadata (thumbnails, etc.)
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		// Set as Featured Image
		set_post_thumbnail( $article_id, $attach_id );
		error_log( "StorageEngine: Set featured image for Article $article_id (Attach ID: $attach_id)" );
	}
}
