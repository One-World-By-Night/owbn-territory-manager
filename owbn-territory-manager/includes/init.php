<?php

/** File: includes/init.php
 * Text Domain: owbn-territory-manager
 * Version: 1.1.0
 * @author greghacke
 * Function: Single entry point to load all plugin components
 */

defined('ABSPATH') || exit;

// ─── Subfolder Loaders ───────────────────────────────────────────────────────
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/classes/init.php';
require_once __DIR__ . '/helper/init.php';
require_once __DIR__ . '/admin/init.php';
require_once __DIR__ . '/webhooks/init.php';
require_once __DIR__ . '/render/init.php';
require_once __DIR__ . '/shortcodes/init.php';
require_once __DIR__ . '/templates/init.php';
require_once __DIR__ . '/languages/init.php';
require_once __DIR__ . '/tools/init.php';
require_once __DIR__ . '/utils/init.php';
require_once __DIR__ . '/docs/init.php';

// ─── Enqueue Assets ──────────────────────────────────────────────────────────
add_action('wp_enqueue_scripts', 'owbn_tm_enqueue_assets');
add_action('admin_enqueue_scripts', 'owbn_tm_enqueue_assets');

function owbn_tm_enqueue_assets()
{
    $assets_url = OWBN_TM_PLUGIN_URL . 'includes/assets/';

    // Select2 CSS
    wp_enqueue_style(
        'select2-css',
        $assets_url . 'css/select2.min.css',
        [],
        '4.0.13'
    );

    // Plugin CSS
    wp_enqueue_style(
        'owbn-territory-manager-css',
        $assets_url . 'css/owbn-territory-manager.css',
        ['select2-css'],
        OWBN_TM_VERSION
    );

    // Select2 JS
    wp_enqueue_script(
        'select2-js',
        $assets_url . 'js/select2.min.js',
        ['jquery'],
        '4.0.13',
        true
    );

    // Plugin JS
    wp_enqueue_script(
        'owbn-territory-manager-js',
        $assets_url . 'js/owbn-territory-manager.js',
        ['jquery', 'select2-js'],
        OWBN_TM_VERSION,
        true
    );
}

// ─── Plugin Loaded Hook ──────────────────────────────────────────────────────
do_action('owbn_territory_manager_loaded');
