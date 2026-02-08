<?php

namespace Knowledge\Service\AI\Provider;

use Knowledge\Service\AI\OllamaClient;

class OllamaProvider implements ProviderInterface {
	private string $id;
	private string $name;
	private string $url;
	private string $model;
	private OllamaClient $client;

	public function __construct( string $id, string $name, array $config ) {
		$this->id    = $id;
		$this->name  = $name;
		$this->url   = $config['url'] ?? 'http://127.0.0.1:11434';
		$this->model = $config['model'] ?? 'llama3';
		
		$this->client = new OllamaClient( $this->url, $this->model );
	}

	public function get_id(): string {
		return $this->id;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function get_type(): string {
		return 'ollama';
	}

	public function get_model(): string {
		return $this->model;
	}

	public function is_available(): bool {
		return $this->client->is_available();
	}

	public function chat( string $prompt ): string {
		return $this->client->chat( $prompt );
	}

	public function embed( string $text ): array {
		try {
			return $this->client->embed( $text );
		} catch ( \Exception $e ) {
			return [];
		}
	}

	public function get_models(): array {
		return $this->client->get_models();
	}
}
