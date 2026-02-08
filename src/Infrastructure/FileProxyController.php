<?php

namespace Knowledge\Infrastructure;

class FileProxyController {

	public function init(): void {
		add_action( 'init', [ $this, 'add_rewrite_endpoint' ] );
		add_action( 'template_redirect', [ $this, 'handle_file_request' ] );
	}

	public function add_rewrite_endpoint(): void {
		add_rewrite_tag( '%kb_file%', '([^&]+)' );
		add_rewrite_rule( '^kb-file/(.+)/?$', 'index.php?kb_file=$matches[1]', 'top' );
	}

	public function handle_file_request(): void {
		$file_path = get_query_var( 'kb_file' );

		if ( empty( $file_path ) ) {
			return;
		}

		// Security Check: Must be logged in and able to read posts
		if ( ! is_user_logged_in() || ! current_user_can( 'read' ) ) {
			status_header( 403 );
			die( 'Access Denied' );
		}

		// Sanitize path (prevent directory traversal)
		$file_path = sanitize_text_field( $file_path );
		$file_path = str_replace( '..', '', $file_path );
		
		// Map URL path to filesystem path
		// URL: /kb-file/versions/uuid/content.html
		// URL: /kb-file/media/hash.jpg
		$full_path = KNOWLEDGE_DATA_PATH . '/' . $file_path;

		if ( ! file_exists( $full_path ) || ! is_file( $full_path ) ) {
			status_header( 404 );
			die( 'File not found' );
		}

		// Serve File
		$mime = wp_check_filetype( $full_path )['type'] ?? 'application/octet-stream';
		
		header( 'Content-Type: ' . $mime );
		header( 'Content-Length: ' . filesize( $full_path ) );
		header( 'X-Robots-Tag: noindex, nofollow' ); // Prevent indexing
		
		readfile( $full_path );
		exit;
	}

	public static function get_url( string $relative_path ): string {
		// Use standard WP Rewrite URL
		// E.g., https://site.com/kb-file/versions/uuid/content.html
		return home_url( '/kb-file/' . $relative_path );
	}
}
