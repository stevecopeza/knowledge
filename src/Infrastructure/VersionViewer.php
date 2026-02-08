<?php

namespace Knowledge\Infrastructure;

class VersionViewer {

	public function init(): void {
		add_action( 'add_meta_boxes', [ $this, 'add_content_meta_box' ] );
	}

	public function add_content_meta_box(): void {
		// Version View
		add_meta_box(
			'kb_version_content',
			'Version Content (Read-Only)',
			[ $this, 'render_content_meta_box' ],
			'kb_version',
			'normal',
			'high'
		);

		// Article View (List Versions)
		add_meta_box(
			'kb_article_versions',
			'Article Versions',
			[ $this, 'render_article_versions_box' ],
			'kb_article',
			'normal',
			'high'
		);
	}

	public function render_article_versions_box( \WP_Post $post ): void {
		$versions = get_posts( [
			'post_type'      => 'kb_version',
			'post_parent'    => $post->ID,
			'posts_per_page' => 10,
			'orderby'        => 'date',
			'order'          => 'DESC',
		] );

		if ( empty( $versions ) ) {
			echo '<p>No versions found for this article.</p>';
			return;
		}

		echo '<table class="widefat fixed striped">';
		echo '<thead><tr><th>Date</th><th>Version UUID</th><th>Actions</th></tr></thead>';
		echo '<tbody>';
		
		$latest_version = null;

		foreach ( $versions as $index => $version ) {
			if ( $index === 0 ) {
				$latest_version = $version;
			}

			$uuid = get_post_meta( $version->ID, '_kb_version_uuid', true );
			$edit_link = get_edit_post_link( $version->ID );
			
			echo '<tr>';
			echo '<td>' . esc_html( get_the_date( 'Y-m-d H:i:s', $version ) ) . '</td>';
			echo '<td>' . esc_html( $uuid ) . '</td>';
			echo '<td><a href="' . esc_url( $edit_link ) . '" class="button button-small">View Version</a></td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';

		// Preview Latest
		if ( $latest_version ) {
			echo '<h3>Latest Version Content Preview</h3>';
			$this->render_content_meta_box( $latest_version );
		}
	}

	public function render_content_meta_box( \WP_Post $post ): void {
		$uuid = get_post_meta( $post->ID, '_kb_version_uuid', true );
		if ( ! $uuid ) {
			echo '<p>No UUID found for this version.</p>';
			return;
		}

		$file_path = KNOWLEDGE_DATA_PATH . '/versions/' . $uuid . '/content.html';
		if ( ! file_exists( $file_path ) ) {
			echo '<p>Content file not found at: ' . esc_html( $file_path ) . '</p>';
			return;
		}

		$content = file_get_contents( $file_path );
		
		echo '<div class="kb-version-content" style="background: #fff; padding: 20px; border: 1px solid #ddd; max-height: 600px; overflow-y: auto;">';
		// Outputting raw HTML from storage. In a real app, we might sanitize this again, 
		// but it was sanitized on ingestion.
		echo $content; 
		echo '</div>';
		
		echo '<hr>';
		echo '<p><strong>Raw Path:</strong> ' . esc_html( $file_path ) . '</p>';
		
		// Fork Button
		$fork_url = wp_nonce_url( 
			admin_url( 'admin-post.php?action=kb_fork_version&version_id=' . $post->ID ), 
			'kb_fork_version_' . $post->ID 
		);
		echo '<p><a href="' . esc_url( $fork_url ) . '" class="button button-primary">Fork this Version</a></p>';
	}
}
