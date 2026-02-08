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
		if ( empty( $question ) ) {
			wp_send_json_error( 'Empty question' );
		}

		try {
			// Manual instantiation for MVP
			$service = new ChatService();
			$answer  = $service->ask( $question );
			
			wp_send_json_success( [ 'answer' => $answer ] );

		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}
}
