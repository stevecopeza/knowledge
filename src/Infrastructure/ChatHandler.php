<?php

namespace Knowledge\Infrastructure;

use Knowledge\Service\AI\ChatService;

class ChatHandler {

	public function init(): void {
		add_action( 'wp_ajax_knowledge_chat', [ $this, 'handle_chat' ] );
	}

	public function handle_chat(): void {
		check_ajax_referer( 'knowledge_chat_nonce', 'nonce' );

		if ( ! current_user_can( 'read' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$question = sanitize_text_field( $_POST['question'] ?? '' );
		$mode     = sanitize_text_field( $_POST['mode'] ?? 'rag_only' );
		
		// Validate mode
		if ( ! in_array( $mode, [ 'rag_only', 'llm_only', 'combined', 'combined_prioritised', 'combined_balanced' ], true ) ) {
			$mode = 'rag_only';
		}

		if ( empty( $question ) ) {
			wp_send_json_error( 'Empty question' );
		}

		try {
			// Manual instantiation for MVP
			$service = new ChatService();
			$result  = $service->ask( $question, $mode );
			
			// Generate Cards HTML
			$cards_html = '';
			if ( ! empty( $result['provenance']['sources'] ) ) {
				// Decode display options
				$display_options = [];
				if ( ! empty( $_POST['options'] ) ) {
					$display_options = json_decode( stripslashes( $_POST['options'] ), true );
					if ( ! is_array( $display_options ) ) {
						$display_options = [];
					}
				}

				$article_ids = $this->resolve_articles( $result['provenance']['sources'] );
				foreach ( $article_ids as $id ) {
					$post = get_post( $id );
					if ( $post ) {
						$cards_html .= FrontendRenderer::render_card( $post, $display_options );
					}
				}
			}
			
			wp_send_json_success( [ 
				'answer'     => $result['answer'],
				'provenance' => $result['provenance'],
				'cards_html' => $cards_html,
			] );

		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	private function resolve_articles( array $uuids ): array {
		if ( empty( $uuids ) ) {
			return [];
		}

		$args = [
			'post_type'      => 'kb_version',
			'meta_query'     => [
				[
					'key'     => '_kb_version_uuid',
					'value'   => $uuids,
					'compare' => 'IN',
				],
			],
			'fields'         => 'ids',
			'posts_per_page' => -1,
		];

		$version_ids = get_posts( $args );
		$article_ids = [];

		foreach ( $version_ids as $vid ) {
			$parent = wp_get_post_parent_id( $vid );
			if ( $parent ) {
				$article_ids[] = $parent;
			}
		}

		return array_unique( $article_ids );
	}
}
