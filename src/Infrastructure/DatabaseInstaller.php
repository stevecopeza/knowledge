<?php

namespace Knowledge\Infrastructure;

class DatabaseInstaller {

	public static function install(): void {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'kb_search_index';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			object_uuid varchar(36) NOT NULL,
			object_type varchar(20) NOT NULL,
			post_id bigint(20) unsigned NOT NULL DEFAULT 0,
			content longtext NOT NULL,
			title text NOT NULL,
			metadata json DEFAULT NULL,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY object_uuid (object_uuid),
			KEY post_id (post_id),
			FULLTEXT KEY content (content, title)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Project Relationships Table
		$project_rel_table = $wpdb->prefix . 'kb_project_relationships';
		$sql_project = "CREATE TABLE $project_rel_table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			project_id bigint(20) unsigned NOT NULL,
			object_id bigint(20) unsigned NOT NULL,
			object_type varchar(20) NOT NULL DEFAULT 'post',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY project_object (project_id, object_id),
			KEY project_id (project_id),
			KEY object_id (object_id)
		) $charset_collate;";

		dbDelta( $sql_project );
	}
}
