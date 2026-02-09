<?php

namespace Knowledge\Infrastructure;

use Knowledge\Service\Project\ProjectService;

class ProjectAdminRegistrar {

	private ProjectService $project_service;

	public function __construct() {
		$this->project_service = new ProjectService();
	}

	public function init(): void {
		add_action( 'add_meta_boxes', [ $this, 'add_project_metabox' ] );
		add_action( 'save_post', [ $this, 'save_project_metabox' ] );
		
		// Project Dashboard (Contents)
		add_action( 'add_meta_boxes', [ $this, 'add_project_contents_metabox' ] );
		
		// Bulk Actions
		add_action( 'manage_posts_extra_tablenav', [ $this, 'add_bulk_project_ui' ] );
		add_action( 'admin_init', [ $this, 'handle_bulk_project_action' ] );
		add_action( 'admin_notices', [ $this, 'bulk_action_admin_notice' ] );
		
		// Handle Removal
		add_action( 'admin_action_kb_remove_from_project', [ $this, 'handle_remove_member' ] );
	}

	/**
	 * Add "Project Contents" metabox to Project Edit screen.
	 */
	public function add_project_contents_metabox(): void {
		add_meta_box(
			'kb_project_contents',
			__( 'Project Contents', 'knowledge' ),
			[ $this, 'render_project_contents' ],
			'kb_project',
			'normal',
			'high'
		);
	}

	/**
	 * Render the contents of the project.
	 *
	 * @param \WP_Post $post The Project post.
	 */
	public function render_project_contents( \WP_Post $post ): void {
		$member_ids = $this->project_service->get_members( $post->ID );
		
		if ( empty( $member_ids ) ) {
			echo '<p>' . __( 'This project is empty.', 'knowledge' ) . '</p>';
			return;
		}

		// Calculate Stats
		$counts = [];
		foreach ( $member_ids as $id ) {
			$type = get_post_type( $id );
			if ( ! $type ) continue; // Handle deleted posts
			if ( ! isset( $counts[ $type ] ) ) {
				$counts[ $type ] = 0;
			}
			$counts[ $type ]++;
		}

		echo '<div class="kb-project-stats" style="margin-bottom: 15px; padding: 10px; background: #f0f0f1; border: 1px solid #c3c4c7;">';
		echo '<strong>' . __( 'Project Stats:', 'knowledge' ) . '</strong> ';
		$parts = [];
		foreach ( $counts as $type => $count ) {
			$obj = get_post_type_object( $type );
			$label = $obj ? $obj->labels->name : $type;
			$parts[] = esc_html( "$label: $count" );
		}
		echo implode( ' <span style="color: #ccc;">|</span> ', $parts );
		echo '</div>';

		echo '<table class="widefat fixed striped">';
		echo '<thead><tr>';
		echo '<th>' . __( 'Title', 'knowledge' ) . '</th>';
		echo '<th>' . __( 'Type', 'knowledge' ) . '</th>';
		echo '<th style="width: 100px;">' . __( 'Actions', 'knowledge' ) . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';

		foreach ( $member_ids as $id ) {
			$title = get_the_title( $id );
			$type  = get_post_type( $id );
			$edit_link = get_edit_post_link( $id );
			$type_label = get_post_type_object( $type )->labels->singular_name;

			// Removal Link
			$remove_url = wp_nonce_url(
				add_query_arg( [
					'action'     => 'kb_remove_from_project',
					'project_id' => $post->ID,
					'object_id'  => $id,
				], admin_url( 'admin.php' ) ),
				'kb_remove_member_' . $post->ID . '_' . $id
			);

			echo '<tr>';
			echo '<td><a href="' . esc_url( $edit_link ) . '"><strong>' . esc_html( $title ) . '</strong></a></td>';
			echo '<td>' . esc_html( $type_label ) . '</td>';
			echo '<td><a href="' . esc_url( $remove_url ) . '" class="button button-small delete" style="color: #b32d2e; border-color: #b32d2e;">' . __( 'Remove', 'knowledge' ) . '</a></td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
	}

	/**
	 * Handle member removal action.
	 */
	public function handle_remove_member(): void {
		if ( ! isset( $_GET['project_id'], $_GET['object_id'] ) ) {
			return;
		}

		$project_id = intval( $_GET['project_id'] );
		$object_id  = intval( $_GET['object_id'] );

		check_admin_referer( 'kb_remove_member_' . $project_id . '_' . $object_id );

		if ( ! current_user_can( 'edit_post', $project_id ) ) {
			wp_die( __( 'Unauthorized', 'knowledge' ) );
		}

		$this->project_service->remove_member( $project_id, $object_id );

		wp_redirect( get_edit_post_link( $project_id, 'redirect' ) );
		exit;
	}

	/**
	 * Display admin notice after bulk action.
	 */
	public function bulk_action_admin_notice(): void {
		if ( empty( $_GET['kb_project_added'] ) ) {
			return;
		}

		$count = intval( $_GET['kb_project_added'] );
		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			sprintf(
				/* translators: %s: number of articles */
				esc_html( _n( '%s article added to project.', '%s articles added to project.', $count, 'knowledge' ) ),
				$count
			)
		);
	}

