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
	}

	public function add_article_columns( array $columns ): array {
		$new_columns = [];
		foreach ( $columns as $key => $title ) {
			if ( $key === 'date' ) {
				$new_columns['source_url'] = 'Source URL';
				$new_columns['version_count'] = 'Versions';
			}
			$new_columns[ $key ] = $title;
		}
		return $new_columns;
	}

	public function render_article_column( string $column, int $post_id ): void {
		switch ( $column ) {
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
