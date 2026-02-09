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

	public function ask( string $question, string $mode = 'combined_prioritised', ?int $project_id = null ): array {
		// Embeddings always use primary provider
		$source_uuids = [];
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

				// 2. Prepare Project Scope (if active)
				$allowed_uuids = [];
				if ( $project_id ) {
					$allowed_uuids = $this->get_project_version_uuids( $project_id );
					if ( empty( $allowed_uuids ) ) {
						// Project is empty, so no context can be found
						$context_text = "No context found (Project is empty).";
					}
				}

				// 3. Retrieve Context
				// Only search if we don't have an empty restricted scope
				if ( ! ($project_id && empty( $allowed_uuids )) ) {
					$results = $this->store->search( $query_vec, 3, $allowed_uuids ); // Top 3 chunks
					
					if ( empty( $results ) ) {
						$context_text = "No specific context found in the knowledge base.";
					} else {
						$context_text = "Context:\n";
						foreach ( $results as $r ) {
							$context_text .= "---\n" . $r['text'] . "\n";
							if ( ! empty( $r['version_uuid'] ) ) {
								$source_uuids[] = $r['version_uuid'];
							}
						}
						$source_uuids = array_values( array_unique( $source_uuids ) );
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
					'sources' => $source_uuids,
				]
			];
		} catch ( \Exception $e ) {
			return [
				'answer' => "Error: " . $e->getMessage(),
				'provenance' => []
			];
		}
	}

	private function get_project_version_uuids( int $project_id ): array {
		$project_service = new \Knowledge\Service\Project\ProjectService();
		$article_ids = $project_service->get_members_by_type( $project_id, 'kb_article' );

		if ( empty( $article_ids ) ) {
			return [];
		}

		global $wpdb;
		$placeholders = implode( ',', array_fill( 0, count( $article_ids ), '%d' ) );
		
		// Get UUIDs of all versions belonging to these articles
		$sql = "SELECT meta_value FROM $wpdb->postmeta 
				JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->postmeta.post_id
				WHERE post_type = 'kb_version' 
				AND post_parent IN ($placeholders)
				AND meta_key = '_kb_version_uuid'";
				
		return $wpdb->get_col( $wpdb->prepare( $sql, $article_ids ) );
	}
}
