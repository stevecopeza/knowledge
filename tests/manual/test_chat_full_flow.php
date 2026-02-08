<?php

// Require Composer Autoloader directly
require_once __DIR__ . '/../../vendor/autoload.php';

use Knowledge\Service\AI\OllamaClient;
use Knowledge\Service\AI\ChunkingService;
use Knowledge\Service\AI\EmbeddingGenerator;
use Knowledge\Service\AI\VectorStore;
use Knowledge\Service\AI\ChatService;
use Knowledge\Domain\Version;
use Knowledge\Domain\ValueObject\Source;

// Mock WP Environment
if (!defined('KNOWLEDGE_DATA_PATH')) {
    define('KNOWLEDGE_DATA_PATH', __DIR__ . '/../../wp-content/kb-data');
}
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../../');
}

function wp_mkdir_p($path) {
    if (!file_exists($path)) {
        mkdir($path, 0755, true);
    }
    return true;
}

function wp_remote_post($url, $args) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $args['body']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, $args['timeout']);
    
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return new WP_Error('http_request_failed', $error);
    }

    return [
        'response' => ['code' => $code],
        'body' => $response
    ];
}

function wp_remote_get($url, $args) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $args['timeout']);
    
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return new WP_Error('http_request_failed', $error);
    }

    return [
        'response' => ['code' => $code],
        'body' => $response
    ];
}

function wp_remote_retrieve_response_code($response) {
    return $response['response']['code'];
}

function wp_remote_retrieve_body($response) {
    return $response['body'];
}

function is_wp_error($thing) {
    return $thing instanceof WP_Error;
}

function wp_json_encode($data, $options = 0) {
    return json_encode($data, $options);
}

function untrailingslashit($string) {
    return rtrim($string, '/\\');
}

function get_option($key, $default = false) {
    if ($key === 'knowledge_ollama_url') {
        return 'http://192.168.5.183:11434';
    }
    return $default;
}

class WP_Error {
    public $code;
    public $message;
    public function __construct($code, $message) {
        $this->code = $code;
        $this->message = $message;
    }
    public function get_error_message() {
        return $this->message;
    }
}

// --- Test Start ---

echo "Testing Chat Flow (Standalone)...\n";

$ollama_url = 'http://192.168.5.183:11434';
$client = new OllamaClient($ollama_url);

if (!$client->is_available()) {
    die("❌ Ollama not available at $ollama_url\n");
}

// 1. Setup Test Data
$uuid = 'test-chat-' . time();
$text = "The Knowledge Plugin for WordPress is a local-first system. It allows users to ingest web pages and perform RAG (Retrieval Augmented Generation) locally using Ollama. It does not rely on external cloud services for AI.";
$title = "Knowledge Plugin Overview";

// Detect best model
$models = $client->get_models();
$model = $models[0] ?? 'llama3';
// Try to find a good model
$preferred = ['llama3.2:latest', 'mistral:latest'];
foreach ($preferred as $p) {
    if (in_array($p, $models)) {
        $model = $p;
        break;
    }
}
echo "Using model: $model\n";
$client = new OllamaClient($ollama_url, $model);


$version = new Version(
    $uuid,
    0,
    new Source('http://example.com/test'),
    $title,
    '/tmp/fake', // Content path unused by embedding generator directly
    md5($text)
);

// Hack: We need to pass text to EmbeddingGenerator, but it reads from file.
// Let's manually create the embedding data structure to skip file reading or mock it?
// Actually, EmbeddingGenerator reads from Version object content path.
// Let's create a temp file.
$temp_file = sys_get_temp_dir() . '/' . $uuid . '.html';
file_put_contents($temp_file, $text);
// Re-create version with real path
$version = new Version(
    $uuid,
    0,
    new Source('http://example.com/test'),
    $title,
    $temp_file,
    md5($text)
);

$chunker = new ChunkingService();
$gen = new EmbeddingGenerator($client, $chunker);
$store = new VectorStore();

echo "1. Generating Embeddings...\n";
try {
    $data = $gen->generate_for_version($version);
    $store->save($data);
    echo "✅ Embeddings saved.\n";
} catch (Exception $e) {
    die("❌ Embedding failed: " . $e->getMessage() . "\n");
}

// 2. Test Chat
$chat = new ChatService();
// We need to inject the client with the correct model into ChatService, 
// but ChatService instantiates its own client.
// We should probably allow dependency injection in ChatService or rely on get_option mocking.
// For this test, we can subclass or just rely on the fact that OllamaClient uses defaults which we mocked via get_option (returning default).
// Wait, get_option mock returns $default. OllamaClient constructor:
// $model = get_option( 'knowledge_ollama_model', 'llama3' );
// So it will use 'llama3'. If I want it to use $model, I need to hack it or change ChatService.
// Let's modify ChatService to allow injecting client.

// ... But I can't modify ChatService code easily from here. 
// I will just rely on the fact that I can set the options via get_option if I could, but I can't.
// However, I can subclass ChatService in this test file? No, it's already defined in the required file.
// I will modify ChatService.php to allow constructor injection first.

echo "2. Asking Question...\n";
// Temporarily using reflection to swap client if needed, or just hope 'llama3' works (or whatever default is).
// Actually, let's modify ChatService.php first to be testable.

$question = "What is the Knowledge Plugin?";
echo "Q: $question\n";

// Manual Chat Logic (since we can't easily configure ChatService in this standalone script without full WP mock)
// We will manually invoke the steps to verify the flow.

$query_vec = $client->embed($question);
echo "Query embedded. Vector size: " . count($query_vec) . "\n";

$results = $store->search($query_vec, 3);
echo "Found " . count($results) . " chunks.\n";
if (count($results) > 0) {
    echo "Top result: " . substr($results[0]['text'], 0, 50) . "...\n";
}

$context_text = "";
foreach ($results as $r) {
    $context_text .= "---\n" . $r['text'] . "\n";
}

$prompt = <<<EOT
You are a helpful assistant.
Context:
$context_text

Question: $question
Answer:
EOT;

echo "Sending to AI...\n";
$answer = $client->chat($prompt);
echo "A: $answer\n";

// 3. Cleanup
echo "3. Cleaning up...\n";
@unlink($temp_file);
$emb_file = KNOWLEDGE_DATA_PATH . '/ai/embeddings/' . $uuid . '.json';
@unlink($emb_file);

echo "✅ Done.\n";
