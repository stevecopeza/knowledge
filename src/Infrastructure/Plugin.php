<?php

namespace Knowledge\Infrastructure;

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
