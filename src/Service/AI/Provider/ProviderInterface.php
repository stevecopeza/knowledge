<?php

namespace Knowledge\Service\AI\Provider;

interface ProviderInterface {
	public function get_id(): string;
	public function get_name(): string;
	public function get_type(): string;
	public function get_model(): string;
	
	/**
	 * Check if the provider is available/reachable.
	 */
	public function is_available(): bool;

	/**
	 * Generate a chat response.
	 */
	public function chat( string $prompt ): string;

	/**
	 * Generate embeddings.
	 * Returns empty array on failure.
	 */
	public function embed( string $text ): array;
}
