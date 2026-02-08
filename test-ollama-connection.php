<?php
/**
 * Test script for Ollama connectivity.
 * Run with: wp eval-file test-ollama-connection.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    require_once( dirname( __FILE__, 4 ) . '/wp-load.php' );
}

use Knowledge\Service\AI\OllamaClient;

echo "Testing Ollama Connection...\n";

try {
    $client = new OllamaClient();
    
    // 1. Test Availability
    echo "1. Checking Availability... ";
    if ( $client->is_available() ) {
        echo "✅ Available\n";
    } else {
        echo "❌ Unavailable (Is Ollama running on localhost:11434?)\n";
        exit(1);
    }

    // 2. Test Generation
    echo "2. Testing Chat Generation... ";
    $response = $client->chat( "Say 'Hello Knowledge' and nothing else." );
    echo "Response: " . trim( $response ) . "\n";
    
    if ( stripos( $response, 'Hello' ) !== false ) {
        echo "✅ Chat Test Passed\n";
    } else {
        echo "⚠️ Chat Test Verification Failed (Response unexpected)\n";
    }

    // 3. Test Embedding
    echo "3. Testing Embedding... ";
    $vector = $client->embed( "Hello world" );
    $dim = count( $vector );
    echo "Generated vector with dimension: $dim\n";
    
    if ( $dim > 0 ) {
        echo "✅ Embedding Test Passed\n";
    } else {
        echo "❌ Embedding Test Failed (Empty vector)\n";
    }

    // 4. Test Get Models
    echo "4. Testing Get Models... ";
    $models = $client->get_models();
    echo "Found " . count( $models ) . " models: " . implode( ', ', $models ) . "\n";
    
    if ( ! empty( $models ) ) {
        echo "✅ Get Models Test Passed\n";
    } else {
        echo "⚠️ No models found (This might be valid if none are pulled, but check manually)\n";
    }

} catch ( Exception $e ) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
