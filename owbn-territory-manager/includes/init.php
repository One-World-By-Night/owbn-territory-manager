<?php

/** File: includes/init.php
 * Text Domain: owbn-territory-manager
 * Version: 1.2.0
 * @author greghacke
 * Function: Single entry point to load all plugin components
 */

defined('ABSPATH') || exit;

// ─── Subfolder Loaders ───────────────────────────────────────────────────────
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/helper/init.php';
require_once __DIR__ . '/admin/init.php';
require_once __DIR__ . '/webhooks/init.php';
require_once __DIR__ . '/tools/init.php';
require_once __DIR__ . '/utils/init.php';

// ─── Enqueue Assets ──────────────────────────────────────────────────────────
add_action('admin_enqueue_scripts', 'owbn_tm_enqueue_admin_assets');

function owbn_tm_enqueue_admin_assets($hook)
{
    // Only load Select2 on territory manager admin pages
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'owbn_territory') {
        return;
    }

    $assets_url = OWBN_TM_PLUGIN_URL . 'includes/assets/';

    wp_enqueue_style(
        'select2-css',
        $assets_url . 'css/select2.min.css',
        [],
        '4.0.13'
    );

    wp_enqueue_script(
        'select2-js',
        $assets_url . 'js/select2.min.js',
        ['jquery'],
        '4.0.13',
        true
    );
}

// ─── Plugin Loaded Hook ──────────────────────────────────────────────────────
do_action('owbn_territory_manager_loaded');
