<?php
// Load WordPress environment
require_once __DIR__ . '/../../../wp-load.php';

use Knowledge\Service\Project\ProjectService;

// Setup
$service = new ProjectService();

// Create Dummy Posts
$project_id = wp_insert_post([
    'post_type' => 'kb_project',
    'post_title' => 'Test Project ' . time(),
    'post_status' => 'publish'
]);

$article_id = wp_insert_post([
    'post_type' => 'kb_article',
    'post_title' => 'Test Article ' . time(),
    'post_status' => 'publish'
]);

echo "Created Project: $project_id\n";
echo "Created Article: $article_id\n";

// Test 1: Add Member
$added = $service->add_member($project_id, $article_id);
echo "Add Member: " . ($added ? "PASS" : "FAIL") . "\n";

// Test 2: Get Members
$members = $service->get_members($project_id);
echo "Get Members: " . (in_array($article_id, $members) ? "PASS" : "FAIL") . "\n";

// Test 3: Get Projects for Object
$projects = $service->get_projects_for_object($article_id);
echo "Get Projects for Object: " . (in_array($project_id, $projects) ? "PASS" : "FAIL") . "\n";

// Test 4: Remove Member
$removed = $service->remove_member($project_id, $article_id);
echo "Remove Member: " . ($removed ? "PASS" : "FAIL") . "\n";

// Test 5: Verify Removal
$members_after = $service->get_members($project_id);
echo "Verify Removal: " . (!in_array($article_id, $members_after) ? "PASS" : "FAIL") . "\n";

// Cleanup
wp_delete_post($project_id, true);
wp_delete_post($article_id, true);
echo "Cleanup Done.\n";
