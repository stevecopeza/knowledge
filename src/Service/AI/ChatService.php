<?php

namespace Knowledge\Service\AI;

class ChatService {
	private OllamaClient $client;
	private VectorStore $store;

	public function __construct() {
		$this->client = new OllamaClient();
		$this->store  = new VectorStore();
	}

	public function ask( string $question ): string {
		if ( ! $this->client->is_available() ) {
			return "Error: AI Service is not available. Please check your configuration.";
		}

		// 1. Embed Question
		$query_vec = $this->client->embed( $question );
		if ( empty( $query_vec ) ) {
			return "Error: Failed to generate embedding for the question.";
		}

		// 2. Retrieve Context
		$results = $this->store->search( $query_vec, 3 ); // Top 3 chunks
		
		$context_text = "";
		if ( empty( $results ) ) {
			$context_text = "No specific context found in the knowledge base.";
		} else {
			$context_text = "Context:\n";
			foreach ( $results as $r ) {
				$context_text .= "---\n" . $r['text'] . "\n";
			}
		}

		// 3. Construct Prompt
		$prompt = <<<EOT
You are a helpful assistant for a personal knowledge base.
Answer the user's question based ONLY on the provided context below.
If the answer is not in the context, say "I don't have enough information in the knowledge base to answer this."
Do not invent facts.

$context_text

Question: $question
Answer:
EOT;

		// 4. Generate Answer
		return $this->client->chat( $prompt );
	}
}
