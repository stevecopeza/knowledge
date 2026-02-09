<?php

namespace Knowledge\Integration\Elementor;

class ElementorIntegration {

	/**
	 * Initialize the Elementor integration.
	 */
	public function init(): void {
		// Only proceed if Elementor is installed and active
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

		// Register Widget Category
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_categories' ] );

		// Register Widgets
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
	}

	/**
	 * Register the Knowledge Base category in Elementor.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager
	 */
	public function register_categories( $elements_manager ): void {
		$elements_manager->add_category(
			'knowledge-base',
			[
				'title' => __( 'Knowledge Base', 'knowledge' ),
				'icon'  => 'fa fa-book',
			]
		);
	}

	/**
	 * Register Elementor Widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager
	 */
	public function register_widgets( $widgets_manager ): void {
		// Require the widget class file
		require_once __DIR__ . '/Widgets/KnowledgeArchiveWidget.php';

		// Register the widget
		$widgets_manager->register( new Widgets\KnowledgeArchiveWidget() );
	}
}
