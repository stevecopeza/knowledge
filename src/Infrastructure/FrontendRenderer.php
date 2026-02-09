<?php

namespace Knowledge\Infrastructure;

class FrontendRenderer {

	public function init(): void {
		add_filter( 'the_content', [ $this, 'inject_article_content' ] );
		add_shortcode( 'knowledge_archive', [ $this, 'render_archive_shortcode' ] );
		add_shortcode( 'knowledge_search', [ $this, 'render_search_shortcode' ] );
		add_shortcode( 'knowledge_category_list', [ $this, 'render_category_list_shortcode' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'wp_ajax_knowledge_load_more', [ $this, 'ajax_load_more' ] );
		add_action( 'wp_ajax_nopriv_knowledge_load_more', [ $this, 'ajax_load_more' ] );
		add_action( 'wp_ajax_knowledge_search_results', [ $this, 'ajax_search_results' ] );
		add_action( 'wp_ajax_nopriv_knowledge_search_results', [ $this, 'ajax_search_results' ] );
	}

	public function enqueue_assets(): void {
		wp_register_style( 'knowledge-frontend', false );
		wp_enqueue_style( 'knowledge-frontend' );

		wp_enqueue_script(
			'knowledge-frontend-js',
			plugins_url( 'assets/js/knowledge-frontend.js', dirname( __DIR__, 2 ) . '/knowledge.php' ),
			[ 'jquery' ],
			'1.0.0',
			true
		);

		wp_localize_script( 'knowledge-frontend-js', 'knowledge_vars', [
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( 'knowledge_load_more' ),
			'chat_nonce' => wp_create_nonce( 'knowledge_chat_nonce' ),
			'recheck_nonce' => wp_create_nonce( 'knowledge_recheck_nonce' ),
		] );
		
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

			.knowledge-card:hover .knowledge-card-hover-content {
				opacity: 1;
			}

			/* Add delay for hover on desktop to prevent jarring effect when scrolling */
			@media (hover: hover) {
				.knowledge-card:hover .knowledge-card-hover-content {
					transition-delay: 1s;
				}
			}

			.knowledge-card-body {
				padding: 24px;
				flex-grow: 1;
				display: flex;
				flex-direction: column;
			}

			.knowledge-card-hover-content {
				position: absolute;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				background: #ffffff;
				color: #333;
				padding: 24px;
				display: flex;
				flex-direction: column;
				justify-content: center;
				opacity: 0;
				transition: opacity 0.3s ease;
				z-index: 20;
				text-decoration: none;
			}

			/* Floating Badges */
			.knowledge-card-badge {
				display: inline-block;
				padding: 4px 8px;
				border-radius: 4px;
				background: #e5e7eb;
				color: #374151;
				font-size: 0.75rem;
				font-weight: 500;
				margin-right: 4px;
				margin-bottom: 4px;
				line-height: 1.2;
			}

			.knowledge-card-floating-badges {
				position: absolute;
				top: 12px;
				right: 12px;
				z-index: 5;
				display: flex;
				gap: 4px;
				flex-wrap: wrap;
				justify-content: flex-end;
				pointer-events: none; /* Let clicks pass through to card/image */
			}
			
			.knowledge-card-floating-badges .knowledge-card-badge {
				background: rgba(255, 255, 255, 0.9);
				color: #333;
				box-shadow: 0 2px 4px rgba(0,0,0,0.1);
				backdrop-filter: blur(4px);
				pointer-events: auto; /* Re-enable clicks on badges if they are links (future proofing) */
			}

			.knowledge-card-summary {
				display: -webkit-box;
				-webkit-line-clamp: 10;
				-webkit-box-orient: vertical;
				overflow: hidden;
				margin-bottom: 16px;
			}

			.knowledge-card-hover-tags {
				margin-top: auto;
			}

			/* Mobile/Touch Optimizations */
			@media (hover: none) {
				.knowledge-card-hover-content {
					background: #ffffff;
					padding: 16px;
					justify-content: center;
				}
				
				.knowledge-card-summary {
					-webkit-line-clamp: 6;
					font-size: 0.9rem;
					margin-bottom: 12px;
					text-shadow: none;
					color: #333;
				}
			}

			.knowledge-card-meta {
				display: flex;
				align-items: center;
				font-size: 0.75rem;
				color: #6b7280;
			}

			.knowledge-card-avatar {
				width: 20px;
				height: 20px;
				border-radius: 50%;
				margin-right: 6px;
				object-fit: cover;
			}

			.knowledge-card-source {
				font-weight: 500;
				color: #374151;
			}

			.knowledge-card-menu-btn {
				background: none;
				border: none;
				padding: 12px; /* Increased touch target */
				margin: -8px; /* Negative margin to maintain visual position */
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
			
			/* Pagination */
			.knowledge-pagination {
				margin-top: 32px;
				display: flex;
				justify-content: center;
				gap: 8px;
			}
			
			.knowledge-load-more-btn {
				padding: 12px 24px;
				background: #f3f4f6;
				border: 1px solid #e5e7eb;
				border-radius: 6px;
				cursor: pointer;
				font-weight: 500;
				color: #374151;
				transition: all 0.2s ease;
			}
			
			.knowledge-load-more-btn:hover {
				background: #e5e7eb;
				color: #111827;
			}
			
			.knowledge-load-more-btn:disabled {
				opacity: 0.6;
				cursor: not-allowed;
			}
			
			.knowledge-loading-spinner {
				display: inline-block;
				width: 20px;
				height: 20px;
				border: 2px solid rgba(0,0,0,0.1);
				border-left-color: currentColor;
				border-radius: 50%;
				animation: knowledge-spin 1s linear infinite;
				margin-right: 8px;
				vertical-align: middle;
			}
			
			@keyframes knowledge-spin {
				0% { transform: rotate(0deg); }
				100% { transform: rotate(360deg); }
			}
			
			.knowledge-end-message {
				text-align: center;
				color: #6b7280;
				padding: 20px;
				font-style: italic;
			}

			.knowledge-recheck-btn {
				position: absolute;
				bottom: 12px;
				right: 12px;
				z-index: 25;
				opacity: 0;
				transition: opacity 0.3s ease;
				cursor: pointer;
				border: none;
				background: #f3f4f6;
				padding: 6px 12px;
				border-radius: 4px;
				font-size: 12px;
				font-weight: 500;
				color: #374151;
				box-shadow: 0 2px 4px rgba(0,0,0,0.1);
			}
			.knowledge-card:hover .knowledge-recheck-btn {
				opacity: 1;
			}
			.knowledge-recheck-btn:hover {
				background: #e5e7eb;
				color: #111827;
			}
			.knowledge-recheck-btn:disabled {
				opacity: 0.6;
				cursor: not-allowed;
			}
			@media (hover: hover) {
				.knowledge-card:hover .knowledge-recheck-btn {
					transition-delay: 1s;
				}
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
			$output .= self::render_card( get_post() );
		}

		$output .= '</div>';
		
		wp_reset_postdata();

		return $output;
	}

	/**
	 * Render a single Knowledge Card.
	 * 
	 * @param \WP_Post $post The post object.
	 * @param array    $options Display options.
	 * @return string HTML output.
	 */
	public static function render_card( \WP_Post $post, array $options = [] ): string {
		$defaults = [
			'show_image'        => true,
			'title_length'      => 0,
			'show_summary'      => true,
			'summary_length'    => 30,
			'show_badges'       => true,
			'show_meta'         => true,
			'show_category'     => true,
			'category_position' => 'inline', // 'inline' or 'top_right'
			'show_avatar'       => false,
		];
		$options = wp_parse_args( $options, $defaults );

		$post_id = $post->ID;
		
		// Title
		$title = get_the_title( $post );
		if ( ! empty( $options['title_length'] ) && intval( $options['title_length'] ) > 0 ) {
			$limit = intval( $options['title_length'] );
			if ( mb_strlen( $title ) > $limit ) {
				$title = mb_substr( $title, 0, $limit ) . '...';
			}
		}
		
		// Image
		$img_html = '';
		if ( $options['show_image'] ) {
			if ( has_post_thumbnail( $post_id ) ) {
				$img_html = get_the_post_thumbnail( $post_id, 'medium' );
			} else {
				$img_html = '<div class="knowledge-no-image">No Image</div>';
			}
		}

		// Badges (Separated)
		$body_badges_html = '';
		$floating_badges_html = '';
		$hover_badges_html = '';
		
		if ( $options['show_category'] ) {
			$cats = get_the_terms( $post_id, 'kb_category' );
			if ( $cats && ! is_wp_error( $cats ) ) {
				$badges_str = '';
				foreach ( $cats as $c ) {
					$badges_str .= sprintf( '<span class="knowledge-card-badge knowledge-card-category">%s</span>', esc_html( $c->name ) );
				}
				
				if ( 'top_right' === $options['category_position'] ) {
					$floating_badges_html = sprintf( '<div class="knowledge-card-floating-badges">%s</div>', $badges_str );
				} else {
					$body_badges_html = $badges_str;
				}
			}
		}

		if ( $options['show_badges'] ) {
			$tags = get_the_terms( $post_id, 'kb_tag' );
			if ( $tags && ! is_wp_error( $tags ) ) {
				foreach ( $tags as $t ) {
					$hover_badges_html .= sprintf( '<span class="knowledge-card-badge">%s</span>', esc_html( $t->name ) );
				}
			}
		}

		// Source & Date
		$source = 'Knowledge';
		$source_url = get_post_meta( $post_id, '_kb_source_url', true );
		if ( $source_url ) {
			$parsed = parse_url( $source_url );
			if ( isset( $parsed['host'] ) ) {
				$source = str_replace( 'www.', '', $parsed['host'] );
			}
		}
		
		$date = get_the_date( 'M j', $post );
		
		// Summary Priority: AI Summary -> Excerpt -> File Content -> Fallback
		$summary = '';
		if ( $options['show_summary'] ) {
			$summary = get_post_meta( $post_id, '_kb_ai_summary', true );
			
			if ( empty( $summary ) ) {
				$summary = get_the_excerpt( $post );
			}

			if ( empty( $summary ) ) {
				// Try to get content from file
				$versions = get_posts( [
					'post_type'      => 'kb_version',
					'post_parent'    => $post_id,
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

			// Apply length limit if set
			if ( ! empty( $options['summary_length'] ) && intval( $options['summary_length'] ) > 0 ) {
				$summary = wp_trim_words( $summary, intval( $options['summary_length'] ) );
			}
		}

		$footer_html = '';
		if ( $options['show_meta'] ) {
			$avatar_html = '';
			if ( ! empty( $options['show_avatar'] ) ) {
				$avatar_html = get_avatar( $post->post_author, 40, '', '', ['class' => 'knowledge-card-avatar'] );
			}

			$footer_html = sprintf(
				'<div class="knowledge-card-footer">
					<div class="knowledge-card-meta">
						%s
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
				</div>',
				$avatar_html,
				esc_html( $source ),
				esc_html( $date )
			);
		}

		$recheck_btn_html = '';
		$show_recheck = false;
		if ( isset( $options['show_recheck_button'] ) ) {
			if ( $options['show_recheck_button'] === 'yes' || $options['show_recheck_button'] === true ) {
				$show_recheck = true;
			}
		}

		if ( $show_recheck ) {
			// Get Source URL from post meta
			$source_url = get_post_meta( $post_id, '_kb_source_url', true );
			if ( ! empty( $source_url ) ) {
				$recheck_btn_html = sprintf(
					'<button class="knowledge-recheck-btn" data-url="%s">Re-check</button>',
					esc_attr( $source_url )
				);
			}
		}

		return sprintf(
			'<article class="knowledge-card">
				%s
				<div class="knowledge-card-image-wrapper">
					%s
				</div>
				<div class="knowledge-card-body">
					<h3 class="knowledge-card-title">
						<a href="%s" class="knowledge-card-link">%s</a>
					</h3>
					<div class="knowledge-card-badges">
						%s
					</div>
					%s
				</div>
				<a href="%s" class="knowledge-card-hover-content">
					<div class="knowledge-card-summary">%s</div>
					<div class="knowledge-card-hover-tags">%s</div>
				</a>
				%s
			</article>',
			$floating_badges_html,
			$img_html,
			get_permalink( $post ),
			esc_html( $title ),
			$body_badges_html,
			$footer_html,
			get_permalink( $post ),
			esc_html( $summary ),
			$hover_badges_html,
			$recheck_btn_html
		);
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

	public function ajax_load_more(): void {
		if ( ! check_ajax_referer( 'knowledge_load_more', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$page = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
		$posts_per_page = isset( $_POST['posts_per_page'] ) ? intval( $_POST['posts_per_page'] ) : 12;
		
		// Reconstruct Query Args securely
		// We only allow specific arguments to be passed
		$query_args = [
			'post_type'      => 'kb_article',
			'post_status'    => 'publish',
			'paged'          => $page,
			'posts_per_page' => $posts_per_page,
		];

		if ( ! empty( $_POST['orderby'] ) ) {
			$allowed_orderby = [ 'date', 'title', 'modified', 'rand' ];
			if ( in_array( $_POST['orderby'], $allowed_orderby, true ) ) {
				$query_args['orderby'] = sanitize_text_field( $_POST['orderby'] );
			}
		}

		if ( ! empty( $_POST['order'] ) ) {
			$query_args['order'] = ( 'ASC' === strtoupper( $_POST['order'] ) ) ? 'ASC' : 'DESC';
		}

		// Handle Options
		$options = isset( $_POST['options'] ) ? $_POST['options'] : [];
		if ( is_string( $options ) ) {
			// Try to decode if it came as a JSON string
			$decoded = json_decode( stripslashes( $options ), true );
			if ( is_array( $decoded ) ) {
				$options = $decoded;
			}
		}
		
		// Sanitize options
		$safe_options = [
			'show_image'        => ! empty( $options['show_image'] ) && filter_var( $options['show_image'], FILTER_VALIDATE_BOOLEAN ),
			'title_length'      => isset( $options['title_length'] ) ? intval( $options['title_length'] ) : 0,
			'show_summary'      => ! empty( $options['show_summary'] ) && filter_var( $options['show_summary'], FILTER_VALIDATE_BOOLEAN ),
			'summary_length'    => isset( $options['summary_length'] ) ? intval( $options['summary_length'] ) : 20,
			'show_category'     => ! empty( $options['show_category'] ) && filter_var( $options['show_category'], FILTER_VALIDATE_BOOLEAN ),
			'category_position' => isset( $options['category_position'] ) ? sanitize_text_field( $options['category_position'] ) : 'on_image',
			'show_badges'       => ! empty( $options['show_badges'] ) && filter_var( $options['show_badges'], FILTER_VALIDATE_BOOLEAN ),
			'show_meta'         => ! empty( $options['show_meta'] ) && filter_var( $options['show_meta'], FILTER_VALIDATE_BOOLEAN ),
			'show_avatar'       => ! empty( $options['show_avatar'] ) && filter_var( $options['show_avatar'], FILTER_VALIDATE_BOOLEAN ),
			'show_recheck_button' => ! empty( $options['show_recheck_button'] ) && filter_var( $options['show_recheck_button'], FILTER_VALIDATE_BOOLEAN ),
		];

		// Taxonomy Filters
		$tax_query = [];
		if ( ! empty( $_POST['category'] ) ) {
			$tax_query[] = [
				'taxonomy' => 'kb_category',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $_POST['category'] ),
			];
		}

		if ( ! empty( $_POST['tag'] ) ) {
			$tax_query[] = [
				'taxonomy' => 'kb_tag',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $_POST['tag'] ),
			];
		}

		if ( ! empty( $tax_query ) ) {
			$query_args['tax_query'] = $tax_query;
		}
		
		// IDs filter
		if ( ! empty( $_POST['ids'] ) ) {
			$ids = array_map( 'intval', explode( ',', $_POST['ids'] ) );
			$query_args['post__in'] = $ids;
		}

		$query = new \WP_Query( $query_args );
		$html = '';

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$html .= self::render_card( get_post(), $safe_options );
			}
		}

		wp_send_json_success( [
			'html'        => $html,
			'max_pages'   => $query->max_num_pages,
			'found_posts' => $query->found_posts,
		] );
	}

	public function ajax_search_results(): void {
		// Use the same nonce as load more for simplicity
		if ( ! check_ajax_referer( 'knowledge_load_more', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
		if ( empty( $search ) ) {
			wp_send_json_error( 'Empty search query' );
		}

		$page = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
		$posts_per_page = 12;

		$query_args = [
			'post_type'      => 'kb_article',
			'post_status'    => 'publish',
			's'              => $search,
			'paged'          => $page,
			'posts_per_page' => $posts_per_page,
		];

		// Handle Options
		$options = isset( $_POST['options'] ) ? $_POST['options'] : [];
		if ( is_string( $options ) ) {
			$decoded = json_decode( stripslashes( $options ), true );
			if ( is_array( $decoded ) ) {
				$options = $decoded;
			}
		}

		// Sanitize options to ensure all keys exist
		$safe_options = [
			'show_image'        => ! empty( $options['show_image'] ) && filter_var( $options['show_image'], FILTER_VALIDATE_BOOLEAN ),
			'title_length'      => isset( $options['title_length'] ) ? intval( $options['title_length'] ) : 0,
			'show_summary'      => ! empty( $options['show_summary'] ) && filter_var( $options['show_summary'], FILTER_VALIDATE_BOOLEAN ),
			'summary_length'    => isset( $options['summary_length'] ) ? intval( $options['summary_length'] ) : 20,
			'show_category'     => ! empty( $options['show_category'] ) && filter_var( $options['show_category'], FILTER_VALIDATE_BOOLEAN ),
			'category_position' => isset( $options['category_position'] ) ? sanitize_text_field( $options['category_position'] ) : 'inline',
			'show_badges'       => ! empty( $options['show_badges'] ) && filter_var( $options['show_badges'], FILTER_VALIDATE_BOOLEAN ),
			'show_meta'         => ! empty( $options['show_meta'] ) && filter_var( $options['show_meta'], FILTER_VALIDATE_BOOLEAN ),
			'show_avatar'       => ! empty( $options['show_avatar'] ) && filter_var( $options['show_avatar'], FILTER_VALIDATE_BOOLEAN ),
			'show_recheck_button' => ! empty( $options['show_recheck_button'] ) && filter_var( $options['show_recheck_button'], FILTER_VALIDATE_BOOLEAN ),
		];

		// Filter by Categories if provided
		if ( ! empty( $_POST['categories'] ) ) {
			$categories = json_decode( stripslashes( $_POST['categories'] ), true );
			if ( ! empty( $categories ) ) {
				$query_args['tax_query'] = [
					[
						'taxonomy' => 'kb_category',
						'field'    => 'term_id',
						'terms'    => $categories,
					],
				];
			}
		}

		$query = new \WP_Query( $query_args );
		$html = '';

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$html .= self::render_card( get_post(), $safe_options );
			}
		} else {
			$html = '<p class="knowledge-no-results">' . sprintf( __( 'No results found for "%s".', 'knowledge' ), esc_html( $search ) ) . '</p>';
		}

		wp_send_json_success( [
			'cards_html'  => $html,
			'max_pages'   => $query->max_num_pages,
			'found_posts' => $query->found_posts,
		] );
	}
}
