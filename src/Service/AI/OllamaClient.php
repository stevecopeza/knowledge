<?php

namespace Knowledge\Service\AI;

class OllamaClient implements AIClientInterface {
	private string $base_url;
	private string $model;

	public function __construct( string $base_url = '', string $model = '' ) {
		if ( empty( $base_url ) ) {
			$base_url = get_option( 'knowledge_ollama_url', 'http://192.168.5.183:11434' );
		}
		if ( empty( $model ) ) {
			$model = get_option( 'knowledge_ollama_model', 'llama3' );
		}

		$this->base_url = untrailingslashit( $base_url );
		$this->model    = $model;
	}

	public function chat( string $prompt, array $options = [] ): string {
		$url = $this->base_url . '/api/generate';

		$body = [
			'model'  => $options['model'] ?? $this->model,
			'prompt' => $prompt,
			'stream' => false,
		];

		$response = wp_remote_post( $url, [
			'body'    => wp_json_encode( $body ),
			'headers' => [ 'Content-Type' => 'application/json' ],
			'timeout' => 60,
		] );

		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( 'Ollama Request Failed: ' . $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code !== 200 ) {
			throw new \RuntimeException( 'Ollama Error (' . $code . '): ' . wp_remote_retrieve_body( $response ) );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		return $data['response'] ?? '';
	}

	public function embed( string $text ): array {
		$url = $this->base_url . '/api/embeddings';

		$body = [
			'model'  => $this->model,
			'prompt' => $text,
		];

		$response = wp_remote_post( $url, [
			'body'    => wp_json_encode( $body ),
			'headers' => [ 'Content-Type' => 'application/json' ],
			'timeout' => 30,
		] );

		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( 'Ollama Embedding Failed: ' . $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code !== 200 ) {
			throw new \RuntimeException( 'Ollama Embedding Error (' . $code . '): ' . wp_remote_retrieve_body( $response ) );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		return $data['embedding'] ?? [];
	}

	public function is_available(): bool {
		$response = wp_remote_get( $this->base_url . '/api/tags', [ 'timeout' => 2 ] );
		return ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200;
	}

	public function get_models(): array {
		$response = wp_remote_get( $this->base_url . '/api/tags', [ 'timeout' => 5 ] );

		if ( is_wp_error( $response ) ) {
			return [];
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code !== 200 ) {
			return [];
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! isset( $data['models'] ) || ! is_array( $data['models'] ) ) {
			return [];
		}

		return array_column( $data['models'], 'name' );
	}
}
