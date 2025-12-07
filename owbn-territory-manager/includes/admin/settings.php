<?php

/** File: admin/settings.php
 * Text Domain: owbn-territory-manager
 * Version: 0.9.0
 * @author greghacke
 * Function: Settings page and options registration
 */

defined('ABSPATH') || exit;

add_action('admin_init', 'owbn_tm_register_settings');

function owbn_tm_register_settings()
{
    register_setting('owbn_tm_settings', 'owbn_tm_options');

    add_settings_section(
        'owbn_tm_general',
        __('General Settings', 'owbn-territory-manager'),
        '__return_null',
        'owbn-territory-settings'
    );
}

function owbn_tm_render_settings_page()
{
?>
    <div class="wrap">
        <h1><?php esc_html_e('Territory Settings', 'owbn-territory-manager'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('owbn_tm_settings');
            do_settings_sections('owbn-territory-settings');
            submit_button();
            ?>
        </form>
    </div>
<?php
}
