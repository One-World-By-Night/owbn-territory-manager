<?php

/** File: admin/menu.php
 * Text Domain: owbn-territory-manager
 * Version: 0.9.0
 * @author greghacke
 * Function: Register admin menus and page callbacks
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
        'owbn_tm_render_territories_page',
        'dashicons-location',
        30
    );

    // All Territories (same as parent)
    add_submenu_page(
        'owbn-territory',
        __('All Territories', 'owbn-territory-manager'),
        __('All Territories', 'owbn-territory-manager'),
        'manage_options',
        'owbn-territory',
        'owbn_tm_render_territories_page'
    );

    // Add Territory
    add_submenu_page(
        'owbn-territory',
        __('Add Territory', 'owbn-territory-manager'),
        __('Add Territory', 'owbn-territory-manager'),
        'manage_options',
        'owbn-territory-add',
        'owbn_tm_render_add_territory_page'
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
}

function owbn_tm_render_territories_page()
{
    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('All Territories', 'owbn-territory-manager') . '</h1>';
    // TODO: Territory list table
    echo '</div>';
}

function owbn_tm_render_add_territory_page()
{
    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Add Territory', 'owbn-territory-manager') . '</h1>';
    // TODO: Add territory form
    echo '</div>';
}
