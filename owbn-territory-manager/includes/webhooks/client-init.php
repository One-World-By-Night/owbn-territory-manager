<?php

/** File: webhooks/init.php
 * Text Domain: owbn-territory-manager
 * Version: 1.1.0
 * @author greghacke
 * Function: Load webhooks files
 */

defined('ABSPATH') || exit;

/**
 * REST API Routes & Permission Check
 */

// CORS
add_action('rest_api_init', function () {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, x-api-key');
}, 15);

// Permission check
function owbn_tm_api_permission_check($request)
{
    if (get_option('owbn_tm_local_only', false)) {
        return new WP_Error('disabled', 'API is in local-only mode', ['status' => 403]);
    }

    if (!get_option('owbn_tm_api_enabled', false)) {
        return new WP_Error('disabled', 'Territory API is disabled', ['status' => 403]);
    }

    $api_key = $request->get_header('x-api-key');
    $expected_key = get_option('owbn_tm_api_key', '');

    if (!$expected_key || !$api_key || $api_key !== $expected_key) {
        return new WP_Error('unauthorized', 'Invalid or missing API key', ['status' => 403]);
    }

    return true;
}

// Register routes
add_action('rest_api_init', function () {
    $namespace = 'owbn-tm/v1';

    // OPTIONS handlers
    register_rest_route($namespace, '/territories', [
        'methods'             => 'OPTIONS',
        'callback'            => fn() => new WP_REST_Response(null, 204),
        'permission_callback' => '__return_true',
    ]);

    register_rest_route($namespace, '/territory', [
        'methods'             => 'OPTIONS',
        'callback'            => fn() => new WP_REST_Response(null, 204),
        'permission_callback' => '__return_true',
    ]);

    register_rest_route($namespace, '/territories-by-slug', [
        'methods'             => 'OPTIONS',
        'callback'            => fn() => new WP_REST_Response(null, 204),
        'permission_callback' => '__return_true',
    ]);

    // POST handlers
    register_rest_route($namespace, '/territories', [
        'methods'             => 'POST',
        'callback'            => 'owbn_tm_api_get_territories',
        'permission_callback' => 'owbn_tm_api_permission_check',
    ]);

    register_rest_route($namespace, '/territory', [
        'methods'             => 'POST',
        'callback'            => 'owbn_tm_api_get_territory',
        'permission_callback' => 'owbn_tm_api_permission_check',
    ]);

    register_rest_route($namespace, '/territories-by-slug', [
        'methods'             => 'POST',
        'callback'            => 'owbn_tm_api_get_territories_by_slug',
        'permission_callback' => 'owbn_tm_api_permission_check',
    ]);
});
