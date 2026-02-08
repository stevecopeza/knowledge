<?php

namespace Knowledge\Service\AI;

class OpenAIClient implements AIClientInterface {
	private string $api_key;
	private string $model;
	private string $base_url = 'https://api.openai.com/v1';

	public function __construct( string $api_key, string $model = 'gpt-4o' ) {
		$this->api_key = $api_key;
		$this->model   = $model;
	}

	public function chat( string $prompt, array $options = [] ): string {
		$url = $this->base_url . '/chat/completions';

		$body = [
			'model'    => $options['model'] ?? $this->model,
			'messages' => [
				[
					'role'    => 'user',
					'content' => $prompt,
				],
			],
		];

		$response = wp_remote_post( $url, [
			'body'    => wp_json_encode( $body ),
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->api_key,
			],
			'timeout' => 60,
		] );

		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( 'OpenAI Request Failed: ' . $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code !== 200 ) {
			throw new \RuntimeException( 'OpenAI Error (' . $code . '): ' . wp_remote_retrieve_body( $response ) );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		return $data['choices'][0]['message']['content'] ?? '';
	}

	public function embed( string $text ): array {
		$url = $this->base_url . '/embeddings';

		$body = [
			'model' => 'text-embedding-3-small', // Default for now, or configurable?
			'input' => $text,
		];

		$response = wp_remote_post( $url, [
			'body'    => wp_json_encode( $body ),
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->api_key,
			],
			'timeout' => 30,
		] );

		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( 'OpenAI Embedding Failed: ' . $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code !== 200 ) {
			throw new \RuntimeException( 'OpenAI Embedding Error (' . $code . '): ' . wp_remote_retrieve_body( $response ) );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		return $data['data'][0]['embedding'] ?? [];
	}

	public function is_available(): bool {
		// Lightweight check: list models
		$url = $this->base_url . '/models';
		
		$response = wp_remote_get( $url, [
			'headers' => [
				'Authorization' => 'Bearer ' . $this->api_key,
			],
			'timeout' => 10,
		] );

		return ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200;
	}

	public function get_models(): array {
		$url = $this->base_url . '/models';
		
		$response = wp_remote_get( $url, [
			'headers' => [
				'Authorization' => 'Bearer ' . $this->api_key,
			],
			'timeout' => 10,
		] );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return [];
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! isset( $data['data'] ) || ! is_array( $data['data'] ) ) {
			return [];
		}

		// Filter for chat models roughly (optional, but good for UX)
		$models = array_column( $data['data'], 'id' );
		sort( $models );
		return $models;
	}
}
