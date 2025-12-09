<?php

/** File: webhooks/client-api-list.php
 * Text Domain: owbn-territory-manager
 * Version: 1.1.0
 * @author greghacke
 * Function: Territory list API endpoint
 */

defined('ABSPATH') || exit;

/**
 * Territory List Endpoint - returns slim data
 */
function owbn_tm_api_get_territories($request)
{
    $query = new WP_Query([
        'post_type'      => 'owbn_territory',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);

    $results = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $results[] = owbn_tm_format_list_data(get_the_ID());
        }
    }

    wp_reset_postdata();
    return rest_ensure_response($results);
}

/**
 * Format territory for list endpoint
 */
function owbn_tm_format_list_data($post_id)
{
    $countries = get_post_meta($post_id, '_owbn_tm_countries', true);
    $slugs = get_post_meta($post_id, '_owbn_tm_slug', true);

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
        'update_date' => get_post_meta($post_id, '_owbn_tm_update_date', true) ?: '',
        'update_user' => get_post_meta($post_id, '_owbn_tm_update_user', true) ?: '',
    ];
}
