<?php

/** File: admin/settings.php
 * Text Domain: owbn-territory-manager
 * Version: 1.3.0
 * @author greghacke
 * Function: Settings page
 */

defined('ABSPATH') || exit;

// ─── Register Settings ───────────────────────────────────────────────────────
add_action('admin_init', 'owbn_tm_register_settings');

function owbn_tm_register_settings()
{
    // No configurable settings — all API and data source settings have been
    // moved to OWBN Client. See Settings > OWBN Client.
}

// ─── Render Settings Page ────────────────────────────────────────────────────
function owbn_tm_render_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $client_settings_url = admin_url('options-general.php?page=owbn-client-settings');

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Territory Manager', 'owbn-territory-manager'); ?></h1>

        <div class="notice notice-info inline">
            <p>
                <?php esc_html_e('Territory Manager has no standalone settings. Territory data is served through the OWBN Client API Gateway. Chronicle and Coordinator data comes from OWBN Client.', 'owbn-territory-manager'); ?>
            </p>
            <p>
                <a href="<?php echo esc_url($client_settings_url); ?>" class="button button-primary">
                    <?php esc_html_e('Go to OWBN Client Settings', 'owbn-territory-manager'); ?>
                </a>
            </p>
        </div>
    </div>
    <?php
}
