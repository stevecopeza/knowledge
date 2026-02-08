<?php

namespace Knowledge\Service\AI;

use Knowledge\Service\AI\Provider\ProviderInterface;
use Knowledge\Service\AI\Provider\OllamaProvider;

class ProviderManager {
	/** @var ProviderInterface[] */
	private array $providers = [];

	public function __construct() {
		$this->load_providers();
	}

	private function load_providers(): void {
		$saved_providers = get_option( 'knowledge_ai_providers', [] );

		if ( empty( $saved_providers ) ) {
			// Migration/Fallback to legacy settings
			$url   = get_option( 'knowledge_ollama_url', 'http://192.168.5.183:11434' );
			$model = get_option( 'knowledge_ollama_model', 'llama3' );
			
			// Create a default provider
			$this->providers[] = new OllamaProvider(
				'default-ollama',
				'Default Ollama',
				[ 'url' => $url, 'model' => $model ]
			);
			return;
		}

		foreach ( $saved_providers as $p_data ) {
			if ( empty( $p_data['enabled'] ) ) {
				continue;
			}

			switch ( $p_data['type'] ) {
				case 'ollama':
					$this->providers[] = new OllamaProvider(
						$p_data['id'],
						$p_data['name'],
						$p_data['config']
					);
					break;
				case 'openai':
					$this->providers[] = new \Knowledge\Service\AI\Provider\OpenAIProvider(
						$p_data['id'],
						$p_data['name'],
						$p_data['config']
					);
					break;
			}
		}
	}

	/**
	 * @return ProviderInterface[]
	 */
	public function get_all_providers(): array {
		return $this->providers;
	}

	/**
	 * Execute a chat request with failover.
	 * Returns an array: ['answer' => string, 'provider' => ProviderInterface]
	 */
	public function chat_with_failover( string $prompt ): array {
		$errors = [];

		foreach ( $this->providers as $provider ) {
			try {
				if ( ! $provider->is_available() ) {
					$errors[] = $provider->get_name() . ': Unavailable';
					continue;
				}

				$answer = $provider->chat( $prompt );
				return [
					'answer'   => $answer,
					'provider' => $provider,
				];

			} catch ( \Exception $e ) {
				$errors[] = $provider->get_name() . ': ' . $e->getMessage();
				error_log( "AI Provider Failover: " . $provider->get_name() . " failed. " . $e->getMessage() );
			}
		}

		throw new \RuntimeException( "All AI providers failed: " . implode( '; ', $errors ) );
	}

	/**
	 * Execute an embedding request with failover.
	 * Note: Embeddings should typically use a consistent model. Mixing models for embeddings breaks search.
	 * For now, we only use the PRIMARY provider for embeddings to ensure consistency.
	 */
	public function embed( string $text ): array {
		if ( empty( $this->providers ) ) {
			throw new \RuntimeException( "No AI providers configured." );
		}

		// Always use the first provider for embeddings to maintain vector space consistency
		$primary = $this->providers[0];
		return $primary->embed( $text );
	}
}
