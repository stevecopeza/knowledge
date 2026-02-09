<?php

namespace Knowledge\Infrastructure;

use Knowledge\Service\Forking\ForkService;

class ForkHandler {

	private ForkService $service;

	public function __construct() {
		$this->service = new ForkService();
	}

	public function init(): void {
		add_action( 'admin_post_kb_fork_version', [ $this, 'handle_fork_action' ] );
		add_action( 'save_post_kb_fork', [ $this, 'handle_save_fork' ], 10, 3 );
	}

	public function handle_fork_action(): void {
		if ( ! isset( $_GET['version_id'] ) || ! isset( $_GET['_wpnonce'] ) ) {
			wp_die( 'Missing parameters.' );
		}

		$version_id = (int) $_GET['version_id'];
		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'kb_fork_version_' . $version_id ) ) {
			wp_die( 'Invalid nonce.' );
		}

		if ( ! current_user_can( 'edit_posts' ) ) { // Adjust capability as needed
			wp_die( 'Unauthorized.' );
		}

		try {
			$fork_id = $this->service->create_fork( $version_id );
			wp_redirect( get_edit_post_link( $fork_id, 'raw' ) );
			exit;
		} catch ( \Exception $e ) {
			wp_die( 'Error creating fork: ' . esc_html( $e->getMessage() ) );
		}
	}

	public function handle_save_fork( int $post_id, \WP_Post $post, bool $update ): void {
		// Autosave, revision, or not ready
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( 'kb_fork' !== $post->post_type ) {
			return;
		}

		// Save to disk
		$this->service->save_fork_to_disk( $post_id );

		// Index Content
		$uuid = get_post_meta( $post_id, '_kb_fork_uuid', true );
		if ( $uuid ) {
			do_action( 'kb_fork_updated', $uuid, $post->post_title, $post->post_content, $post_id );
		}
	}
}
