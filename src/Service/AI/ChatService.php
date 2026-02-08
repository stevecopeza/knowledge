<?php

namespace Knowledge\Service\AI;

use Knowledge\Service\AI\ProviderManager;

class ChatService {
	private ProviderManager $provider_manager;
	private VectorStore $store;

	public function __construct() {
		$this->provider_manager = new ProviderManager();
		$this->store            = new VectorStore();
	}

	public function ask( string $question, string $mode = 'combined_prioritised' ): array {
		// Embeddings always use primary provider
		try {
			// 1. Embed Question (if needed)
			$context_text = "";
			if ( $mode !== 'llm_only' ) {
				$query_vec = $this->provider_manager->embed( $question );
				if ( empty( $query_vec ) ) {
					return [ 
						'answer' => "Error: Failed to generate embedding for the question.",
						'provenance' => []
					];
				}

				// 2. Retrieve Context
				$results = $this->store->search( $query_vec, 3 ); // Top 3 chunks
				
				if ( empty( $results ) ) {
					$context_text = "No specific context found in the knowledge base.";
				} else {
					$context_text = "Context:\n";
					foreach ( $results as $r ) {
						$context_text .= "---\n" . $r['text'] . "\n";
					}
				}
			}
		} catch ( \Exception $e ) {
			return [ 
				'answer' => "Error during retrieval: " . $e->getMessage(),
				'provenance' => []
			];
		}

		// 3. Construct Prompt based on Mode
		$prompt = "";
		// Log the mode for debugging
		error_log( "ChatService: Mode selected: " . $mode );

		if ( $mode === 'llm_only' ) {
			$prompt = <<<EOT
You are a helpful assistant.
Answer the user's question to the best of your ability using your general knowledge.

Question: $question
Answer:
EOT;
		} elseif ( $mode === 'combined_balanced' || $mode === 'combined' ) {
			$prompt = <<<EOT
You are a helpful assistant for a personal knowledge base.
Answer the user's question using the provided context.
If the context is insufficient, you may use your general knowledge to answer, but please mention if the information comes from outside the knowledge base.

$context_text

Question: $question
Answer:
EOT;
		} elseif ( $mode === 'rag_only' ) {
			$prompt = <<<EOT
You are a helpful assistant for a personal knowledge base.
Answer the user's question based ONLY on the provided context below.
If the answer is not in the context, say "I don't have enough information in the knowledge base to answer this."
Do not invent facts.

$context_text

Question: $question
Answer:
EOT;
		} else { // combined_prioritised (Default)
			$prompt = <<<EOT
You are a helpful assistant for a personal knowledge base.
Answer the user's question using the provided context.
If the context is insufficient, you may supplement with general knowledge to provide a complete answer, but prioritize the information from the knowledge base.

$context_text

Question: $question
Answer:
EOT;
		}

		// 4. Generate Answer with Failover
		error_log( "ChatService: Final Prompt:\n" . $prompt );
		
		try {
			$result = $this->provider_manager->chat_with_failover( $prompt );
			return [
				'answer' => $result['answer'],
				'provenance' => [
					'provider_id' => $result['provider']->get_id(),
					'provider_name' => $result['provider']->get_name(),
					'model' => $result['provider']->get_model(),
				]
			];
		} catch ( \Exception $e ) {
			return [
				'answer' => "Error: " . $e->getMessage(),
				'provenance' => []
			];
		}
	}
}
