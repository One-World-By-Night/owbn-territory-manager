<?php

/** File: admin/settings.php
 * Text Domain: owbn-territory-manager
 * Version: 1.4.0
 * @author greghacke
 * Function: Territory Manager settings page
 */

defined('ABSPATH') || exit;

// ─── Register Settings ───────────────────────────────────────────────────────
add_action('admin_init', 'owbn_tm_register_settings');

function owbn_tm_register_settings()
{
    register_setting('owbn_tm_settings', 'owbn_tm_import_default_country', [
        'type'              => 'string',
        'default'           => 'US',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    register_setting('owbn_tm_settings', 'owbn_tm_import_unknown_country', [
        'type'              => 'string',
        'default'           => 'ZZ',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
}

// ─── Render Settings Page ────────────────────────────────────────────────────
function owbn_tm_render_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $default_country = get_option('owbn_tm_import_default_country', 'US');
    $unknown_country = get_option('owbn_tm_import_unknown_country', 'ZZ');
    $countries       = owbn_tm_get_country_list();

    if (function_exists('owc_get_client_id')) {
        $client_settings_url = admin_url('admin.php?page=' . owc_get_client_id() . '-owc-settings');
    } else {
        $client_settings_url = '';
    }

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Territory Manager Settings', 'owbn-territory-manager'); ?></h1>

        <div class="notice notice-info inline">
            <p>
                <?php esc_html_e('Territory data is served through the OWBN Client API Gateway.', 'owbn-territory-manager'); ?>
                <?php if ($client_settings_url) : ?>
                    <a href="<?php echo esc_url($client_settings_url); ?>" class="button button-secondary" style="margin-left: 8px;">
                        <?php esc_html_e('Go to OWBN Client Settings', 'owbn-territory-manager'); ?>
                    </a>
                <?php endif; ?>
            </p>
        </div>

        <?php settings_errors(); ?>

        <form method="post" action="options.php">
            <?php settings_fields('owbn_tm_settings'); ?>

            <h2><?php esc_html_e('Import Defaults', 'owbn-territory-manager'); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="owbn_tm_import_default_country">
                            <?php esc_html_e('Default Country', 'owbn-territory-manager'); ?>
                        </label>
                    </th>
                    <td>
                        <select name="owbn_tm_import_default_country" id="owbn_tm_import_default_country">
                            <?php foreach ($countries as $code => $name) : ?>
                                <option value="<?php echo esc_attr($code); ?>" <?php selected($default_country, $code); ?>>
                                    <?php echo esc_html($code . ' — ' . $name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php esc_html_e('Country applied to imported rows when no country is specified.', 'owbn-territory-manager'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="owbn_tm_import_unknown_country">
                            <?php esc_html_e('Unknown Country Code', 'owbn-territory-manager'); ?>
                        </label>
                    </th>
                    <td>
                        <select name="owbn_tm_import_unknown_country" id="owbn_tm_import_unknown_country">
                            <?php foreach ($countries as $code => $name) : ?>
                                <option value="<?php echo esc_attr($code); ?>" <?php selected($unknown_country, $code); ?>>
                                    <?php echo esc_html($code . ' — ' . $name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php esc_html_e('Country code assigned when an imported country name cannot be mapped to a known ISO code.', 'owbn-territory-manager'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