	/**
	 * Add "Add to Project" dropdown to Article list.
	 *
	 * @param string $which 'top' or 'bottom'.
	 */
	public function add_bulk_project_ui( string $which ): void {
		if ( 'top' !== $which ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'kb_article' !== $screen->post_type ) {
			return;
		}

		$projects = get_posts( [
			'post_type'      => 'kb_project',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );

		if ( empty( $projects ) ) {
			return;
		}

		echo '<div class="alignleft actions">';
		echo '<select name="kb_bulk_project_id" style="max-width: 200px;">';
		echo '<option value="">' . __( 'Add to Project...', 'knowledge' ) . '</option>';
		foreach ( $projects as $project ) {
			echo '<option value="' . esc_attr( $project->ID ) . '">' . esc_html( $project->post_title ) . '</option>';
		}
		echo '</select>';
		submit_button( __( 'Add', 'knowledge' ), 'button', 'kb_add_to_project_action', false );
		echo '</div>';
	}

	/**
	 * Handle the bulk action submission.
	 */
	public function handle_bulk_project_action(): void {
		// Check trigger
		if ( ! isset( $_REQUEST['kb_add_to_project_action'] ) ) {
			return;
		}

		// Check project selection
		if ( empty( $_REQUEST['kb_bulk_project_id'] ) ) {
			return;
		}

		// Check posts selection
		if ( empty( $_REQUEST['post'] ) || ! is_array( $_REQUEST['post'] ) ) {
			return;
		}

		// Verify nonce (standard WP bulk action nonce)
		check_admin_referer( 'bulk-posts' );

		// Check permissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$project_id = intval( $_REQUEST['kb_bulk_project_id'] );
		$post_ids   = array_map( 'intval', $_REQUEST['post'] );
		$count      = 0;

		foreach ( $post_ids as $pid ) {
			if ( $this->project_service->add_member( $project_id, $pid, get_post_type( $pid ) ) ) {
				$count++;
			}
		}

		// Redirect with message
		$redirect_url = remove_query_arg( [ 'kb_add_to_project_action', 'kb_bulk_project_id', 'posted', 'post' ], wp_unslash( $_SERVER['REQUEST_URI'] ) );
		$redirect_url = add_query_arg( 'kb_project_added', $count, $redirect_url );
		
		wp_redirect( $redirect_url );
		exit;
	}

	/**
	 * Add the Projects metabox to Articles (and future Notes).
	 */
	public function add_project_metabox(): void {
		$screens = [ 'kb_article', 'kb_note' ]; // kb_note will be added later
		foreach ( $screens as $screen ) {
			add_meta_box(
				'kb_project_membership',
				__( 'Projects', 'knowledge' ),
				[ $this, 'render_project_metabox' ],
				$screen,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render the metabox content.
	 *
	 * @param \WP_Post $post The current post object.
	 */
	public function render_project_metabox( \WP_Post $post ): void {
		// Get all published projects
		$projects = get_posts( [
			'post_type'      => 'kb_project',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );

		// Get current assignments
		$assigned_project_ids = $this->project_service->get_projects_for_object( $post->ID );

		// Security nonce
		wp_nonce_field( 'kb_save_project_membership', 'kb_project_membership_nonce' );

		echo '<div class="kb-project-list" style="max-height: 200px; overflow-y: auto; margin-bottom: 10px;">';
		
		if ( empty( $projects ) ) {
			echo '<p>' . __( 'No projects found.', 'knowledge' ) . '</p>';
		} else {
			echo '<ul>';
			foreach ( $projects as $project ) {
				$checked = in_array( $project->ID, $assigned_project_ids, true ) ? 'checked="checked"' : '';
				echo '<li><label>';
				echo '<input type="checkbox" name="kb_project_ids[]" value="' . esc_attr( $project->ID ) . '" ' . $checked . '> ';
				echo esc_html( $project->post_title );
				echo '</label></li>';
			}
			echo '</ul>';
		}
		
		echo '</div>';
		
		echo '<p class="howto">' . __( 'Select projects this content belongs to.', 'knowledge' ) . '</p>';
	}

	/**
	 * Save the metabox selections.
	 *
	 * @param int $post_id The Post ID.
	 */
	public function save_project_metabox( int $post_id ): void {
		// Check nonce
		if ( ! isset( $_POST['kb_project_membership_nonce'] ) || ! wp_verify_nonce( $_POST['kb_project_membership_nonce'], 'kb_save_project_membership' ) ) {
			return;
		}

		// Check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Get selected projects
		$selected_project_ids = isset( $_POST['kb_project_ids'] ) ? array_map( 'intval', $_POST['kb_project_ids'] ) : [];

		// Get current projects
		$current_project_ids = $this->project_service->get_projects_for_object( $post_id );

		// Calculate changes
		$to_add    = array_diff( $selected_project_ids, $current_project_ids );
		$to_remove = array_diff( $current_project_ids, $selected_project_ids );

		// Apply changes
		foreach ( $to_add as $project_id ) {
			$this->project_service->add_member( $project_id, $post_id, get_post_type( $post_id ) );
		}

		foreach ( $to_remove as $project_id ) {
			$this->project_service->remove_member( $project_id, $post_id );
		}
	}
}
