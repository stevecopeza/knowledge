<?php

namespace Knowledge\Infrastructure;

use Knowledge\Service\Search\SearchIndexService;

class SearchHandler {

	private SearchIndexService $service;

	public function __construct() {
		$this->service = new SearchIndexService();
	}

	public function init(): void {
		// Indexing Hooks
		add_action( 'kb_version_created', [ $this, 'index_version' ], 10, 4 );
		add_action( 'kb_fork_updated', [ $this, 'index_fork' ], 10, 3 );

		// Search Hooks
		add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ] );
	}

	public function index_version( string $uuid, string $title, string $content, array $metadata ): void {
		$this->service->index_item( $uuid, 'version', $title, $content, $metadata );
	}

	public function index_fork( string $uuid, string $title, string $content ): void {
		$this->service->index_item( $uuid, 'fork', $title, $content );
	}

	public function pre_get_posts( \WP_Query $query ): void {
		if ( ! $query->is_search() || ! $query->is_main_query() || is_admin() ) {
			return;
		}

		$search_term = $query->get( 's' );
		if ( empty( $search_term ) ) {
			return;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'kb_search_index';
		
		// Find matching UUIDs using FULLTEXT
		// Note: We use boolean mode for simplicity
		$uuids = $wpdb->get_col( $wpdb->prepare(
			"SELECT object_uuid FROM $table_name WHERE MATCH(content, title) AGAINST(%s IN BOOLEAN MODE) LIMIT 100",
			$search_term
		) );

		if ( empty( $uuids ) ) {
			return;
		}

		// Map UUIDs to Post IDs
		// Version UUID -> _kb_version_uuid
		// Fork UUID -> _kb_fork_uuid
		
		$post_ids = $wpdb->get_col( "
			SELECT post_id FROM {$wpdb->postmeta} 
			WHERE (meta_key = '_kb_version_uuid' OR meta_key = '_kb_fork_uuid') 
			AND meta_value IN ('" . implode( "','", array_map( 'esc_sql', $uuids ) ) . "')
		" );

		if ( ! empty( $post_ids ) ) {
			// Merge with existing search results logic or force these
			// For this MVP, we want to ensure these show up.
			// However, standard WP search might not find them if the title doesn't match in wp_posts.
			// So we add them to 'post__in'.
			$current_in = $query->get( 'post__in', [] );
			$query->set( 'post__in', array_merge( $current_in, $post_ids ) );
			
			// Also ensure we are searching our CPTs
			$types = $query->get( 'post_type' );
			if ( empty( $types ) || 'any' === $types ) {
				$types = [ 'post', 'page', 'kb_version', 'kb_fork' ];
			} elseif ( is_array( $types ) ) {
				$types = array_merge( $types, [ 'kb_version', 'kb_fork' ] );
			}
			$query->set( 'post_type', $types );
		}
	}
}
