<?php

/** File: core/post-type.php
 * Text Domain: owbn-territory-manager
 * Version: 1.0.0
 * @author greghacke
 * Function: Register territory CPT and meta
 */

defined('ABSPATH') || exit;

add_action('init', 'owbn_tm_register_post_type');

function owbn_tm_register_post_type()
{
    $labels = [
        'name'               => __('Territories', 'owbn-territory-manager'),
        'singular_name'      => __('Territory', 'owbn-territory-manager'),
        'add_new'            => __('Add New', 'owbn-territory-manager'),
        'add_new_item'       => __('Add New Territory', 'owbn-territory-manager'),
        'edit_item'          => __('Edit Territory', 'owbn-territory-manager'),
        'new_item'           => __('New Territory', 'owbn-territory-manager'),
        'view_item'          => __('View Territory', 'owbn-territory-manager'),
        'search_items'       => __('Search Territories', 'owbn-territory-manager'),
        'not_found'          => __('No territories found', 'owbn-territory-manager'),
        'not_found_in_trash' => __('No territories found in trash', 'owbn-territory-manager'),
        'all_items'          => __('All Territories', 'owbn-territory-manager'),
        'menu_name'          => __('Territories', 'owbn-territory-manager'),
    ];

    $args = [
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => 'owbn-territory',
        'show_in_rest'        => true,
        'rest_base'           => 'territories',
        'capability_type'     => 'post',
        'has_archive'         => false,
        'hierarchical'        => false,
        'rewrite'             => ['slug' => 'territory', 'with_front' => false],
        'supports'            => ['title', 'editor', 'revisions'],
        'menu_icon'           => 'dashicons-location',
    ];

    register_post_type('owbn_territory', $args);
}

add_action('init', 'owbn_tm_register_meta');

function owbn_tm_register_meta()
{
    $meta_fields = [
        '_owbn_tm_countries' => [
            'type'         => 'array',
            'description'  => 'Array of ISO country codes',
            'single'       => true,
            'default'      => [],
            'show_in_rest' => [
                'schema' => [
                    'type'  => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
        ],
        '_owbn_tm_region' => [
            'type'         => 'string',
            'description'  => 'Region/State/Province',
            'single'       => true,
            'default'      => '',
            'show_in_rest' => true,
        ],
        '_owbn_tm_location' => [
            'type'         => 'string',
            'description'  => 'Location name',
            'single'       => true,
            'default'      => '',
            'show_in_rest' => true,
        ],
        '_owbn_tm_detail' => [
            'type'         => 'string',
            'description'  => 'Location detail',
            'single'       => true,
            'default'      => '',
            'show_in_rest' => true,
        ],
        '_owbn_tm_owner' => [
            'type'         => 'string',
            'description'  => 'Owner display name',
            'single'       => true,
            'default'      => '',
            'show_in_rest' => true,
        ],
        '_owbn_tm_slug' => [
            'type'         => 'array',
            'description'  => 'Chronicle/Coordinator slugs',
            'single'       => true,
            'default'      => [],
            'show_in_rest' => [
                'schema' => [
                    'type'  => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
        ],
        '_owbn_tm_update_date' => [
            'type'         => 'string',
            'description'  => 'Last update date from source',
            'single'       => true,
            'default'      => '',
            'show_in_rest' => true,
        ],
        '_owbn_tm_update_user' => [
            'type'         => 'string',
            'description'  => 'User who last updated in source',
            'single'       => true,
            'default'      => '',
            'show_in_rest' => true,
        ],
    ];

    foreach ($meta_fields as $key => $args) {
        register_post_meta('owbn_territory', $key, $args);
    }
}
