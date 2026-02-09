<?php
// Backfill script to initialize _kb_note_count and _kb_current_version_uuid for existing articles

echo "Starting Backfill Process...\n";

// 1. Get all Articles
$articles = get_posts([
    'post_type' => 'kb_article',
    'posts_per_page' => -1,
    'fields' => 'ids',
]);

echo "Found " . count($articles) . " articles.\n";

foreach ($articles as $article_id) {
    echo "Processing Article ID: $article_id... ";

    // 2. Find Latest Version
    $versions = get_posts([
        'post_type'      => 'kb_version',
        'post_parent'    => $article_id,
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'fields'         => 'ids',
    ]);

    if (empty($versions)) {
        echo "No versions found. Skipping.\n";
        continue;
    }

    $latest_version_id = $versions[0];
    $uuid = get_post_meta($latest_version_id, '_kb_version_uuid', true);

    if (!$uuid) {
        echo "Version UUID missing. Skipping.\n";
        continue;
    }

    // 3. Update Current Version UUID
    update_post_meta($article_id, '_kb_current_version_uuid', $uuid);

    // 4. Count Notes
    $note_count = 0;
    $notes = get_posts([
        'post_type'   => 'kb_note',
        'post_status' => ['publish', 'private'],
        'meta_key'    => '_kb_note_source',
        'meta_value'  => $uuid,
        'fields'      => 'ids',
        'posts_per_page' => -1,
    ]);

    $note_count = count($notes);

    // 5. Update Note Count
    update_post_meta($article_id, '_kb_note_count', $note_count);

    echo "Set Version UUID: $uuid, Note Count: $note_count\n";
}

echo "Backfill Complete.\n";
