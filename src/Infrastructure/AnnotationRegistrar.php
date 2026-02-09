<?php

namespace Knowledge\Infrastructure;

use Knowledge\Service\Annotation\AnnotationService;

class AnnotationRegistrar {

	private AnnotationService $service;

	public function __construct() {
		$this->service = new AnnotationService();
	}

	public function init(): void {
		add_action( 'rest_api_init', [ $this, 'register_rest_fields' ] );
		add_action( 'before_delete_post', [ $this, 'handle_delete' ] );
		add_filter( 'rest_kb_note_collection_params', [ $this, 'add_collection_params' ] );
		add_filter( 'rest_kb_note_query', [ $this, 'filter_by_source' ], 10, 2 );
	}

	public function add_collection_params( array $params ): array {
		$params['source'] = [
			'description'       => 'Filter notes by source UUID (Article Version).',
			'type'              => 'string',
			'validate_callback' => 'rest_validate_request_arg',
		];
		return $params;
	}

	public function filter_by_source( array $args, \WP_REST_Request $request ): array {
		if ( $request->has_param( 'source' ) ) {
			$args['meta_key']   = '_kb_note_source';
			$args['meta_value'] = $request->get_param( 'source' );
		}
		return $args;
	}

	public function register_rest_fields(): void {
		register_rest_field( 'kb_note', 'target', [
			'get_callback'    => [ $this, 'get_target_field' ],
			'update_callback' => [ $this, 'update_target_field' ],
			'schema'          => [
				'description' => 'Annotation target selector and source.',
				'type'        => 'object',
				'context'     => [ 'view', 'edit' ],
			],
		] );

		register_rest_field( 'kb_note', 'author_details', [
			'get_callback' => [ $this, 'get_author_details' ],
			'schema'       => [
				'description' => 'Author name and avatar.',
				'type'        => 'object',
				'context'     => [ 'view', 'edit' ],
			],
		] );
	}

	public function get_author_details( array $post ): array {
		$author_id = isset( $post['author'] ) ? $post['author'] : 0;
		$avatar    = get_avatar_url( $author_id, [ 'size' => 48 ] );

		return [
			'name'   => get_the_author_meta( 'display_name', $author_id ) ?: 'Unknown',
			'avatar' => $avatar ?: '',
		];
	}

	/**
	 * Get the target field value.
	 * 
	 * @param array $post Post data array from REST.
	 */
	public function get_target_field( array $post ): ?array {
		$data = $this->service->get_target( $post['id'] );
		return $data ? $data['target'] : null;
	}

	/**
	 * Update the target field value.
	 *
	 * @param mixed    $value      The value of the field.
	 * @param \WP_Post $post       The post object.
	 * @param string   $field_name The name of the field.
	 */
	public function update_target_field( $value, $post, $field_name ) {
		if ( ! is_array( $value ) ) {
			return new \WP_Error( 'rest_invalid_param', 'Target must be an object.', [ 'status' => 400 ] );
		}
		$saved = $this->service->save_target( $post->ID, $value );
		if ( ! $saved ) {
			return new \WP_Error( 'rest_update_failed', 'Failed to save annotation target.', [ 'status' => 500 ] );
		}
		return true;
	}

	public function handle_delete( int $post_id ): void {
		if ( get_post_type( $post_id ) === 'kb_note' ) {
			$this->service->delete_target( $post_id );
		}
	}
}
