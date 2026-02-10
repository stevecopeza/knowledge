<?php
/**
 * Script to analyze duplicate articles based on title similarity and content hashes.
 * 
 * Usage: wp eval-file analyze_duplicates.php
 */

global $wpdb;

echo "Analyzing for potential duplicates...\n\n";

// 1. Fetch all Articles
$articles = $wpdb->get_results( "
    SELECT ID, post_title 
    FROM {$wpdb->posts} 
    WHERE post_type = 'kb_article' 
    AND post_status = 'publish'
" );

echo "Total Articles: " . count( $articles ) . "\n";

// 2. Group by Normalized Title
// Strategy: Remove " - SourceName" or " | SourceName" from the end
$grouped_by_title = [];
foreach ( $articles as $article ) {
    $title = $article->post_title;
    
    // Heuristic: Strip common suffixes
    // e.g. "My Article - TechCrunch" -> "My Article"
    // e.g. "My Article | The Verge" -> "My Article"
    $normalized_title = preg_replace( '/\s+[-|]\s+.*$/', '', $title );
    $normalized_title = trim( $normalized_title );
    $normalized_title = strtolower( $normalized_title );

    if ( ! isset( $grouped_by_title[ $normalized_title ] ) ) {
        $grouped_by_title[ $normalized_title ] = [];
    }
    $grouped_by_title[ $normalized_title ][] = $article;
}

// Filter for groups with > 1 article
$potential_duplicates = array_filter( $grouped_by_title, function( $group ) {
    return count( $group ) > 1;
} );

echo "Found " . count( $potential_duplicates ) . " groups of potential duplicates based on title similarity.\n\n";

$confirmed_hash_duplicates = 0;

foreach ( $potential_duplicates as $normalized_title => $group ) {
    echo "Group: '$normalized_title' (" . count( $group ) . " articles)\n";
    
    foreach ( $group as $article ) {
        // Get the current version hash for each article
        // We need to find the child kb_version posts
        $versions = get_posts( [
            'post_type'   => 'kb_version',
            'post_parent' => $article->ID,
            'numberposts' => 1, // Just check the latest
            'orderby'     => 'date',
            'order'       => 'DESC',
        ] );

        $hash = 'N/A';
        $source = get_post_meta( $article->ID, '_kb_source_url', true );

        if ( ! empty( $versions ) ) {
            $hash = get_post_meta( $versions[0]->ID, '_kb_content_hash', true );
        }

        echo "  - ID: {$article->ID} | Title: {$article->post_title}\n";
        echo "    Source: $source\n";
        echo "    Hash: $hash\n";
    }
    echo "\n";
}

// 3. Check for Global Hash Collisions (Content Duplicates across different Articles)
echo "Checking for global content hash collisions...\n";

// Get all versions and their hashes, grouped by hash
$hash_query = "
    SELECT pm.meta_value as hash, p.post_parent as article_id
    FROM {$wpdb->postmeta} pm
    JOIN {$wpdb->posts} p ON pm.post_id = p.ID
    WHERE pm.meta_key = '_kb_content_hash'
    AND p.post_type = 'kb_version'
";

$results = $wpdb->get_results( $hash_query );
$hash_map = [];

foreach ( $results as $row ) {
    if ( empty( $row->hash ) ) continue;
    if ( ! isset( $hash_map[ $row->hash ] ) ) {
        $hash_map[ $row->hash ] = [];
    }
    // Track unique article IDs for this hash
    if ( ! in_array( $row->article_id, $hash_map[ $row->hash ] ) ) {
        $hash_map[ $row->hash ][] = $row->article_id;
    }
}

$content_duplicates = array_filter( $hash_map, function( $article_ids ) {
    return count( $article_ids ) > 1;
} );

echo "Found " . count( $content_duplicates ) . " groups of exact content duplicates across different articles.\n";

foreach ( $content_duplicates as $hash => $article_ids ) {
    echo "Hash: $hash (" . count( $article_ids ) . " articles)\n";
    foreach ( $article_ids as $id ) {
        echo "  - Article ID: $id | Title: " . get_the_title( $id ) . "\n";
    }
    echo "\n";
}
