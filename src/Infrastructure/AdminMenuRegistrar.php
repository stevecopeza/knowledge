<?php

namespace Knowledge\Infrastructure;

class AdminMenuRegistrar {

	public function init(): void {
		add_action( 'admin_menu', [ $this, 'register_main_menu' ] );
	}

	public function register_main_menu(): void {
		add_menu_page(
			'Knowledge',
			'Knowledge',
			'read', // Capability
			'knowledge-main',
			[ $this, 'render_dashboard' ],
			'dashicons-book',
			25
		);

		// Placeholder Submenus
		$this->register_ingestion_submenu();
		$this->register_placeholder_submenu( 'Search', 'knowledge-search' );
		$this->register_ai_settings_submenu();
		$this->register_chat_submenu();
		$this->register_operations_submenu();
	}

	private function register_operations_submenu(): void {
		add_submenu_page(
			'knowledge-main',
			'Operations',
			'Operations',
			'manage_options',
			'knowledge-operations',
			[ $this, 'render_operations' ]
		);
	}

	private function register_chat_submenu(): void {
		$hook = add_submenu_page(
			'knowledge-main',
			'Ask AI',
			'Ask AI',
			'read',
			'knowledge-chat',
			[ $this, 'render_chat_page' ]
		);

		add_action( 'load-' . $hook, [ $this, 'enqueue_chat_assets' ] );
	}

	public function enqueue_chat_assets(): void {
		$plugin_url = plugin_dir_url( dirname( __DIR__, 2 ) . '/knowledge.php' );
		
		wp_enqueue_script(
			'knowledge-chat',
			$plugin_url . 'assets/js/knowledge-chat.js',
			[ 'jquery' ],
			'1.0.0',
			true
		);

		wp_localize_script( 'knowledge-chat', 'knowledgeChat', [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'knowledge_chat_nonce' ),
		] );

