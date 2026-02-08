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
				gap: 32px;
				margin: 32px 0;
				padding: 0 32px;
				box-sizing: border-box;
				font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;
			}

			@media (min-width: 860px) {
				.knowledge-archive-grid {
					/* Reset to container width to ensure gutters and balance */
					width: 100%;
					margin-left: 0;
					margin-right: 0;
				}
			}

			@media (min-width: 1024px) {
				.knowledge-archive-grid[data-columns=\"2\"] { grid-template-columns: repeat(2, 1fr); }
				.knowledge-archive-grid[data-columns=\"3\"] { grid-template-columns: repeat(3, 1fr); }
				.knowledge-archive-grid[data-columns=\"4\"] { grid-template-columns: repeat(4, 1fr); }
			}

			.knowledge-card {
				background: #ffffff;
				border-radius: 12px;
				overflow: hidden;
				box-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
				transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
				display: flex;
				flex-direction: column;
				height: 100%;
				text-decoration: none;
				color: inherit;
				border: 1px solid #e5e7eb;
				position: relative;
			}

			.knowledge-card:hover {
				transform: translateY(-4px);
				box-shadow: 0 12px 20px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
				border-color: #d1d5db;
				cursor: pointer;
			}

			.knowledge-card-image-wrapper {
				width: 100%;
				height: 180px;
				overflow: hidden;
				position: relative;
				background-color: #f3f4f6;
				display: block;
			}

			.knowledge-card-image-wrapper img {
				width: 100%;
				height: 100%;
				object-fit: cover;
				transition: transform 0.3s ease;
			}

			.knowledge-card:hover .knowledge-card-image-wrapper img {
				transform: scale(1.05);
			}

			.knowledge-card-hover-content {
				position: absolute;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				background: rgba(0, 0, 0, 0.75);
				color: #fff;
				padding: 24px;
				display: flex;
				flex-direction: column;
				justify-content: flex-end;
				opacity: 0;
				transition: opacity 0.3s ease;
				z-index: 2;
				pointer-events: none;
			}

			.knowledge-card:hover .knowledge-card-hover-content {
				opacity: 1;
			}

			.knowledge-card-summary {
				font-size: 0.9rem;
				line-height: 1.5;
				margin-bottom: 12px;
				display: -webkit-box;
				-webkit-line-clamp: 4;
				-webkit-box-orient: vertical;
				overflow: hidden;
				text-shadow: 0 1px 2px rgba(0,0,0,0.5);
			}

			.knowledge-card-hover-tags {
				display: flex;
				flex-wrap: wrap;
				gap: 6px;
			}

			.knowledge-card-hover-tags .knowledge-card-badge {
				background: rgba(255, 255, 255, 0.25);
				color: #fff;
				border: 1px solid rgba(255, 255, 255, 0.1);
			}

			.knowledge-card-body {
				padding: 16px;
				display: flex;
				flex-direction: column;
				flex-grow: 1;
			}

			.knowledge-card-title {
				font-size: 1.05rem;
				font-weight: 600;
				color: #111827;
				margin: 0 0 12px 0;
				line-height: 1.4;
				display: -webkit-box;
				-webkit-line-clamp: 2;
				-webkit-box-orient: vertical;
				overflow: hidden;
				text-decoration: none;
			}
			
			a.knowledge-card-link {
				text-decoration: none;
				color: inherit;
			}

			/* Stretched Link */
			a.knowledge-card-link::after {
				content: '';
				position: absolute;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				z-index: 1;
			}

			.knowledge-card-badges {
				display: flex;
				flex-wrap: wrap;
				gap: 6px;
				margin-bottom: 16px;
				position: relative;
				z-index: 2; /* Clickable badges */
			}

			.knowledge-card-badge {
				display: inline-flex;
				align-items: center;
				padding: 2px 10px;
				border-radius: 9999px;
				font-size: 0.75rem;
				font-weight: 500;
				background-color: #f3f4f6;
				color: #4b5563;
				white-space: nowrap;
				letter-spacing: 0.025em;
			}

			.knowledge-card-footer {
				margin-top: auto;
				display: flex;
				align-items: center;
				justify-content: space-between;
				padding-top: 12px;
				border-top: 1px solid #f3f4f6;
				position: relative;
				z-index: 2;
			}

			.knowledge-card-meta {
				display: flex;
				align-items: center;
				gap: 6px;
				font-size: 0.75rem;
				color: #6b7280;
			}

			.knowledge-card-source {
				font-weight: 600;
				color: #374151;
			}

			.knowledge-card-menu-btn {
				background: none;
				border: none;
				padding: 4px;
				cursor: pointer;
				color: #9ca3af;
				display: flex;
				align-items: center;
				justify-content: center;
				border-radius: 50%;
				transition: background-color 0.2s;
				position: relative;
				z-index: 3; /* Top most */
			}

			.knowledge-card-menu-btn:hover {
				background-color: #f3f4f6;
				color: #4b5563;
			}

			/* Fallback for no image */
			.knowledge-no-image {
				display: flex;
				align-items: center;
				justify-content: center;
				width: 100%;
				height: 100%;
				background-color: #e5e7eb;
				color: #9ca3af;
				font-size: 2rem;
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
				$img_html = '<div class="knowledge-no-image">No Image</div>';
			}

			// Badges (Category + Tags, max 3)
			$terms = [];
			$cats = get_the_terms( get_the_ID(), 'kb_category' );
			if ( $cats && ! is_wp_error( $cats ) ) {
				foreach ( $cats as $c ) {
					$terms[] = $c->name;
				}
			}
			$tags = get_the_terms( get_the_ID(), 'kb_tag' );
			if ( $tags && ! is_wp_error( $tags ) ) {
				foreach ( $tags as $t ) {
					$terms[] = $t->name;
				}
			}
			$terms = array_slice( array_unique( $terms ), 0, 3 );
			
			$badges_html = '';
			foreach ( $terms as $term_name ) {
				$badges_html .= sprintf( '<span class="knowledge-card-badge">%s</span>', esc_html( $term_name ) );
			}

			// Source & Date
			$source = 'Knowledge';
			$source_url = get_post_meta( get_the_ID(), '_kb_source_url', true );
			if ( $source_url ) {
				$parsed = parse_url( $source_url );
				if ( isset( $parsed['host'] ) ) {
					$source = str_replace( 'www.', '', $parsed['host'] );
				}
			}
			
			$date = get_the_date( 'M j' );
			
			// Summary Priority: AI Summary -> Excerpt -> File Content -> Fallback
			$summary = get_post_meta( get_the_ID(), '_kb_ai_summary', true );
			
			if ( empty( $summary ) ) {
				$summary = get_the_excerpt();
			}

			if ( empty( $summary ) ) {
				// Try to get content from file
				$versions = get_posts( [
					'post_type'      => 'kb_version',
					'post_parent'    => get_the_ID(),
					'posts_per_page' => 1,
					'orderby'        => 'date',
					'order'          => 'DESC',
					'fields'         => 'ids',
				] );

				if ( ! empty( $versions ) ) {
					$uuid = get_post_meta( $versions[0], '_kb_version_uuid', true );
					if ( $uuid && defined( 'KNOWLEDGE_DATA_PATH' ) ) {
						$file_path = KNOWLEDGE_DATA_PATH . '/versions/' . $uuid . '/content.html';
						if ( file_exists( $file_path ) ) {
							$raw_content = file_get_contents( $file_path );
							$raw_content = strip_shortcodes( $raw_content );
							$raw_content = wp_strip_all_tags( $raw_content );
							$summary = wp_trim_words( $raw_content, 20 );
						}
					}
				}
			}
			
			if ( empty( $summary ) ) {
				$summary = 'View article details...';
			}

			$output .= sprintf(
				'<article class="knowledge-card">
					<div class="knowledge-card-image-wrapper">
						%s
						<div class="knowledge-card-hover-content">
							<div class="knowledge-card-summary">%s</div>
							<div class="knowledge-card-hover-tags">%s</div>
						</div>
					</div>
					<div class="knowledge-card-body">
						<a href="%s" class="knowledge-card-link">
							<h3 class="knowledge-card-title">%s</h3>
						</a>
						<div class="knowledge-card-badges">
							%s
						</div>
						<div class="knowledge-card-footer">
							<div class="knowledge-card-meta">
								<span class="knowledge-card-source">%s</span>
								<span>•</span>
								<span class="knowledge-card-date">%s</span>
							</div>
							<button class="knowledge-card-menu-btn" aria-label="Options">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<circle cx="12" cy="12" r="1"></circle>
									<circle cx="19" cy="12" r="1"></circle>
									<circle cx="5" cy="12" r="1"></circle>
								</svg>
							</button>
						</div>
					</div>
				</article>',
				$img_html,
				esc_html( $summary ),
				$badges_html,
				get_permalink(),
				get_the_title(),
				$badges_html,
				esc_html( $source ),
				esc_html( $date )
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
