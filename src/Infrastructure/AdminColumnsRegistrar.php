<?php

namespace Knowledge\Infrastructure;

class AdminColumnsRegistrar {

	public function init(): void {
		// Article Columns
		add_filter( 'manage_kb_article_posts_columns', [ $this, 'add_article_columns' ] );
		add_action( 'manage_kb_article_posts_custom_column', [ $this, 'render_article_column' ], 10, 2 );

		// Version Columns
		add_filter( 'manage_kb_version_posts_columns', [ $this, 'add_version_columns' ] );
		add_action( 'manage_kb_version_posts_custom_column', [ $this, 'render_version_column' ], 10, 2 );

		// Row Actions (Tooltip)
		add_filter( 'post_row_actions', [ $this, 'add_hover_tooltip' ], 10, 2 );

		// Enqueue Styles
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
	}

	public function enqueue_styles( $hook ): void {
		global $post_type;
		if ( $hook === 'edit.php' && $post_type === 'kb_article' ) {
			wp_enqueue_style(
				'knowledge-admin',
				plugin_dir_url( dirname( __DIR__ ) . '/knowledge.php' ) . 'assets/css/knowledge-admin.css',
				[],
				'1.0.0'
			);
		}
	}

	public function add_hover_tooltip( array $actions, \WP_Post $post ): array {
		if ( $post->post_type !== 'kb_article' ) {
			return $actions;
		}

		$summary = get_post_meta( $post->ID, '_kb_ai_summary', true );
		$tags    = get_the_term_list( $post->ID, 'kb_tag', '', ', ' );

		if ( ! $summary && ! $tags ) {
			return $actions;
		}

		$tooltip_html = '<div class="knowledge-tooltip-content">';
		if ( $summary ) {
			$tooltip_html .= '<div class="knowledge-tooltip-section"><strong>Summary:</strong> ' . esc_html( $summary ) . '</div>';
		}
		if ( $tags ) {
			$tooltip_html .= '<div class="knowledge-tooltip-section"><strong>Tags:</strong> ' . $tags . '</div>';
		}
		$tooltip_html .= '</div>';

		// We add a hidden action that serves as the container
		$actions['knowledge_info'] = '<span class="knowledge-tooltip-trigger">Info ' . $tooltip_html . '</span>';

		return $actions;
	}

	public function add_article_columns( array $columns ): array {
		$new_columns = [];
		foreach ( $columns as $key => $title ) {
			if ( $key === 'date' ) {
				$new_columns['projects']      = 'Projects';
				$new_columns['source_url']    = 'Source URL';
				$new_columns['version_count'] = 'Versions';
			}
			$new_columns[ $key ] = $title;
		}
		return $new_columns;
	}

	public function render_article_column( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'projects':
				$service = new \Knowledge\Service\Project\ProjectService();
				$project_ids = $service->get_projects_for_object( $post_id );
				if ( empty( $project_ids ) ) {
					echo '—';
				} else {
					$names = [];
					foreach ( $project_ids as $pid ) {
						$names[] = '<a href="' . esc_url( get_edit_post_link( $pid ) ) . '">' . esc_html( get_the_title( $pid ) ) . '</a>';
					}
					echo implode( ', ', $names );
				}
				break;
			case 'source_url':
				$url = get_post_meta( $post_id, '_kb_source_url', true );
				if ( $url ) {
					echo '<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $url ) . '</a>';
				} else {
					echo '—';
				}
				break;
			case 'version_count':
				$query = new \WP_Query( [
					'post_type'      => 'kb_version',
					'post_parent'    => $post_id,
					'posts_per_page' => -1,
					'fields'         => 'ids',
				] );
				echo esc_html( $query->found_posts );
				break;
		}
	}

	public function add_version_columns( array $columns ): array {
		$new_columns = [];
		foreach ( $columns as $key => $title ) {
			if ( $key === 'title' ) {
				$new_columns['uuid'] = 'UUID';
			}
			if ( $key === 'date' ) {
				$new_columns['parent_article'] = 'Parent Article';
				$new_columns['content_hash'] = 'Hash';
			}
			$new_columns[ $key ] = $title;
		}
		return $new_columns;
	}

	public function render_version_column( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'uuid':
				echo '<code>' . esc_html( get_post_meta( $post_id, '_kb_version_uuid', true ) ) . '</code>';
				break;
			case 'parent_article':
				$parent_id = wp_get_post_parent_id( $post_id );
				if ( $parent_id ) {
					echo '<a href="' . esc_url( get_edit_post_link( $parent_id ) ) . '">' . esc_html( get_the_title( $parent_id ) ) . '</a>';
				} else {
					echo '—';
				}
				break;
			case 'content_hash':
				$hash = get_post_meta( $post_id, '_kb_content_hash', true );
				if ( $hash ) {
					echo '<code>' . esc_html( substr( $hash, 0, 8 ) ) . '...</code>';
				}
				break;
		}
	}
}
