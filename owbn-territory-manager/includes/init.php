<?php
defined('ABSPATH') || exit;

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/helper/init.php';
require_once __DIR__ . '/admin/init.php';
require_once __DIR__ . '/tools/init.php';
require_once __DIR__ . '/utils/init.php';

add_action('admin_enqueue_scripts', 'owbn_tm_enqueue_admin_assets');

function owbn_tm_enqueue_admin_assets($hook) {
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'owbn_territory') {
        return;
    }

    $assets_url = OWBN_TM_PLUGIN_URL . 'includes/assets/';

    wp_enqueue_style('select2-css', $assets_url . 'css/select2.min.css', [], '4.0.13');
    wp_enqueue_script('select2-js', $assets_url . 'js/select2.min.js', ['jquery'], '4.0.13', true);
}

do_action('owbn_territory_manager_loaded');
