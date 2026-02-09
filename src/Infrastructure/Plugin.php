<?php

namespace Knowledge\Infrastructure;

use Knowledge\Service\Search\SearchIndexService;
use Knowledge\Integration\Elementor\ElementorIntegration;

class Plugin {

	/**
	 * Main entry point for the plugin.
	 */
	public function run(): void {
		// Bootstrap services
		$this->register_services();
	}

	/**
	 * Register core services and hooks.
	 */
	private function register_services(): void {
		// Future: Dependency Injection Container registration
		
		// Initialize Custom Post Types
		$cpt_registrar = new CptRegistrar();
		$cpt_registrar->init();

		// Initialize Admin Menu
		$admin_menu = new AdminMenuRegistrar();
		$admin_menu->init();

		// Initialize Admin Columns
		$admin_columns = new AdminColumnsRegistrar();
		$admin_columns->init();

		// Initialize Version Viewer
		$version_viewer = new VersionViewer();
		$version_viewer->init();

		// Initialize Fork Handler
		$fork_handler = new ForkHandler();
		$fork_handler->init();

		// Initialize Search Handler
		$search_handler = new SearchHandler();
		$search_handler->init();

		// Initialize Chat Handler
		$chat_handler = new ChatHandler();
		$chat_handler->init();

		// Initialize File Proxy
		$file_proxy = new FileProxyController();
		$file_proxy->init();

		// Initialize Frontend Renderer
		$frontend_renderer = new FrontendRenderer();
		$frontend_renderer->init();

		// Initialize Elementor Integration
		$elementor_integration = new ElementorIntegration();
		$elementor_integration->init();

		// Register Async Ingestion Handler
		add_action( 'knowledge_async_ingest', [ AdminMenuRegistrar::class, 'process_async_ingestion' ], 10, 3 );

		// Register Batch Import Queue Processor
		add_action( 'knowledge_process_import_queue', [ \Knowledge\Service\Ingestion\BatchImportService::class, 'process_queue' ] );

		// Register AI Embedding Job
		add_action( 'kb_version_created', [ \Knowledge\Service\AI\EmbeddingJob::class, 'schedule' ], 10, 4 );
		add_action( 'knowledge_generate_embeddings', [ \Knowledge\Service\AI\EmbeddingJob::class, 'process' ] );

		// Register AI Analysis Job
		add_action( 'knowledge_ai_analyze_article', [ \Knowledge\Service\AI\AIAnalysisService::class, 'handle_analysis_job' ], 10, 2 );

		// Initialize Filesystem Security (Runtime check)
		add_action( 'init', [ FilesystemInitializer::class, 'ensure_security' ] );
	}

	/**
	 * Plugin Activation Hook.
	 */
	public static function activate(): void {
		// 1. Create Database Tables
		DatabaseInstaller::install();

		// 2. Register CPTs (needed for rewrite flush)
		$cpt = new CptRegistrar();
		$cpt->register_post_types();
		$cpt->register_taxonomies();

		// 3. Flush Rewrite Rules
		flush_rewrite_rules();

		// 4. Initialize Filesystem
		FilesystemInitializer::init();
	}

	/**
	 * Plugin Deactivation Hook.
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
