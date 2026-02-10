<?php
/**
 * Plugin Name: Knowledge
 * Plugin URI:  https://github.com/stevecopeza/knowledge
 * Description: A local-first, ownership-first knowledge repository designed for epistemic durability.
 * Version:     0.1.1
 * Author:      Steve Cope
 * Author URI:  https://cope.zone
 * Text Domain: knowledge
 * Domain Path: /languages
 * Requires PHP: 8.0
 */

namespace Knowledge;

use Knowledge\Infrastructure\Plugin;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Constants
define( 'KNOWLEDGE_VERSION', '0.1.0' );
define( 'KNOWLEDGE_PATH', plugin_dir_path( __FILE__ ) );
define( 'KNOWLEDGE_URL', plugin_dir_url( __FILE__ ) );
define( 'KNOWLEDGE_DATA_PATH', WP_CONTENT_DIR . '/kb-data' );

// Load Autoloader
if ( file_exists( KNOWLEDGE_PATH . 'vendor/autoload.php' ) ) {
	require KNOWLEDGE_PATH . 'vendor/autoload.php';
}

// Initialize Plugin
function knowledge_init() {
	static $plugin = null;
	if ( $plugin === null ) {
		$plugin = new Plugin();
		$plugin->run();
	}
	return $plugin;
}

add_action( 'plugins_loaded', 'Knowledge\\knowledge_init' );

// Register Activation Hook
register_activation_hook( __FILE__, [ 'Knowledge\\Infrastructure\\Plugin', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'Knowledge\\Infrastructure\\Plugin', 'deactivate' ] );
