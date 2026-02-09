<?php

namespace Knowledge\Infrastructure;

class CptRegistrar {

	public function init(): void {
		add_action( 'init', [ $this, 'register_post_types' ] );
		add_action( 'init', [ $this, 'register_taxonomies' ] );
	}

	public function register_post_types(): void {
		$this->register_article();
		$this->register_version();
		$this->register_fork();
		$this->register_project();
		$this->register_note();
		$this->register_import_job();
	}

	public function register_taxonomies(): void {
		$this->register_category();
		$this->register_tag();
	}

	private function register_article(): void {
		register_post_type( 'kb_article', [
			'labels'       => [
				'name'          => 'Articles',
				'singular_name' => 'Article',
				'add_new'       => 'Add New',
				'add_new_item'  => 'Add New Article',
				'edit_item'     => 'Edit Article',
			],
			'public'       => true,
			'has_archive'  => true,
			'show_in_menu' => 'knowledge-main',
			'menu_icon'    => 'dashicons-book',
			'supports'     => [ 'title', 'author', 'revisions', 'thumbnail' ],
			'show_in_rest' => true,
			'rewrite'      => [ 'slug' => 'kb' ],
		] );
	}

	private function register_version(): void {
		register_post_type( 'kb_version', [
			'labels'       => [
				'name'          => 'Versions',
				'singular_name' => 'Version',
			],
			'public'       => false, // Internal use mostly
			'show_ui'      => true,
			'show_in_menu' => 'knowledge-main',
			'supports'     => [ 'title', 'author' ],
			'capabilities' => [
				'create_posts' => 'do_not_allow', // Immutable via UI
			],
			'map_meta_cap' => true,
		] );
	}

	private function register_fork(): void {
		register_post_type( 'kb_fork', [
			'labels'       => [
				'name'          => 'Forks',
				'singular_name' => 'Fork',
			],
			'public'       => true,
			'show_ui'      => true,
			'show_in_menu' => 'knowledge-main',
			'supports'     => [ 'title', 'editor', 'author' ],
		] );
	}

	private function register_project(): void {
		register_post_type( 'kb_project', [
			'labels'       => [
				'name'          => 'Projects',
				'singular_name' => 'Project',
			],
			'public'       => true,
			'show_ui'      => true,
			'show_in_menu' => 'knowledge-main',
			'menu_icon'    => 'dashicons-portfolio',
			'supports'     => [ 'title', 'editor', 'author' ],
		] );
	}

	private function register_note(): void {
		register_post_type( 'kb_note', [
			'labels'       => [
				'name'          => 'Notes',
				'singular_name' => 'Note',
				'add_new'       => 'Add New',
				'add_new_item'  => 'Add New Note',
				'edit_item'     => 'Edit Note',
			],
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => 'knowledge-main',
			'menu_icon'    => 'dashicons-sticky',
			'supports'     => [ 'title', 'editor', 'author' ],
			'show_in_rest' => true,
		] );
	}

	private function register_import_job(): void {
		register_post_type( 'kb_import_job', [
			'labels'       => [
				'name'          => 'Import Jobs',
				'singular_name' => 'Import Job',
			],
			'public'       => false,
			'show_ui'      => false, // Hidden from menu, managed via custom page
			'supports'     => [ 'title', 'author' ],
			'capabilities' => [
				'create_posts' => 'do_not_allow',
			],
			'map_meta_cap' => true,
		] );
	}

	private function register_category(): void {
		register_taxonomy( 'kb_category', [ 'kb_article' ], [
			'labels'            => [
				'name'          => 'Knowledge Categories',
				'singular_name' => 'Category',
			],
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
		] );
	}

	private function register_tag(): void {
		register_taxonomy( 'kb_tag', [ 'kb_article', 'kb_note' ], [
			'labels'            => [
				'name'          => 'Knowledge Tags',
				'singular_name' => 'Tag',
			],
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
		] );
	}
}
