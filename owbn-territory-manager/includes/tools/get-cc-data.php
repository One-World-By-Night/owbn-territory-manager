<?php

/** File: tools/get-cc-data.php
 * Text Domain: owbn-territory-manager
 * Version: 0.9.0
 * @author greghacke
 * Function: Fetch chronicle/coordinator data from local or remote API
 */

defined('ABSPATH') || exit;

/**
 * Check if chronicles enabled.
 */
function owbn_tm_chronicles_enabled(): bool
{
    return (bool) get_option('owbn_tm_enable_chronicles', false);
}

/**
 * Check if coordinators enabled.
 */
function owbn_tm_coordinators_enabled(): bool
{
    return (bool) get_option('owbn_tm_enable_coordinators', false);
}

/**
 * Get chronicles mode.
 */
function owbn_tm_chronicles_mode(): string
{
    return get_option('owbn_tm_chronicles_mode', 'local');
}

/**
 * Get coordinators mode.
 */
function owbn_tm_coordinators_mode(): string
{
    return get_option('owbn_tm_coordinators_mode', 'local');
}

/**
 * Get cache TTL.
 */
function owbn_tm_get_cache_ttl(): int
{
    return (int) get_option('owbn_tm_cache_ttl', 3600);
}

/**
 * Make remote API request.
 *
 * @param string $url     Full API URL
 * @param string $api_key API key
 * @return array|WP_Error
 */
function owbn_tm_remote_request(string $url, string $api_key)
{
    if (empty($url)) {
        return new WP_Error('no_url', __('API URL not configured.', 'owbn-territory-manager'));
    }

    $response = wp_remote_post($url, [
        'timeout' => 15,
        'headers' => [
            'Content-Type' => 'application/json',
            'x-api-key'    => $api_key,
        ],
        'body' => wp_json_encode([]),
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if ($code !== 200) {
        return new WP_Error(
            'api_error',
            $data['message'] ?? sprintf(__('API returned status %d', 'owbn-territory-manager'), $code),
            ['status' => $code]
        );
    }

    return $data;
}

/**
 * Get local chronicles data.
 *
 * @return array|WP_Error
 */
function owbn_tm_get_local_chronicles()
{
    // Check if owbn-chronicle-manager function exists
    if (function_exists('owbn_get_chronicles_data')) {
        return owbn_get_chronicles_data();
    }

    // Fallback: query CPT directly
    if (!post_type_exists('owbn_chronicle')) {
        return new WP_Error('no_cpt', __('Chronicle post type not available.', 'owbn-territory-manager'));
    }

    $posts = get_posts([
        'post_type'      => 'owbn_chronicle',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    ]);

    $data = [];
    foreach ($posts as $post) {
        $data[] = [
            'id'    => $post->ID,
            'slug'  => $post->post_name,
            'title' => $post->post_title,
            'name'  => $post->post_title,
        ];
    }

    return $data;
}

/**
 * Get local coordinators data.
 *
 * @return array|WP_Error
 */
function owbn_tm_get_local_coordinators()
{
    // Check if owbn-chronicle-manager function exists
    if (function_exists('owbn_get_coordinators_data')) {
        return owbn_get_coordinators_data();
    }

    // Fallback: query CPT directly
    if (!post_type_exists('owbn_coordinator')) {
        return new WP_Error('no_cpt', __('Coordinator post type not available.', 'owbn-territory-manager'));
    }

    $posts = get_posts([
        'post_type'      => 'owbn_coordinator',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    ]);

    $data = [];
    foreach ($posts as $post) {
        $data[] = [
            'id'    => $post->ID,
            'slug'  => $post->post_name,
            'title' => $post->post_title,
            'name'  => $post->post_title,
        ];
    }

    return $data;
}

/**
 * Get chronicles (cached).
 *
 * @param bool $force_refresh Bypass cache
 * @return array|WP_Error
 */
function owbn_tm_get_chronicles(bool $force_refresh = false)
{
    if (!owbn_tm_chronicles_enabled()) {
        return [];
    }

    $cache_key = 'owbn_tm_chronicles_cache';

    if (!$force_refresh) {
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }
    }

    $mode = owbn_tm_chronicles_mode();

    if ($mode === 'local') {
        $data = owbn_tm_get_local_chronicles();
    } else {
        $url = trailingslashit(get_option('owbn_tm_chronicles_url', '')) . 'chronicles';
        $key = get_option('owbn_tm_chronicles_api_key', '');
        $data = owbn_tm_remote_request($url, $key);
    }

    if (!is_wp_error($data)) {
        $ttl = owbn_tm_get_cache_ttl();
        if ($ttl > 0) {
            set_transient($cache_key, $data, $ttl);
        }
    }

    return $data;
}

/**
 * Get coordinators (cached).
 *
 * @param bool $force_refresh Bypass cache
 * @return array|WP_Error
 */
function owbn_tm_get_coordinators(bool $force_refresh = false)
{
    if (!owbn_tm_coordinators_enabled()) {
        return [];
    }

    $cache_key = 'owbn_tm_coordinators_cache';

    if (!$force_refresh) {
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }
    }

    $mode = owbn_tm_coordinators_mode();

    if ($mode === 'local') {
        $data = owbn_tm_get_local_coordinators();
    } else {
        $url = trailingslashit(get_option('owbn_tm_coordinators_url', '')) . 'coordinators';
        $key = get_option('owbn_tm_coordinators_api_key', '');
        $data = owbn_tm_remote_request($url, $key);
    }

    if (!is_wp_error($data)) {
        $ttl = owbn_tm_get_cache_ttl();
        if ($ttl > 0) {
            set_transient($cache_key, $data, $ttl);
        }
    }

    return $data;
}

/**
 * Refresh enabled caches.
 *
 * @return true|WP_Error
 */
function owbn_tm_refresh_cc_cache()
{
    $errors = [];

    if (owbn_tm_chronicles_enabled()) {
        $chronicles = owbn_tm_get_chronicles(true);
        if (is_wp_error($chronicles)) {
            $errors[] = 'Chronicles: ' . $chronicles->get_error_message();
        }
    }

    if (owbn_tm_coordinators_enabled()) {
        $coordinators = owbn_tm_get_coordinators(true);
        if (is_wp_error($coordinators)) {
            $errors[] = 'Coordinators: ' . $coordinators->get_error_message();
        }
    }

    if (!empty($errors)) {
        return new WP_Error('refresh_failed', implode(' | ', $errors));
    }

    return true;
}

/**
 * Get combined slugs for dropdowns.
 *
 * @return array ['slug' => 'Label (type)']
 */
function owbn_tm_get_all_slugs(): array
{
    $slugs = [];

    $chronicles = owbn_tm_get_chronicles();
    if (!is_wp_error($chronicles) && is_array($chronicles)) {
        foreach ($chronicles as $c) {
            $slug = $c['slug'] ?? '';
            $name = $c['title'] ?? $c['name'] ?? $slug;
            if ($slug) {
                $slugs[$slug] = $name . ' (Chronicle)';
            }
        }
    }

    $coordinators = owbn_tm_get_coordinators();
    if (!is_wp_error($coordinators) && is_array($coordinators)) {
        foreach ($coordinators as $c) {
            $slug = $c['slug'] ?? '';
            $name = $c['title'] ?? $c['name'] ?? $slug;
            if ($slug) {
                $slugs[$slug] = $name . ' (Coordinator)';
            }
        }
    }

    asort($slugs);
    return $slugs;
}
