<?php

namespace Knowledge\Infrastructure;

class FrontendRenderer {

	public function init(): void {
		add_filter( 'the_content', [ $this, 'inject_article_content' ] );
		add_shortcode( 'knowledge_archive', [ $this, 'render_archive_shortcode' ] );
		add_shortcode( 'knowledge_search', [ $this, 'render_search_shortcode' ] );
		add_shortcode( 'knowledge_category_list', [ $this, 'render_category_list_shortcode' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
	}

	public function enqueue_styles(): void {
		wp_register_style( 'knowledge-frontend', false );
		wp_enqueue_style( 'knowledge-frontend' );
		
		$css = "
			.knowledge-archive-grid {
				display: grid;
				grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
				gap: 20px;
				margin: 20px 0;
			}
			.knowledge-archive-grid[data-columns='1'] { grid-template-columns: 1fr; }
			.knowledge-archive-grid[data-columns='2'] { grid-template-columns: repeat(2, 1fr); }
			.knowledge-archive-grid[data-columns='3'] { grid-template-columns: repeat(3, 1fr); }
			.knowledge-archive-grid[data-columns='4'] { grid-template-columns: repeat(4, 1fr); }
			
			@media (max-width: 768px) {
				.knowledge-archive-grid[data-columns] { grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); }
			}

			.knowledge-card {
				border: 1px solid #e0e0e0;
				border-radius: 8px;
				overflow: hidden;
				transition: transform 0.2s, box-shadow 0.2s;
				background: #fff;
				display: flex;
				flex-direction: column;
				text-decoration: none;
				color: inherit;
			}
			.knowledge-card:hover {
				transform: translateY(-4px);
				box-shadow: 0 10px 20px rgba(0,0,0,0.08);
			}
			.knowledge-card-image {
				height: 200px;
				background: #f5f5f5;
				overflow: hidden;
				position: relative;
			}
			.knowledge-card-image img {
				width: 100%;
				height: 100%;
				object-fit: cover;
			}
			.knowledge-card-content {
				padding: 20px;
				flex-grow: 1;
				display: flex;
				flex-direction: column;
			}
			.knowledge-card-title {
				margin: 0 0 10px;
				font-size: 1.25rem;
				line-height: 1.4;
				font-weight: 600;
			}
			.knowledge-card-meta {
				margin-top: auto;
				font-size: 0.85rem;
				color: #666;
				display: flex;
				justify-content: space-between;
				align-items: center;
				border-top: 1px solid #eee;
				padding-top: 10px;
			}
			.knowledge-card-category {
				background: #f0f0f0;
				padding: 2px 8px;
				border-radius: 12px;
				font-weight: 500;
			}
			
			/* Search Form */
			.knowledge-search-form {
				display: flex;
				gap: 10px;
				margin-bottom: 20px;
			}
			.knowledge-search-input {
				flex-grow: 1;
				padding: 10px;
				border: 1px solid #ccc;
				border-radius: 4px;
				font-size: 1rem;
			}
			.knowledge-search-button {
				padding: 10px 20px;
				background: #0073aa;
				color: #fff;
				border: none;
				border-radius: 4px;
				cursor: pointer;
				font-size: 1rem;
			}
			.knowledge-search-button:hover {
				background: #005177;
			}

			/* Category List */
			.knowledge-category-list {
				list-style: none;
				padding: 0;
				margin: 0 0 20px 0;
			}
			.knowledge-category-list li {
				margin-bottom: 5px;
			}
			.knowledge-category-list a {
				text-decoration: none;
				color: #0073aa;
			}
			
			/* Category Pills */
			.knowledge-category-list.style-pills {
				display: flex;
				flex-wrap: wrap;
				gap: 10px;
			}
			.knowledge-category-list.style-pills li {
				margin: 0;
			}
			.knowledge-category-list.style-pills a {
				background: #f0f0f0;
				padding: 5px 15px;
				border-radius: 20px;
				color: #333;
				display: inline-block;
				transition: background 0.2s;
			}
			.knowledge-category-list.style-pills a:hover {
				background: #e0e0e0;
			}
			.knowledge-cat-count {
				font-size: 0.8em;
				opacity: 0.7;
				margin-left: 5px;
			}
		";
		
		wp_add_inline_style( 'knowledge-frontend', $css );
	}

	public function render_archive_shortcode( $atts ): string {
		$args = shortcode_atts( [
			'limit'    => 12,
			'columns'  => 3,
			'category' => null,
			'tag'      => null,
			'ids'      => null,
			'orderby'  => 'date',
			'order'    => 'DESC',
		], $atts );

		$query_args = [
			'post_type'      => 'kb_article',
			'posts_per_page' => $args['limit'],
			'orderby'        => $args['orderby'],
			'order'          => $args['order'],
		];

		if ( ! empty( $args['category'] ) ) {
			$query_args['tax_query'][] = [
				'taxonomy' => 'kb_category',
				'field'    => 'slug',
				'terms'    => $args['category'],
			];
		}

		if ( ! empty( $args['tag'] ) ) {
			$query_args['tax_query'][] = [
				'taxonomy' => 'kb_tag',
				'field'    => 'slug',
				'terms'    => $args['tag'],
			];
		}

		if ( ! empty( $args['ids'] ) ) {
			$query_args['post__in'] = array_map( 'intval', explode( ',', $args['ids'] ) );
		}

		$query = new \WP_Query( $query_args );

		if ( ! $query->have_posts() ) {
			return '<p>No articles found.</p>';
		}

		$columns = absint( $args['columns'] );
		$output = sprintf( '<div class="knowledge-archive-grid" data-columns="%d">', $columns );

		while ( $query->have_posts() ) {
			$query->the_post();
			
			// Image
			$img_html = '';
			if ( has_post_thumbnail() ) {
				$img_html = get_the_post_thumbnail( get_the_ID(), 'medium' );
			} else {
				$img_html = '<div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#ccc;">No Image</div>';
			}

			// Category
			$cats = get_the_terms( get_the_ID(), 'kb_category' );
			$cat_name = 'Uncategorized';
			if ( $cats && ! is_wp_error( $cats ) ) {
				$cat_name = $cats[0]->name;
			}

			$output .= sprintf(
				'<a href="%s" class="knowledge-card">
					<div class="knowledge-card-image">%s</div>
					<div class="knowledge-card-content">
						<h3 class="knowledge-card-title">%s</h3>
						<div class="knowledge-card-meta">
							<span class="knowledge-card-category">%s</span>
							<span class="knowledge-card-date">%s</span>
						</div>
					</div>
				</a>',
				get_permalink(),
				$img_html,
				get_the_title(),
				esc_html( $cat_name ),
				get_the_date()
			);
		}

		$output .= '</div>';
		
		wp_reset_postdata();

		return $output;
	}

	public function render_search_shortcode( $atts ): string {
		$args = shortcode_atts( [
			'placeholder' => 'Search knowledge...',
			'button_text' => 'Search',
		], $atts );

		$action = esc_url( home_url( '/' ) );
		$query  = get_search_query();

		return sprintf(
			'<form role="search" method="get" class="knowledge-search-form" action="%s">
				<input type="hidden" name="post_type" value="kb_article" />
				<input type="search" class="knowledge-search-input" placeholder="%s" value="%s" name="s" />
				<button type="submit" class="knowledge-search-button">%s</button>
			</form>',
			$action,
			esc_attr( $args['placeholder'] ),
			esc_attr( $query ),
			esc_html( $args['button_text'] )
		);
	}

	public function render_category_list_shortcode( $atts ): string {
		$args = shortcode_atts( [
			'style'      => 'list',
			'show_count' => 'true',
			'hide_empty' => 'true',
		], $atts );

		$terms = get_terms( [
			'taxonomy'   => 'kb_category',
			'hide_empty' => filter_var( $args['hide_empty'], FILTER_VALIDATE_BOOLEAN ),
		] );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return '';
		}

		$style_class = 'style-' . sanitize_html_class( $args['style'] );
		$output = sprintf( '<ul class="knowledge-category-list %s">', $style_class );

		foreach ( $terms as $term ) {
			$count_html = '';
			if ( filter_var( $args['show_count'], FILTER_VALIDATE_BOOLEAN ) ) {
				$count_html = sprintf( '<span class="knowledge-cat-count">(%d)</span>', $term->count );
			}

			$output .= sprintf(
				'<li><a href="%s">%s%s</a></li>',
				esc_url( get_term_link( $term ) ),
				esc_html( $term->name ),
				$count_html
			);
		}

		$output .= '</ul>';

		return $output;
	}

