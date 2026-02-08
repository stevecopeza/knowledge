<?php

namespace Knowledge\Service\Search;

class SearchIndexService {

	private string $table_name;

	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'kb_search_index';
	}

	public function index_item( string $uuid, string $type, string $title, string $html_content, array $metadata = [] ): void {
		global $wpdb;

		// 1. Clean content (remove HTML tags for better fulltext search)
		$text_content = wp_strip_all_tags( $html_content );

		// 2. Prepare data
		$data = [
			'object_uuid' => $uuid,
			'object_type' => $type,
			'title'       => $title,
			'content'     => $text_content,
			'metadata'    => wp_json_encode( $metadata ),
			'updated_at'  => current_time( 'mysql' ),
		];

		// 3. Check if exists
		$exists = $wpdb->get_var( $wpdb->prepare( 
			"SELECT id FROM {$this->table_name} WHERE object_uuid = %s", 
			$uuid 
		) );

		if ( $exists ) {
			$wpdb->update( $this->table_name, $data, [ 'object_uuid' => $uuid ] );
		} else {
			$wpdb->insert( $this->table_name, $data );
		}
	}

	public function remove_item( string $uuid ): void {
		global $wpdb;
		$wpdb->delete( $this->table_name, [ 'object_uuid' => $uuid ] );
	}
}
