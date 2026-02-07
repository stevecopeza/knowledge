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
			content longtext NOT NULL,
			title text NOT NULL,
			metadata json DEFAULT NULL,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY object_uuid (object_uuid),
			FULLTEXT KEY content (content, title)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
