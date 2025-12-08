<?php

/** File: webhooks/client-api-detail.php
 * Text Domain: owbn-territory-manager
 * Version: 1.0.0
 * @author greghacke
 * Function: Load webhooks files
 */

defined('ABSPATH') || exit;

/**
 * Single Territory Endpoint - by ID
 */
function owbn_tm_api_get_territory($request)
{
    $body = $request->get_json_params();
    $id = isset($body['id']) ? absint($body['id']) : 0;

    if (!$id) {
        return new WP_Error('missing_id', 'Territory ID is required', ['status' => 400]);
    }

    $post = get_post($id);

    if (!$post || $post->post_type !== 'owbn_territory' || $post->post_status !== 'publish') {
        return new WP_Error('not_found', 'Territory not found', ['status' => 404]);
    }

    return rest_ensure_response(owbn_tm_format_detail_data($id));
}

/**
 * Territories by Slug Endpoint
 */
function owbn_tm_api_get_territories_by_slug($request)
{
    $body = $request->get_json_params();
    $slug = isset($body['slug']) ? sanitize_text_field($body['slug']) : '';

    if (!$slug) {
        return new WP_Error('missing_slug', 'Slug is required', ['status' => 400]);
    }

    // Query territories where slug array contains the requested slug
    $query = new WP_Query([
        'post_type'      => 'owbn_territory',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => [
            [
                'key'     => '_owbn_tm_slug',
                'value'   => $slug,
                'compare' => 'LIKE',
            ],
        ],
    ]);

    $results = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $slugs = get_post_meta($post_id, '_owbn_tm_slug', true) ?: [];

            // Verify exact match (LIKE can match partial)
            if (is_array($slugs) && in_array($slug, $slugs, true)) {
                $results[] = owbn_tm_format_detail_data($post_id);
            }
        }
    }

    wp_reset_postdata();
    return rest_ensure_response($results);
}

/**
 * Format territory for detail endpoint (full)
 */
function owbn_tm_format_detail_data($post_id)
{
    $countries = get_post_meta($post_id, '_owbn_tm_countries', true);
    $slugs = get_post_meta($post_id, '_owbn_tm_slug', true);

    // Ensure arrays, filter empty values, re-index for proper JSON array encoding
    $countries = is_array($countries) ? array_values(array_filter($countries)) : [];
    $slugs = is_array($slugs) ? array_values(array_filter($slugs)) : [];

    return [
        'id'          => $post_id,
        'title'       => get_the_title($post_id),
        'countries'   => $countries,
        'region'      => get_post_meta($post_id, '_owbn_tm_region', true) ?: '',
        'location'    => get_post_meta($post_id, '_owbn_tm_location', true) ?: '',
        'detail'      => get_post_meta($post_id, '_owbn_tm_detail', true) ?: '',
        'owner'       => get_post_meta($post_id, '_owbn_tm_owner', true) ?: '',
        'slugs'       => $slugs,
        'description' => get_post_field('post_content', $post_id),
    ];
}
