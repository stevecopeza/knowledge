<?php

namespace Knowledge\Service\AI;

class ChunkingService {

	/**
	 * Chunk text into smaller segments for embedding.
	 *
	 * @param string $content    The raw content (can be HTML).
	 * @param int    $chunk_size Target size in characters (approx).
	 * @param int    $overlap    Overlap size in characters.
	 * @return array List of text chunks.
	 */
	public function chunk( string $content, int $chunk_size = 1000, int $overlap = 100 ): array {
		// 1. Convert to Plain Text
		$text = $this->html_to_text( $content );

		if ( empty( $text ) ) {
			return [];
		}

		// 2. Split by logical boundaries (paragraphs)
		// We use a simple regex for double newlines or standard paragraph breaks
		$paragraphs = preg_split( '/\n\s*\n/', $text );
		
		$chunks = [];
		$current_chunk = '';

		foreach ( $paragraphs as $paragraph ) {
			$paragraph = trim( $paragraph );
			if ( empty( $paragraph ) ) {
				continue;
			}

			// If adding this paragraph exceeds chunk size, save current and start new
			if ( strlen( $current_chunk ) + strlen( $paragraph ) > $chunk_size ) {
				if ( ! empty( $current_chunk ) ) {
					$chunks[] = trim( $current_chunk );
					
					// Handle Overlap: Keep the last N chars of the previous chunk
					// to maintain context continuity.
					$current_chunk = substr( $current_chunk, -1 * $overlap );
				}
			}

			$current_chunk .= "\n\n" . $paragraph;
		}

		if ( ! empty( $current_chunk ) ) {
			$chunks[] = trim( $current_chunk );
		}

		// 3. Post-process (handle giant paragraphs that exceed chunk size alone)
		return $this->refine_chunks( $chunks, $chunk_size );
	}

	private function html_to_text( string $html ): string {
		// Simple strip tags, but could be improved to preserve semantic breaks
		// Replace block tags with newlines
		$html = preg_replace( '/<(p|div|h[1-6]|li|br)/i', "\n$0", $html );
		return strip_tags( html_entity_decode( $html ) );
	}

	private function refine_chunks( array $chunks, int $chunk_size ): array {
		$final_chunks = [];
		foreach ( $chunks as $chunk ) {
			if ( strlen( $chunk ) > $chunk_size * 1.5 ) {
				// Force split if way too big
				$parts = str_split( $chunk, $chunk_size );
				foreach ( $parts as $part ) {
					$final_chunks[] = $part;
				}
			} else {
				$final_chunks[] = $chunk;
			}
		}
		return $final_chunks;
	}
}
