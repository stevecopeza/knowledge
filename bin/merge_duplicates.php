<?php
/**
 * Merge Duplicate Articles Script
 * 
 * Usage: wp eval-file bin/merge_duplicates.php -- <primary_id> <duplicate_id> [--dry-run]
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Please run via wp-cli' );
}

$args = $args ?? [];
$dry_run = in_array( '--dry-run', $args ) || in_array( 'dry-run', $args );

// Filter out flags to get IDs
$ids = array_filter( $args, function( $arg ) {
    return is_numeric( $arg );
} );
$ids = array_values( $ids );

if ( count( $ids ) < 2 ) {
    WP_CLI::error( "Usage: wp eval-file bin/merge_duplicates.php -- <primary_id> <duplicate_id> [--dry-run]" );
}

$primary_id = (int) $ids[0];
$duplicate_id = (int) $ids[1];

WP_CLI::line( "Merging Duplicate ID $duplicate_id into Primary ID $primary_id..." );

if ( $dry_run ) {
    WP_CLI::line( "Running in DRY RUN mode." );
}

// 1. Verify Articles exist
$primary = get_post( $primary_id );
$duplicate = get_post( $duplicate_id );

if ( ! $primary || $primary->post_type !== 'kb_article' ) {
    WP_CLI::error( "Primary ID $primary_id is not a valid kb_article." );
}
if ( ! $duplicate || $duplicate->post_type !== 'kb_article' ) {
    WP_CLI::error( "Duplicate ID $duplicate_id is not a valid kb_article." );
}

global $wpdb;

// 2. Move Versions
$versions = get_posts( [
    'post_type' => 'kb_version',
    'post_parent' => $duplicate_id,
    'posts_per_page' => -1,
    'fields' => 'ids',
] );

WP_CLI::line( "Found " . count( $versions ) . " versions attached to Duplicate." );

foreach ( $versions as $version_id ) {
    WP_CLI::line( " - Version $version_id: Moving to parent $primary_id" );
    if ( ! $dry_run ) {
        wp_update_post( [
            'ID' => $version_id,
            'post_parent' => $primary_id,
        ] );
    }
}

// 3. Move Project Relationships
$table = $wpdb->prefix . 'kb_project_relationships';
$duplicate_projects = $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM $table WHERE object_id = %d",
    $duplicate_id
) );

WP_CLI::line( "Found " . count( $duplicate_projects ) . " project memberships for Duplicate." );

foreach ( $duplicate_projects as $rel ) {
    $project_id = $rel->project_id;
    
    // Check if primary is already a member
    $exists = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM $table WHERE project_id = %d AND object_id = %d",
        $project_id,
        $primary_id
    ) );

    if ( $exists ) {
        WP_CLI::line( " - Project $project_id: Primary is already a member. Removing duplicate relationship." );
        if ( ! $dry_run ) {
            $wpdb->delete( $table, [ 'id' => $rel->id ] );
        }
    } else {
        WP_CLI::line( " - Project $project_id: Transferring membership to Primary." );
        if ( ! $dry_run ) {
            $wpdb->update(
                $table,
                [ 'object_id' => $primary_id ],
                [ 'id' => $rel->id ]
            );
        }
    }
}

// 4. Delete Duplicate Article
WP_CLI::line( "Deleting Duplicate Article $duplicate_id..." );
if ( ! $dry_run ) {
    $result = wp_delete_post( $duplicate_id, true ); // Force delete
    if ( $result ) {
        WP_CLI::success( "Successfully deleted duplicate article." );
    } else {
        WP_CLI::error( "Failed to delete duplicate article." );
    }
} else {
    WP_CLI::line( "DRY RUN: Would delete article $duplicate_id" );
}

WP_CLI::success( "Merge complete!" );
