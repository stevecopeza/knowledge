<?php

namespace Knowledge\Service\AI;

use Knowledge\Service\AI\ProviderManager;

class AIAnalysisService {
	private ProviderManager $provider_manager;

	public function __construct() {
		$this->provider_manager = new ProviderManager();
	}

	public static function handle_analysis_job( string $version_uuid, int $article_id ): void {
		error_log( "AIAnalysisService: Job started for Version $version_uuid, Article $article_id" );
		$service = new self();
		$service->process_analysis( $version_uuid, $article_id );
	}

	public function process_analysis( string $version_uuid, int $article_id ): void {
		// 1. Retrieve Version Content
		$args = [
			'post_type'      => 'kb_version',
			'meta_key'       => '_kb_version_uuid',
			'meta_value'     => $version_uuid,
			'posts_per_page' => 1,
		];
		$query = new \WP_Query( $args );
		
		if ( ! $query->have_posts() ) {
			error_log( "AIAnalysisService: Version not found for UUID $version_uuid" );
			return;
		}
		
		$version_post = $query->posts[0];
		$file_path_suffix = get_post_meta( $version_post->ID, '_kb_file_path', true );
		$full_path = KNOWLEDGE_DATA_PATH . '/versions/' . $file_path_suffix;
		
		if ( ! file_exists( $full_path ) ) {
			error_log( "AIAnalysisService: Content file not found at $full_path" );
			return;
		}

		$content = file_get_contents( $full_path );
		$title   = get_the_title( $article_id );

		$this->analyze_content( $article_id, $title, $content );
	}

	private function analyze_content( int $article_id, string $title, string $content ): void {
		// Get existing categories to help the AI match
		$existing_categories = get_terms( [
			'taxonomy'   => 'kb_category',
			'hide_empty' => false,
			'fields'     => 'names',
		] );
		
		$cat_list = 'None';
		if ( ! empty( $existing_categories ) && ! is_wp_error( $existing_categories ) ) {
			$cat_list = implode( ', ', $existing_categories );
		}

		// Strip tags and truncate content to avoid hitting context limits (approx 8000 chars)
		$clean_content = substr( strip_tags( $content ), 0, 8000 );

		$prompt = "You are a knowledge assistant. Analyze the following article content and provide:
1. A single relevant Category (choose from: [{$cat_list}] if applicable, or create a new one).
2. A list of 3-5 relevant Tags.
3. A concise summary (max 100 words).

Return ONLY raw JSON in this format:
{
  \"category\": \"Category Name\",
  \"tags\": [\"tag1\", \"tag2\"],
  \"summary\": \"The summary text...\"
}

Title: {$title}
Content: {$clean_content}";

		try {
			$result   = $this->provider_manager->chat_with_failover( $prompt );
			$answer   = $result['answer'];
			$provider = $result['provider'];

			// Parse JSON
			$json_start = strpos( $answer, '{' );
			$json_end   = strrpos( $answer, '}' );
			
			$json_str = '';

			if ( $json_start !== false ) {
				if ( $json_end !== false ) {
					$json_str = substr( $answer, $json_start, $json_end - $json_start + 1 );
				} else {
					// Attempt to fix truncated JSON (missing closing brace)
					$json_str = substr( $answer, $json_start );
					$json_str = trim( $json_str );
					if ( substr( $json_str, -1 ) !== '}' ) {
						$json_str .= '}';
					}
				}
				
				$data = json_decode( $json_str, true );
				
				if ( $data ) {
					$this->save_results( $article_id, $data, $provider->get_id(), $provider->get_model() ?? 'unknown' );
				} else {
					error_log( "AIAnalysisService: Failed to decode JSON: " . json_last_error_msg() );
					error_log( "AIAnalysisService: Raw JSON string: " . $json_str );
				}
			} else {
				error_log( "AIAnalysisService: No JSON start found in response." );
				error_log( "AIAnalysisService: Full Answer: " . $answer );
			}
		} catch ( \Exception $e ) {
			error_log( "AI Analysis Failed: " . $e->getMessage() );
			// Mark as 'Needs Review'
			wp_set_object_terms( $article_id, 'needs-review', 'kb_tag', true );
		}
	}

	private function save_results( int $article_id, array $data, string $provider_id, string $model ): void {
		error_log( "AIAnalysisService: Saving results for Article $article_id" );
		// 1. Category
		if ( ! empty( $data['category'] ) ) {
			$cat_name = sanitize_text_field( $data['category'] );
			$term     = term_exists( $cat_name, 'kb_category' );
			
			if ( ! $term ) {
				$term = wp_insert_term( $cat_name, 'kb_category' );
			}
			
			if ( ! is_wp_error( $term ) ) {
				$term_id = is_array( $term ) ? $term['term_id'] : $term;
				$res = wp_set_post_terms( $article_id, [ $term_id ], 'kb_category' );
				error_log( "AIAnalysisService: Set category '$cat_name' (ID $term_id) for Article $article_id. Result: " . print_r($res, true) );
			} else {
				error_log( "AIAnalysisService: Error with category term '$cat_name': " . $term->get_error_message() );
			}
		} else {
			error_log( "AIAnalysisService: No category provided in JSON for Article $article_id" );
		}

		// 2. Tags
		if ( ! empty( $data['tags'] ) && is_array( $data['tags'] ) ) {
			wp_set_post_terms( $article_id, $data['tags'], 'kb_tag', true );
		}

		// 3. Summary (CPT kb_summary)
		if ( ! empty( $data['summary'] ) ) {
			$summary_text = sanitize_textarea_field( $data['summary'] );
			
			$summary_id = wp_insert_post( [
				'post_type'    => 'kb_summary',
				'post_status'  => 'publish',
				'post_content' => $summary_text,
				'post_parent'  => $article_id,
				'meta_input'   => [
					'_kb_ai_provenance' => [
						'provider_id' => $provider_id,
						'model'       => $model,
						'timestamp'   => time(),
					],
				],
			] );
			
			// Also save as post meta on the article for easy retrieval in shortcodes
			update_post_meta( $article_id, '_kb_ai_summary', $summary_text );
		}
		
		// 4. Save Provenance on Article
		update_post_meta( $article_id, '_kb_ai_provenance', [
			'provider_id' => $provider_id,
			'model'       => $model,
			'timestamp'   => time(),
		] );
	}
}
