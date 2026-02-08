<?php

namespace Knowledge\Service\AI\Provider;

use Knowledge\Service\AI\OpenAIClient;

class OpenAIProvider implements ProviderInterface {
	private string $id;
	private string $name;
	private string $api_key;
	private string $model;
	private OpenAIClient $client;

	public function __construct( string $id, string $name, array $config ) {
		$this->id      = $id;
		$this->name    = $name;
		$this->api_key = $config['api_key'] ?? '';
		$this->model   = $config['model'] ?? 'gpt-4o';
		
		$this->client = new OpenAIClient( $this->api_key, $this->model );
	}

	public function get_id(): string {
		return $this->id;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function get_type(): string {
		return 'openai';
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
