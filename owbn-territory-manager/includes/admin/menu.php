<?php

/** File: admin/menu.php
 * Text Domain: owbn-territory-manager
 * Version: 1.0.0
 * @author greghacke
 * Function: Register admin menus
 */

defined('ABSPATH') || exit;

add_action('admin_menu', 'owbn_tm_register_admin_menu');

function owbn_tm_register_admin_menu()
{
    // Main menu
    add_menu_page(
        __('OWBN Territory', 'owbn-territory-manager'),
        __('OWBN Territory', 'owbn-territory-manager'),
        'manage_options',
        'owbn-territory',
        null,
        'dashicons-location',
        30
    );

    // Settings
    add_submenu_page(
        'owbn-territory',
        __('Settings', 'owbn-territory-manager'),
        __('Settings', 'owbn-territory-manager'),
        'manage_options',
        'owbn-territory-settings',
        'owbn_tm_render_settings_page'
    );

    // Import tool
    add_submenu_page(
        'owbn-territory',
        __('Import', 'owbn-territory-manager'),
        __('Import', 'owbn-territory-manager'),
        'manage_options',
        'owbn-territory-import',
        'owbn_tm_render_import_page'
    );
}
