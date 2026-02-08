<?php

namespace Knowledge\Service\AI;

use Knowledge\Domain\Version;

class EmbeddingGenerator {
	private AIClientInterface $client;
	private ChunkingService $chunker;

	public function __construct( AIClientInterface $client, ChunkingService $chunker ) {
		$this->client  = $client;
		$this->chunker = $chunker;
	}

	public function generate_for_version( Version $version ): array {
		if ( ! file_exists( $version->get_content_path() ) ) {
			throw new \RuntimeException( "Version content not found: " . $version->get_content_path() );
		}

		$html = file_get_contents( $version->get_content_path() );
		
		// 1. Chunking
		$chunks = $this->chunker->chunk( $html );
		
		$result = [
			'version_uuid' => $version->get_uuid(),
			'generated_at' => date( 'c' ),
			'chunks'       => [],
		];

		// 2. Embedding
		foreach ( $chunks as $index => $text ) {
			$vector = $this->client->embed( $text );
			
			$result['chunks'][] = [
				'id'        => $version->get_uuid() . '_chunk_' . $index,
				'text'      => $text,
				'embedding' => $vector,
			];
		}

		return $result;
	}
}
