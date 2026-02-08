<?php

require_once __DIR__ . '/vendor/autoload.php';

use Knowledge\Service\AI\ChunkingService;

$service = new ChunkingService();

$html = "
<h1>Title</h1>
<p>This is paragraph one. It has some text.</p>
<p>This is paragraph two. It is also short.</p>
<div class='content'>
    <p>This is paragraph three. It is a bit longer to test the chunking logic and ensure that we are capturing enough context for the embedding model to work with.</p>
</div>
";

echo "Testing ChunkingService...\n";

$chunks = $service->chunk( $html, 50, 10 ); // Small chunk size to force splits

foreach ( $chunks as $i => $chunk ) {
    echo "--- Chunk $i ---\n";
    echo $chunk . "\n";
    echo "Length: " . strlen($chunk) . "\n";
}

if ( count($chunks) > 0 ) {
    echo "✅ Chunking Test Passed\n";
} else {
    echo "❌ Chunking Test Failed\n";
}