		wp_enqueue_style(
			'knowledge-chat',
			$plugin_url . 'assets/css/knowledge-chat.css',
			[],
			'1.0.0'
		);
	}

	public function render_chat_page(): void {
		echo '<div class="wrap">';
		echo '<h1>Ask AI</h1>';
		echo '<div id="knowledge-chat-container">';
		echo '<div id="knowledge-chat-history"></div>';
		echo '<div id="knowledge-chat-status"></div>';
		echo '<div id="knowledge-chat-controls">';
		echo '<input type="text" id="knowledge-chat-input" placeholder="Ask a question about your knowledge base..." class="regular-text">';
		echo '<button id="knowledge-chat-submit" class="button button-primary">Ask</button>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
	}

	private function register_ingestion_submenu(): void {
		add_submenu_page(
			'knowledge-main',
			'Ingestion',
			'Ingestion',
			'edit_posts',
			'knowledge-ingestion',
			[ $this, 'render_ingestion' ]
		);
	}

	private function register_ai_settings_submenu(): void {
		add_submenu_page(
			'knowledge-main',
			'AI Settings',
			'AI Settings',
			'manage_options',
			'knowledge-ai-settings',
			[ $this, 'render_ai_settings' ]
		);
	}

	public function render_ai_settings(): void {
		if ( isset( $_POST['knowledge_ollama_save'] ) && check_admin_referer( 'knowledge_ai_save' ) ) {
			update_option( 'knowledge_ollama_url', sanitize_text_field( $_POST['knowledge_ollama_url'] ) );
			update_option( 'knowledge_ollama_model', sanitize_text_field( $_POST['knowledge_ollama_model'] ) );
			echo '<div class="notice notice-success"><p>Settings saved.</p></div>';
		}

		// Handle Index Rebuild
		if ( isset( $_POST['knowledge_rebuild_index'] ) && check_admin_referer( 'knowledge_rebuild_index' ) ) {
			$versions = get_posts( [
				'post_type'      => 'kb_version',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			] );

			$count = 0;
			foreach ( $versions as $post_id ) {
				$uuid = get_post_meta( $post_id, '_kb_version_uuid', true );
				if ( $uuid ) {
					// Schedule embedding generation
					\Knowledge\Service\AI\EmbeddingJob::schedule( $uuid, '', '', [] );
					$count++;
				}
			}
			
			echo '<div class="notice notice-success"><p>Scheduled embedding generation for ' . intval( $count ) . ' versions. This process runs in the background.</p></div>';
		}

		$url   = get_option( 'knowledge_ollama_url', 'http://localhost:11434' );
		$model = get_option( 'knowledge_ollama_model', 'llama3' );
		
		// Check Connection
		$client = new \Knowledge\Service\AI\OllamaClient( $url, $model );
		$is_connected = $client->is_available();
		$status_icon  = $is_connected ? '✅ Connected' : '❌ Not Connected';
		$models       = $is_connected ? $client->get_models() : [];

		echo '<div class="wrap">';
		echo '<h1>AI Configuration (Ollama)</h1>';
		echo '<p>Configure the connection to your local Ollama instance.</p>';
		
		echo '<div class="card" style="max-width: 600px; padding: 1em; margin-bottom: 20px;">';
		echo '<h3>Connection Status: ' . esc_html( $status_icon ) . '</h3>';
		echo '</div>';

		echo '<form method="post" action="">';
		wp_nonce_field( 'knowledge_ai_save' );
		echo '<table class="form-table">';
		
		echo '<tr><th scope="row"><label for="ollama_url">Ollama URL</label></th>';
		echo '<td><input name="knowledge_ollama_url" type="url" id="ollama_url" value="' . esc_attr( $url ) . '" class="regular-text" required>';
		echo '<p class="description">Default: http://localhost:11434</p></td></tr>';

		echo '<tr><th scope="row"><label for="ollama_model">Model Name</label></th>';
		echo '<td>';
		
		if ( ! empty( $models ) ) {
			echo '<select name="knowledge_ollama_model" id="ollama_model" class="regular-text">';
			$found = false;
			foreach ( $models as $m ) {
				$selected = ( $model === $m );
				if ( $selected ) {
					$found = true;
				}
				echo '<option value="' . esc_attr( $m ) . '" ' . selected( $model, $m, false ) . '>' . esc_html( $m ) . '</option>';
			}
			// Preserve current value if not in list
			if ( ! $found && ! empty( $model ) ) {
				echo '<option value="' . esc_attr( $model ) . '" selected>' . esc_html( $model ) . ' (Current)</option>';
			}
			echo '</select>';
			echo '<p class="description">Select a model from your Ollama library.</p>';
		} else {
			echo '<input name="knowledge_ollama_model" type="text" id="ollama_model" value="' . esc_attr( $model ) . '" class="regular-text" required>';
			echo '<p class="description">e.g., llama3, mistral, nomic-embed-text</p>';
		}
		
		echo '</td></tr>';
		
		echo '</table>';
		echo submit_button( 'Save Settings', 'primary', 'knowledge_ollama_save' );
		echo '</form>';
		echo '</div>';

		// Index Management Section
		echo '<div class="card" style="max-width: 600px; padding: 1em; margin-bottom: 20px;">';
		echo '<h3>Knowledge Index</h3>';
		echo '<p>If your AI answers are missing context, you may need to rebuild the search index (embeddings). This process scans all Versions and generates vector embeddings using the configured Ollama model.</p>';
		echo '<form method="post" action="">';
		wp_nonce_field( 'knowledge_rebuild_index' );
		echo submit_button( 'Rebuild Knowledge Index', 'secondary', 'knowledge_rebuild_index' );
		echo '</form>';
		echo '</div>';
	}

	public function render_ingestion(): void {
		// Handle Form Submission
		if ( isset( $_POST['knowledge_ingest_url'] ) && check_admin_referer( 'knowledge_ingest_action' ) ) {
			$url = sanitize_text_field( $_POST['knowledge_ingest_url'] );
			
			// Schedule Async Ingestion
			if ( ! wp_next_scheduled( 'knowledge_async_ingest', [ $url ] ) ) {
				wp_schedule_single_event( time(), 'knowledge_async_ingest', [ $url ] );
				
				// Set Processing Flag
				set_transient( 'knowledge_ingest_processing', [
					'url'        => $url,
					'start_time' => time(),
				], 600 ); // 10 minutes timeout
			}
		}

		// Display Status Notices
		$processing = get_transient( 'knowledge_ingest_processing' );
		$result     = get_transient( 'knowledge_ingest_result' );

		if ( $processing ) {
			echo '<div class="notice notice-info"><p>Ingestion started for <strong>' . esc_html( $processing['url'] ) . '</strong>. This runs in the background. Please refresh this page in a few moments.</p></div>';
		}

		if ( $result && ! $processing ) {
			$class = ( $result['status'] === 'success' ) ? 'notice-success' : 'notice-error';
			echo '<div class="notice ' . $class . '"><p>' . wp_kses_post( $result['message'] ) . '</p></div>';
			
			// Clear result after showing
			delete_transient( 'knowledge_ingest_result' );
		}

		echo '<div class="wrap">';
		echo '<h1>Ingest Content</h1>';
		echo '<form method="post" action="">';
		wp_nonce_field( 'knowledge_ingest_action' );
		echo '<table class="form-table">';
		echo '<tr><th scope="row"><label for="url">URL to Ingest</label></th>';
		echo '<td><input name="knowledge_ingest_url" type="url" id="url" value="" class="regular-text" placeholder="https://example.com" required></td></tr>';
		echo '</table>';
		echo submit_button( 'Ingest URL' );
		echo '</form>';
		echo '</div>';
	}

	public static function process_async_ingestion( string $url ): void {
		try {
			// Manual instantiation for MVP
			$service = new \Knowledge\Service\Ingestion\IngestionService();
			$version = $service->ingest_url( $url );
			
			set_transient( 'knowledge_ingest_result', [
				'status'  => 'success',
				'message' => 'Successfully ingested: <strong>' . esc_html( $version->get_title() ) . '</strong> (UUID: ' . esc_html( $version->get_uuid() ) . ')',
			], DAY_IN_SECONDS );

		} catch ( \Exception $e ) {
			set_transient( 'knowledge_ingest_result', [
				'status'  => 'error',
				'message' => 'Error: ' . esc_html( $e->getMessage() ),
			], DAY_IN_SECONDS );
			
			// Log persistent failure
			\Knowledge\Infrastructure\FailureLog::log( $url, $e->getMessage() );
		} finally {
			// Clear processing flag
			delete_transient( 'knowledge_ingest_processing' );
		}
	}

	private function register_placeholder_submenu( string $title, string $slug ): void {
		add_submenu_page(
			'knowledge-main',
			$title,
			$title,
			'read',
			$slug,
			[ $this, 'render_placeholder' ]
		);
	}

	public function render_dashboard(): void {
		echo '<div class="wrap"><h1>Knowledge Dashboard</h1><p>Welcome to the Knowledge base.</p></div>';
	}

	public function render_operations(): void {
		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'overview';
		
		echo '<div class="wrap">';
		echo '<h1>Operations</h1>';
		
		echo '<nav class="nav-tab-wrapper">';
		echo '<a href="?page=knowledge-operations&tab=overview" class="nav-tab ' . ( $active_tab === 'overview' ? 'nav-tab-active' : '' ) . '">Overview</a>';
		echo '<a href="?page=knowledge-operations&tab=shortcodes" class="nav-tab ' . ( $active_tab === 'shortcodes' ? 'nav-tab-active' : '' ) . '">Shortcodes</a>';
		echo '</nav>';
		
		echo '<div style="margin-top: 20px;">';
		if ( $active_tab === 'overview' ) {
			$this->render_operations_overview();
		} elseif ( $active_tab === 'shortcodes' ) {
			$this->render_operations_shortcodes();
		}
		echo '</div>';
		echo '</div>';
	}

	public function render_operations_shortcodes(): void {
		echo '<div class="card" style="max-width: 800px; padding: 1em; margin-bottom: 20px;">';
		echo '<h3>Available Shortcodes</h3>';
		
		$shortcodes = [
			[
				'code' => '[knowledge_archive limit="12" columns="3"]',
				'desc' => 'Displays a grid of Knowledge Articles.',
				'args' => 'limit (int), columns (int), category (slug), tag (slug), ids (comma-sep list)',
			],
			[
				'code' => '[knowledge_search placeholder="Search..."]',
				'desc' => 'Displays a search form for Knowledge Articles.',
				'args' => 'placeholder (string), button_text (string)',
			],
			[
				'code' => '[knowledge_category_list style="list"]',
				'desc' => 'Displays a list of Knowledge Categories.',
				'args' => 'style (list|pills), show_count (true|false), hide_empty (true|false)',
			]
		];

		foreach ( $shortcodes as $sc ) {
			echo '<div style="margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px;">';
			echo '<h4>' . esc_html( explode( ' ', $sc['code'] )[0] ) . ']</h4>';
			echo '<p>' . esc_html( $sc['desc'] ) . '</p>';
			echo '<p><strong>Arguments:</strong> ' . esc_html( $sc['args'] ) . '</p>';
			
			echo '<div style="position: relative;">';
			echo '<textarea readonly class="code-block" style="width: 100%; height: 50px; font-family: monospace; background: #f0f0f1; padding: 10px; border: 1px solid #ccc;" id="sc_' . md5( $sc['code'] ) . '">' . esc_html( $sc['code'] ) . '</textarea>';
			echo '<button type="button" class="button copy-button" data-target="sc_' . md5( $sc['code'] ) . '" style="margin-top: 5px;">Copy</button>';
			echo '<span class="copy-success" style="display:none; color: green; margin-left: 10px;">Copied!</span>';
			echo '</div>';
			echo '</div>';
		}
		
		echo '<script>
		document.addEventListener("DOMContentLoaded", function() {
			document.querySelectorAll(".copy-button").forEach(function(btn) {
				btn.addEventListener("click", function() {
					var targetId = this.getAttribute("data-target");
					var copyText = document.getElementById(targetId);
					copyText.select();
					copyText.setSelectionRange(0, 99999);
					navigator.clipboard.writeText(copyText.value).then(function() {
						var successMsg = btn.nextElementSibling;
						successMsg.style.display = "inline";
						setTimeout(function() { successMsg.style.display = "none"; }, 2000);
					});
				});
			});
		});
		</script>';
		
		echo '</div>';
	}

	public function render_operations_overview(): void {
		if ( isset( $_POST['knowledge_flush_rewrite'] ) && check_admin_referer( 'knowledge_flush_rewrite' ) ) {
			flush_rewrite_rules();
			echo '<div class="notice notice-success"><p>Rewrite rules flushed successfully.</p></div>';
		}
		
		// Handle Failure Actions
		if ( isset( $_POST['knowledge_failure_action'] ) && check_admin_referer( 'knowledge_failure_action' ) ) {
			$action = sanitize_text_field( $_POST['knowledge_failure_action'] );
			$id     = sanitize_text_field( $_POST['knowledge_failure_id'] );
			$url    = sanitize_text_field( $_POST['knowledge_failure_url'] );

			if ( $action === 'resubmit' ) {
				// Reschedule
				if ( ! wp_next_scheduled( 'knowledge_async_ingest', [ $url ] ) ) {
					wp_schedule_single_event( time(), 'knowledge_async_ingest', [ $url ] );
					// Set processing flag immediately to reflect status
					set_transient( 'knowledge_ingest_processing', [
						'url'        => $url,
						'start_time' => time(),
					], 600 );
				}
				// Remove from failure log
				\Knowledge\Infrastructure\FailureLog::dismiss( $id );
				echo '<div class="notice notice-success"><p>Job resubmitted: ' . esc_html( $url ) . '</p></div>';
			} elseif ( $action === 'dismiss' ) {
				\Knowledge\Infrastructure\FailureLog::dismiss( $id );
				echo '<div class="notice notice-success"><p>Failure log dismissed.</p></div>';
			}
		}

		// Gather Stats
		$article_stats = wp_count_posts( 'kb_article' );
		$version_stats = wp_count_posts( 'kb_version' );
		$fork_stats    = wp_count_posts( 'kb_fork' );
		
		// Gather Queue
		$crons = _get_cron_array();
		$knowledge_jobs = [];
		if ( ! empty( $crons ) ) {
			foreach ( $crons as $timestamp => $cronhooks ) {
				foreach ( $cronhooks as $hook => $keys ) {
					if ( str_starts_with( $hook, 'knowledge_' ) || str_starts_with( $hook, 'kb_' ) ) {
						foreach ( $keys as $key => $data ) {
							$knowledge_jobs[] = [
								'timestamp' => $timestamp,
								'hook'      => $hook,
								'args'      => $data['args'],
							];
						}
					}
				}
			}
		}

		// Gather Recent Activity (Last 5 versions)
		$recent_versions = get_posts( [
			'post_type'      => 'kb_version',
			'posts_per_page' => 5,
			'orderby'        => 'date',
			'order'          => 'DESC',
		] );

		// 1. Stats Section
		echo '<div class="card" style="max-width: 800px; padding: 1em; margin-bottom: 20px;">';
		echo '<h3>System Statistics</h3>';
		echo '<table class="widefat striped">';
		echo '<thead><tr><th>Metric</th><th>Count</th></tr></thead>';
		echo '<tbody>';
		echo '<tr><td><strong>Articles (Published)</strong></td><td>' . intval( $article_stats->publish ?? 0 ) . '</td></tr>';
		echo '<tr><td><strong>Versions (Total)</strong></td><td>' . intval( ( $version_stats->publish ?? 0 ) + ( $version_stats->draft ?? 0 ) + ( $version_stats->private ?? 0 ) ) . '</td></tr>';
		echo '<tr><td><strong>Forks</strong></td><td>' . intval( $fork_stats->publish ?? 0 ) . '</td></tr>';
		echo '</tbody></table>';
		echo '</div>';

		// 2. Queue Section
		echo '<div class="card" style="max-width: 800px; padding: 1em; margin-bottom: 20px;">';
		echo '<h3>Background Jobs & Queue</h3>';
		
		// 2.1 Failed Jobs Section
		$failures = \Knowledge\Infrastructure\FailureLog::get_failures();
		if ( ! empty( $failures ) ) {
			echo '<div style="background: #fff5f5; border-left: 4px solid #dc3232; padding: 10px; margin-bottom: 20px;">';
			echo '<h4 style="margin-top: 0; color: #dc3232;">Failed Ingestions</h4>';
			echo '<table class="widefat striped">';
			echo '<thead><tr><th>Time</th><th>URL</th><th>Error</th><th>Actions</th></tr></thead>';
			echo '<tbody>';
			foreach ( $failures as $fail ) {
				echo '<tr>';
				echo '<td>' . human_time_diff( $fail['timestamp'] ) . ' ago</td>';
				echo '<td>' . esc_html( $fail['url'] ) . '</td>';
				echo '<td>' . esc_html( $fail['error'] ) . '</td>';
				echo '<td>
					<form method="post" action="" style="display:inline;">
						' . wp_nonce_field( 'knowledge_failure_action', '_wpnonce', true, false ) . '
						<input type="hidden" name="knowledge_failure_id" value="' . esc_attr( $fail['id'] ) . '">
						<input type="hidden" name="knowledge_failure_url" value="' . esc_attr( $fail['url'] ) . '">
						<button type="submit" name="knowledge_failure_action" value="resubmit" class="button button-small button-primary">Resubmit</button>
						<button type="submit" name="knowledge_failure_action" value="dismiss" class="button button-small">Dismiss</button>
					</form>
				</td>';
				echo '</tr>';
			}
			echo '</tbody></table>';
			echo '</div>';
		}
		
		// Check for active jobs (via JobTracker)
		$active_jobs = \Knowledge\Infrastructure\JobTracker::get_active_jobs();
		
		// Legacy transient check (for ingestion if not migrated yet, though we want to unify)
		// We can keep it as a fallback or merge it.
		$processing = get_transient( 'knowledge_ingest_processing' );
		if ( $processing ) {
			echo '<div class="notice notice-info inline" style="margin: 0 0 10px 0;"><p><strong>Active Ingestion:</strong> ' . esc_html( $processing['url'] ) . ' (Started ' . human_time_diff( $processing['start_time'] ) . ' ago)</p></div>';
		}
		
		if ( ! empty( $active_jobs ) ) {
			echo '<table class="widefat striped" style="margin-bottom: 15px;">';
			echo '<thead><tr><th>Type</th><th>Description</th><th>Started</th></tr></thead>';
			echo '<tbody>';
			foreach ( $active_jobs as $job ) {
				echo '<tr>';
				echo '<td>' . esc_html( $job['type'] ) . '</td>';
				echo '<td>' . esc_html( $job['description'] ) . '</td>';
				echo '<td>' . human_time_diff( $job['start_time'] ) . ' ago</td>';
				echo '</tr>';
			}
			echo '</tbody></table>';
		}

		if ( empty( $knowledge_jobs ) && empty( $active_jobs ) && ! $processing ) {
			echo '<p>No scheduled or active jobs.</p>';
		} elseif ( ! empty( $knowledge_jobs ) ) {
			echo '<h4>Scheduled Queue (Waiting)</h4>';
			echo '<table class="widefat striped">';
			echo '<thead><tr><th>Scheduled Time</th><th>Job Name</th><th>Arguments</th></tr></thead>';
			echo '<tbody>';
			foreach ( $knowledge_jobs as $job ) {
				$time_diff = human_time_diff( time(), $job['timestamp'] );
				$timing = ( $job['timestamp'] < time() ) ? "$time_diff ago (Due)" : "In $time_diff";
				
				echo '<tr>';
				echo '<td>' . esc_html( $timing ) . '</td>';
				echo '<td>' . esc_html( $job['hook'] ) . '</td>';
				echo '<td>' . esc_html( json_encode( $job['args'] ) ) . '</td>';
				echo '</tr>';
			}
			echo '</tbody></table>';
		}
		echo '</div>';

		// 3. Recent Activity Section
		echo '<div class="card" style="max-width: 800px; padding: 1em; margin-bottom: 20px;">';
		echo '<h3>Recent Activity</h3>';
		if ( empty( $recent_versions ) ) {
			echo '<p>No recent activity.</p>';
		} else {
			echo '<table class="widefat striped">';
			echo '<thead><tr><th>Time</th><th>Action</th><th>Item</th></tr></thead>';
			echo '<tbody>';
			foreach ( $recent_versions as $version ) {
				$parent_title = get_the_title( $version->post_parent );
				$uuid = get_post_meta( $version->ID, '_kb_version_uuid', true );
				echo '<tr>';
				echo '<td>' . get_the_date( 'Y-m-d H:i:s', $version ) . '</td>';
				echo '<td>New Version</td>';
				echo '<td><a href="' . get_edit_post_link( $version->post_parent ) . '">' . esc_html( $parent_title ) . '</a> <span class="description">(' . substr( $uuid, 0, 8 ) . '...)</span></td>';
				echo '</tr>';
			}
			echo '</tbody></table>';
		}
		echo '</div>';

		// 4. Test Content Extraction
		$extraction_result = null;
		if ( isset( $_POST['knowledge_test_extraction'] ) && check_admin_referer( 'knowledge_test_extraction' ) ) {
			$url = sanitize_text_field( $_POST['knowledge_test_url'] );
			try {
				$fetcher = new \Knowledge\Service\Ingestion\HtmlFetcher();
				$normalizer = new \Knowledge\Service\Ingestion\ContentNormalizer();
				
				$source = new \Knowledge\Domain\ValueObject\Source( $url );
				$raw_html = $fetcher->fetch( $source );
				$data = $normalizer->normalize( $raw_html );
				
				$extraction_result = $data;
			} catch ( \Exception $e ) {
				$extraction_result = [ 'error' => $e->getMessage() ];
			}
		}

		echo '<div class="card" style="max-width: 800px; padding: 1em; margin-bottom: 20px;">';
		echo '<h3>Test Content Extraction</h3>';
		echo '<p>Enter a URL to preview how the content will be extracted and cleaned using the latest normalization logic.</p>';
		echo '<form method="post" action="">';
		wp_nonce_field( 'knowledge_test_extraction' );
		echo '<table class="form-table">';
		echo '<tr><th scope="row"><label for="test_url">URL</label></th>';
		echo '<td><input name="knowledge_test_url" type="url" id="test_url" value="' . ( isset($_POST['knowledge_test_url']) ? esc_attr($_POST['knowledge_test_url']) : '' ) . '" class="regular-text" style="width: 100%;" placeholder="https://example.com" required></td></tr>';
		echo '</table>';
		echo submit_button( 'Test Extraction', 'secondary', 'knowledge_test_extraction' );
		echo '</form>';

		if ( $extraction_result ) {
			echo '<hr>';
			if ( isset( $extraction_result['error'] ) ) {
				echo '<div class="notice notice-error inline"><p>Error: ' . esc_html( $extraction_result['error'] ) . '</p></div>';
			} else {
				echo '<h4>Extracted Title: ' . esc_html( $extraction_result['title'] ) . '</h4>';
				echo '<p><strong>Preview (Rendered):</strong></p>';
				echo '<div class="knowledge-preview" style="background: #fff; padding: 20px; border: 1px solid #ddd; margin-top: 10px; max-height: 400px; overflow: auto;">';
				echo $extraction_result['content']; 
				echo '</div>';
				echo '<p><strong>Metadata:</strong></p><pre>' . esc_html( print_r( $extraction_result['metadata'], true ) ) . '</pre>';
			}
		}
		echo '</div>';
		
		// 5. Maintenance Section
		echo '<div class="card" style="max-width: 800px; padding: 1em; margin-bottom: 20px;">';
		echo '<h3>System Maintenance</h3>';
		echo '<p><strong>Flush Rewrite Rules:</strong> Use this if you are experiencing 404 errors on content or images.</p>';
		echo '<form method="post" action="">';
		wp_nonce_field( 'knowledge_flush_rewrite' );
		echo submit_button( 'Flush Rewrite Rules', 'secondary', 'knowledge_flush_rewrite' );
		echo '</form>';
		echo '</div>';
	}

	public function render_placeholder(): void {
		echo '<div class="wrap"><h1>' . esc_html( get_admin_page_title() ) . '</h1><p>Coming soon...</p></div>';
	}
}
