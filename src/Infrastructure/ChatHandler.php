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
			
			wp_send_json_success( [ 
				'answer'     => $result['answer'],
				'provenance' => $result['provenance']
			] );

		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}
}
