<?php

namespace Knowledge\Service\AI;

class VectorStore {
	private string $storage_dir;

	public function __construct() {
		$this->storage_dir = KNOWLEDGE_DATA_PATH . '/ai/embeddings';
		if ( ! file_exists( $this->storage_dir ) ) {
			wp_mkdir_p( $this->storage_dir );
		}
	}

	/**
	 * Save embedding result for a version.
	 *
	 * @param array $data Output from EmbeddingGenerator.
	 */
	public function save( array $data ): void {
		$uuid = $data['version_uuid'];
		$file = $this->storage_dir . '/' . $uuid . '.json';
		
		file_put_contents( $file, wp_json_encode( $data, JSON_PRETTY_PRINT ) );
	}

	/**
	 * Search for similar chunks.
	 *
	 * @param array $query_vector The embedding of the query.
	 * @param int   $limit        Number of results to return.
	 * @param array $allowed_uuids Optional list of Version UUIDs to restrict search to.
	 * @return array List of matches ['text' => ..., 'score' => ..., 'source_uuid' => ...]
	 */
	public function search( array $query_vector, int $limit = 5, array $allowed_uuids = [] ): array {
		$files = glob( $this->storage_dir . '/*.json' );
		$scores = [];

		foreach ( $files as $file ) {
			// Optimization: Check filename (UUID) before reading content if filtering
			if ( ! empty( $allowed_uuids ) ) {
				$filename = basename( $file, '.json' );
				if ( ! in_array( $filename, $allowed_uuids, true ) ) {
					continue;
				}
			}

			$data = json_decode( file_get_contents( $file ), true );
			if ( ! isset( $data['chunks'] ) ) {
				continue;
			}

			foreach ( $data['chunks'] as $chunk ) {
				if ( empty( $chunk['embedding'] ) ) {
					continue;
				}

				$similarity = $this->cosine_similarity( $query_vector, $chunk['embedding'] );
				
				$scores[] = [
					'score'        => $similarity,
					'text'         => $chunk['text'],
					'version_uuid' => $data['version_uuid'],
					'chunk_id'     => $chunk['id'],
				];
			}
		}

		// Sort by score DESC
		usort( $scores, function( $a, $b ) {
			return $b['score'] <=> $a['score'];
		} );

		return array_slice( $scores, 0, $limit );
	}

	private function cosine_similarity( array $vec_a, array $vec_b ): float {
		$dot_product = 0;
		$norm_a      = 0;
		$norm_b      = 0;

		foreach ( $vec_a as $i => $val ) {
			$dot_product += $val * $vec_b[ $i ];
			$norm_a      += $val * $val;
			$norm_b      += $vec_b[ $i ] * $vec_b[ $i ];
		}

		if ( $norm_a == 0 || $norm_b == 0 ) {
			return 0;
		}

		return $dot_product / ( sqrt( $norm_a ) * sqrt( $norm_b ) );
	}
}
