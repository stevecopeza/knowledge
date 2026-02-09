<?php

namespace Knowledge\Service\Project;

class ProjectService {

	/**
	 * Table name for project relationships.
	 */
	private const TABLE_NAME = 'kb_project_relationships';

	/**
	 * Add an object (article/note) to a project.
	 *
	 * @param int    $project_id The Project ID.
	 * @param int    $object_id  The Object ID (Article/Note ID).
	 * @param string $object_type The Object Type (default: 'post').
	 * @return bool True on success, false on failure.
	 */
	public function add_member( int $project_id, int $object_id, string $object_type = 'post' ): bool {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;

		// Check if already exists to avoid errors (though UNIQUE constraint handles it, better to be explicit)
		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM $table WHERE project_id = %d AND object_id = %d",
			$project_id,
			$object_id
		) );

		if ( $exists ) {
			return true;
		}

		$result = $wpdb->insert(
			$table,
			[
				'project_id'  => $project_id,
				'object_id'   => $object_id,
				'object_type' => $object_type,
				'created_at'  => current_time( 'mysql' ),
			],
			[ '%d', '%d', '%s', '%s' ]
		);

		return $result !== false;
	}

	/**
	 * Remove an object from a project.
	 *
	 * @param int $project_id The Project ID.
	 * @param int $object_id  The Object ID.
	 * @return bool True on success.
	 */
	public function remove_member( int $project_id, int $object_id ): bool {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;

		$result = $wpdb->delete(
			$table,
			[
				'project_id' => $project_id,
				'object_id'  => $object_id,
			],
			[ '%d', '%d' ]
		);

		return $result !== false;
	}

	/**
	 * Get all members of a project.
	 *
	 * @param int $project_id The Project ID.
	 * @return array Array of object IDs.
	 */
	public function get_members( int $project_id ): array {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;

		return $wpdb->get_col( $wpdb->prepare(
			"SELECT object_id FROM $table WHERE project_id = %d ORDER BY created_at DESC",
			$project_id
		) );
	}

	/**
	 * Get members of a specific type.
	 *
	 * @param int    $project_id The Project ID.
	 * @param string $type       The object type (e.g., 'kb_article').
	 * @return array Array of object IDs.
	 */
	public function get_members_by_type( int $project_id, string $type ): array {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;

		// Note: The DB column object_type stores 'post' by default currently because of the schema default,
		// but we should store the actual post type.
		// However, the `add_member` calls `get_post_type($pid)` so it should be correct ('kb_article').
		
		return $wpdb->get_col( $wpdb->prepare(
			"SELECT object_id FROM $table WHERE project_id = %d AND object_type = %s ORDER BY created_at DESC",
			$project_id,
			$type
		) );
	}

	/**
	 * Get all projects an object belongs to.
	 *
	 * @param int $object_id The Object ID.
	 * @return array Array of Project IDs.
	 */
	public function get_projects_for_object( int $object_id ): array {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;

		return $wpdb->get_col( $wpdb->prepare(
			"SELECT project_id FROM $table WHERE object_id = %d ORDER BY created_at DESC",
			$object_id
		) );
	}
}
