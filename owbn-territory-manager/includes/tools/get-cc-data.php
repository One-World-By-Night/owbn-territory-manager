<?php

/** File: tools/get-cc-data.php
 * Text Domain: owbn-territory-manager
 * Version: 1.3.0
 * @author greghacke
 * Function: Fetch chronicle/coordinator data via OWBN Client
 */

defined('ABSPATH') || exit;

/**
 * Get chronicles via OWBN Client.
 *
 * Delegates to owc_get_chronicles() which handles local CPT queries and
 * remote fetching with caching. Returns an empty array if the client
 * function is unavailable.
 *
 * @return array|WP_Error
 */
function owbn_tm_get_chronicles()
{
    if (function_exists('owc_get_chronicles')) {
        return owc_get_chronicles();
    }
    return [];
}

/**
 * Get coordinators via OWBN Client.
 *
 * Delegates to owc_get_coordinators() which handles local CPT queries and
 * remote fetching with caching. Returns an empty array if the client
 * function is unavailable.
 *
 * @return array|WP_Error
 */
function owbn_tm_get_coordinators()
{
    if (function_exists('owc_get_coordinators')) {
        return owc_get_coordinators();
    }
    return [];
}

/**
 * Refresh chronicle and coordinator caches via OWBN Client.
 *
 * @return true|WP_Error
 */
function owbn_tm_refresh_cc_cache()
{
    if (function_exists('owc_refresh_all_caches')) {
        return owc_refresh_all_caches();
    }
    return true;
}

/**
 * Get combined chronicle/coordinator slugs for admin dropdowns.
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
                $slugs[$slug] = $slug . ' – ' . $name . ' (Chronicle)';
            }
        }
    }

    $coordinators = owbn_tm_get_coordinators();
    if (!is_wp_error($coordinators) && is_array($coordinators)) {
        foreach ($coordinators as $c) {
            $slug = $c['slug'] ?? '';
            $name = $c['title'] ?? $c['name'] ?? $slug;
            if ($slug) {
                $slugs[$slug] = $slug . ' – ' . $name . ' (Coordinator)';
            }
        }
    }

    asort($slugs);
    return $slugs;
}