	public function inject_article_content( string $content ): string {
		// Only run on single KB Article pages
		if ( ! is_singular( 'kb_article' ) ) {
			return $content;
		}

		// Get current post ID
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return $content;
		}

		// Find latest version
		$versions = get_posts( [
			'post_type'      => 'kb_version',
			'post_parent'    => $post_id,
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
		] );

		if ( empty( $versions ) ) {
			return $content . '<p><em>No content versions found.</em></p>';
		}

		$latest_version_id = $versions[0];
		$uuid = get_post_meta( $latest_version_id, '_kb_version_uuid', true );

		if ( ! $uuid ) {
			return $content . '<p><em>Error: Version UUID missing.</em></p>';
		}

		$file_path = KNOWLEDGE_DATA_PATH . '/versions/' . $uuid . '/content.html';

		if ( ! file_exists( $file_path ) ) {
			return $content . '<p><em>Error: Content file not found.</em></p>';
		}

		// Read content
		$file_content = file_get_contents( $file_path );

		// Remove duplicate H1 title if present
		$file_content = $this->strip_title( $file_content );

		// Return injected content
		// We append it to any existing content (usually empty for kb_article)
		return $content . $file_content;
	}

	private function strip_title( string $html ): string {
		// Suppress warnings for malformed HTML
		$dom = new \DOMDocument();
		libxml_use_internal_errors( true );
		
		// Helper to handle UTF-8 correctly
		$html = mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' );
		
		if ( empty( $html ) || ! $dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD ) ) {
			libxml_clear_errors();
			return $html;
		}
		libxml_clear_errors();

		$h1s = $dom->getElementsByTagName( 'h1' );
		if ( $h1s->length > 0 ) {
			$h1 = $h1s->item( 0 );
			$h1->parentNode->removeChild( $h1 );
		}

		return $dom->saveHTML();
	}
}
