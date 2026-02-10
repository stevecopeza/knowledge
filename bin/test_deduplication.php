<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap WordPress
$wp_load_path = __DIR__ . '/../../../../wp-load.php';
if ( ! file_exists( $wp_load_path ) ) {
    die( "Error: Could not find wp-load.php at $wp_load_path\n" );
}
require_once $wp_load_path;

use Knowledge\Service\Storage\StorageEngine;
use Knowledge\Domain\ValueObject\Source;
use Knowledge\Service\Ingestion\IngestionService;

function run_tests() {
    echo "Starting Deduplication Tests...\n";

    test_global_hash_deduplication();
    test_concurrency_locking();
    // test_canonical_resolution(); // Harder to test without mocking HTTP, leaving for now.

    echo "\nAll Tests Completed.\n";
}

function test_global_hash_deduplication() {
    echo "\n[Test] Global Hash Deduplication (Layer 2)\n";
    
    $storage = new StorageEngine();
    $unique_content = "Test Content " . uniqid() . " " . microtime(true);
    $url_a = "http://example.com/article-a-" . uniqid();
    $url_b = "http://example.com/article-b-" . uniqid(); // Different URL
    
    echo "1. Creating Article A with unique content...\n";
    $version_a = $storage->store(
        new Source($url_a),
        "Article A Title",
        $unique_content
    );
    
    $article_id_a = $version_a->get_article_id();
    echo "   -> Created Article A (ID: $article_id_a)\n";

    echo "2. Attempting to create Article B with SAME content but DIFFERENT URL...\n";
    $version_b = $storage->store(
        new Source($url_b),
        "Article B Title",
        $unique_content
    );
    
    $article_id_b = $version_b->get_article_id();
    
    if ( $article_id_a === $article_id_b ) {
        echo "   [PASS] Success! Article B was NOT created. Reused Article A (ID: $article_id_a).\n";
    } else {
        echo "   [FAIL] Error! A new Article B (ID: $article_id_b) was created despite duplicate content.\n";
    }
}

function test_concurrency_locking() {
    echo "\n[Test] Concurrency Locking (Layer 3)\n";
    
    $ingestion = new IngestionService();
    $url = "http://example.com/locked-article-" . uniqid();
    
    // Manually set the lock
    $lock_key = 'kb_ingest_lock_' . md5( $url );
    set_transient( $lock_key, true, 60 );
    
    echo "1. Set lock for URL: $url\n";
    
    try {
        echo "2. Attempting ingestion (should fail)...\n";
        $ingestion->ingest_url( $url );
        echo "   [FAIL] Error! Ingestion proceeded despite lock.\n";
    } catch ( \RuntimeException $e ) {
        if ( strpos( $e->getMessage(), 'Ingestion already in progress' ) !== false ) {
            echo "   [PASS] Success! Caught expected exception: " . $e->getMessage() . "\n";
        } else {
            echo "   [FAIL] Caught unexpected exception: " . $e->getMessage() . "\n";
        }
    } finally {
        delete_transient( $lock_key );
    }
}

if ( PHP_SAPI === 'cli' ) {
    run_tests();
}
