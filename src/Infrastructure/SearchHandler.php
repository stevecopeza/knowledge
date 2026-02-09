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
		add_action( 'kb_version_created', [ $this, 'index_version' ], 10, 5 );
		add_action( 'kb_fork_updated', [ $this, 'index_fork' ], 10, 4 );

		// Search Hooks
		add_filter( 'query_vars', [ $this, 'register_query_vars' ] );
		add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ] );
	}

	public function register_query_vars( array $vars ): array {
		$vars[] = 'kb_project_id';
		return $vars;
	}

	public function index_version( string $uuid, string $title, string $content, array $metadata, int $post_id ): void {
		$this->service->index_item( $uuid, 'version', $post_id, $title, $content, $metadata );
	}

	public function index_fork( string $uuid, string $title, string $content, int $post_id ): void {
		$this->service->index_item( $uuid, 'fork', $post_id, $title, $content );
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
		
		// Find matching Post IDs directly using FULLTEXT
		// Note: We use boolean mode for simplicity
		// Optimization: Limit increased to 1000 to support larger datasets while maintaining performance.
		// For >10k items, we rely on this limit to prevent memory exhaustion in 'post__in'.
		
		$project_id = $query->get( 'kb_project_id' );
		
		if ( $project_id ) {
			// Scoped Search
			$rel_table = $wpdb->prefix . 'kb_project_relationships';
			$sql = $wpdb->prepare(
				"SELECT s.post_id 
				 FROM $table_name s
				 JOIN $rel_table p ON s.post_id = p.object_id
				 WHERE MATCH(s.content, s.title) AGAINST(%s IN BOOLEAN MODE)
				 AND p.project_id = %d
				 LIMIT 1000",
				$search_term,
				$project_id
			);
		} else {
			// Global Search
			$sql = $wpdb->prepare(
				"SELECT post_id FROM $table_name WHERE MATCH(content, title) AGAINST(%s IN BOOLEAN MODE) LIMIT 1000",
				$search_term
			);
		}

		$post_ids = $wpdb->get_col( $sql );

		if ( empty( $post_ids ) ) {
			return;
		}

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
