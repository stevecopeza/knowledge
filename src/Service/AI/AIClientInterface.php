<?php

namespace Knowledge\Service\AI;

interface AIClientInterface {
	/**
	 * Send a prompt to the AI model and get a response.
	 *
	 * @param string $prompt The user's input or system prompt.
	 * @param array  $options Optional parameters (model, temperature, etc.).
	 * @return string The generated text.
	 */
	public function chat( string $prompt, array $options = [] ): string;

	/**
	 * Generate embeddings for a given text.
	 *
	 * @param string $text The text to embed.
	 * @return array The vector embedding (list of floats).
	 */
	public function embed( string $text ): array;

	/**
	 * Check if the service is available.
	 *
	 * @return bool
	 */
	public function is_available(): bool;
}
