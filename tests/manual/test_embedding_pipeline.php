<?php
/**
 * Standalone Test for Embedding Pipeline
 * run with: php tests/manual/test_pipeline_standalone.php
 */

// 1. Mock WP Functions
if (!function_exists('wp_remote_post')) {
    function wp_remote_post($url, $args) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $args['body']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, $args['timeout'] ?? 30);
        
        $result = curl_exec($ch);
        $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error  = curl_error($ch);
        curl_close($ch);

        if ($result === false) {
            return new WP_Error('curl_error', $error);
        }

        return [
            'response' => ['code' => $code],
            'body'     => $result,
        ];
    }
}

if (!function_exists('wp_remote_get')) {
    function wp_remote_get($url, $args) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $args['timeout'] ?? 30);
        
        $result = curl_exec($ch);
        $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error  = curl_error($ch);
        curl_close($ch);

        if ($result === false) {
            return new WP_Error('curl_error', $error);
        }

        return [
            'response' => ['code' => $code],
            'body'     => $result,
        ];
    }
}

if (!function_exists('wp_remote_retrieve_response_code')) {
    function wp_remote_retrieve_response_code($response) {
        return $response['response']['code'] ?? 0;
    }
}

if (!function_exists('wp_remote_retrieve_body')) {
    function wp_remote_retrieve_body($response) {
        return $response['body'] ?? '';
    }
}

if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) {
        return $thing instanceof WP_Error;
    }
}

class WP_Error {
    public $message;
    public function __construct($code, $message) { $this->message = $message; }
    public function get_error_message() { return $this->message; }
}

if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, $options = 0) { return json_encode($data, $options); }
}

if (!function_exists('untrailingslashit')) {
    function untrailingslashit($str) { return rtrim($str, '/\\'); }
}

if (!function_exists('get_option')) {
    function get_option($name, $default = false) { return $default; }
}

if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($path) {
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        return true;
    }
}

if (!defined('KNOWLEDGE_DATA_PATH')) {
    define('KNOWLEDGE_DATA_PATH', __DIR__ . '/../../wp-content/kb-data');
}

// 2. Load Classes
require_once __DIR__ . '/../../src/Service/AI/AIClientInterface.php';
require_once __DIR__ . '/../../src/Service/AI/OllamaClient.php';
require_once __DIR__ . '/../../src/Service/AI/ChunkingService.php';
require_once __DIR__ . '/../../src/Service/AI/VectorStore.php';
require_once __DIR__ . '/../../src/Service/AI/EmbeddingGenerator.php';
require_once __DIR__ . '/../../src/Domain/Version.php'; // Needed if we use Version type hint

use Knowledge\Service\AI\OllamaClient;
use Knowledge\Service\AI\ChunkingService;
use Knowledge\Service\AI\VectorStore;
use Knowledge\Service\AI\EmbeddingGenerator;
use Knowledge\Domain\Version;

// 3. Test Logic
echo "Testing Embedding Pipeline (Standalone)...\n";

// Mock Version
$uuid = '28ca1222-5752-416c-8d0b-8707d4a992c7';
$version_path = KNOWLEDGE_DATA_PATH . '/versions/' . $uuid;

if (!file_exists($version_path)) {
    // Create dummy version if not exists
    echo "Creating dummy version at $version_path\n";
    wp_mkdir_p($version_path);
    file_put_contents($version_path . '/content.html', '
        <h1>Knowledge System</h1>
        <p>The Knowledge system is designed to be local-first and authoritative.</p>
        <p>It uses WordPress as the system of record.</p>
        <p>AI is an assistant, not an owner.</p>
    ');
}

// Mock class for Version to bypass constructor/dependencies
$version_mock = new class($uuid, $version_path) extends Version {
    private $uuid;
    private $path;
    public function __construct($uuid, $path) { $this->uuid = $uuid; $this->path = $path; }
    public function get_uuid(): string { return $this->uuid; }
    public function get_content_path(): string { return $this->path . '/content.html'; }
    public function get_article_id(): int { return 0; }
    public function get_hash(): string { return ''; }
    public function get_title(): string { return 'Test'; }
    public function get_source_url(): string { return ''; }
};

try {
    // Use the IP from the user's screenshot as a fallback or primary
    $ollama_url = 'http://192.168.5.183:11434';
    echo "Connecting to Ollama at $ollama_url ...\n";
    
    $client  = new OllamaClient($ollama_url);
    
    // Pick first available model
    $models = $client->get_models();
    echo "Available models: " . implode(", ", $models) . "\n";
    
    if (!empty($models)) {
        // Prefer known models that support embeddings well
        $preferred = ['llama3.2:latest', 'mistral:latest', 'nomic-embed-text:latest', 'mxbai-embed-large:latest'];
        $model = $models[0];
        
        foreach ($preferred as $p) {
            if (in_array($p, $models)) {
                $model = $p;
                break;
            }
        }
        
        // Fallback: look for 'embed' in name
        if (!in_array($model, $preferred)) {
            foreach ($models as $m) {
                if (strpos($m, 'embed') !== false) {
                    $model = $m;
                    break;
                }
            }
        }
        
        echo "Using model: $model\n";
        $client = new OllamaClient($ollama_url, $model);
    } else {
        echo "⚠️ No models found! Using default.\n";
    }

    $chunker = new ChunkingService();
    $store   = new VectorStore();
    $gen     = new EmbeddingGenerator( $client, $chunker );

    echo "1. Checking Ollama... ";
    if (!$client->is_available()) {
        echo "❌ Not available (Check Docker/Ollama)\n";
        exit(1);
    }
    echo "✅ Available\n";

    echo "2. Generating... ";
    $data = $gen->generate_for_version($version_mock);
    echo "Generated " . count($data['chunks']) . " chunks.\n";

    echo "3. Saving... ";
    $store->save($data);
    echo "✅ Saved to " . KNOWLEDGE_DATA_PATH . "/ai/embeddings/$uuid.json\n";

    echo "4. Searching... ";
    $query = "system of record";
    $vec = $client->embed($query);
    $results = $store->search($vec, 1);
    
    if (count($results) > 0) {
        echo "Found: " . $results[0]['text'] . "\n";
        echo "Score: " . $results[0]['score'] . "\n";
    } else {
        echo "❌ No results found\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
