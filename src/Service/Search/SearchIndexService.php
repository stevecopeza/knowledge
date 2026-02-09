<?php

namespace Knowledge\Service\Search;

class SearchIndexService {

	private string $table_name;

	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'kb_search_index';
	}

	public function index_item( string $uuid, string $type, int $post_id, string $title, string $html_content, array $metadata = [] ): void {
		global $wpdb;

		// 1. Clean content (remove HTML tags for better fulltext search)
		$text_content = wp_strip_all_tags( $html_content );

		// 2. Prepare data
		$data = [
			'object_uuid' => $uuid,
			'object_type' => $type,
			'post_id'     => $post_id,
			'title'       => $title,
			'content'     => $text_content,
			'metadata'    => wp_json_encode( $metadata ),
			'updated_at'  => current_time( 'mysql' ),
		];

		// 3. Insert or Update (Upsert)
		// We use ON DUPLICATE KEY UPDATE. Since object_uuid is UNIQUE, this handles updates efficiently.
		$format = [ '%s', '%s', '%d', '%s', '%s', '%s', '%s' ];
		
		$sql = "INSERT INTO {$this->table_name} 
				(object_uuid, object_type, post_id, title, content, metadata, updated_at) 
				VALUES (%s, %s, %d, %s, %s, %s, %s)
				ON DUPLICATE KEY UPDATE 
				post_id = VALUES(post_id),
				title = VALUES(title), 
				content = VALUES(content), 
				metadata = VALUES(metadata), 
				updated_at = VALUES(updated_at)";

		$wpdb->query( $wpdb->prepare( 
			$sql, 
			$data['object_uuid'],
			$data['object_type'],
			$data['post_id'],
			$data['title'],
			$data['content'],
			$data['metadata'],
			$data['updated_at']
		) );
	}

	public function remove_item( string $uuid ): void {
		global $wpdb;
		$wpdb->delete( $this->table_name, [ 'object_uuid' => $uuid ] );
	}
}
