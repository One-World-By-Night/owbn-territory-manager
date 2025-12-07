<?php

/** File: admin/settings.php
 * Text Domain: owbn-territory-manager
 * Version: 0.9.0
 * @author greghacke
 * Function: Settings page and options registration
 */

defined('ABSPATH') || exit;

// ─── Register Settings ───────────────────────────────────────────────────────
add_action('admin_init', 'owbn_tm_register_settings');

function owbn_tm_register_settings()
{
    $group = 'owbn_tm_settings';

    // Chronicles
    register_setting($group, 'owbn_tm_enable_chronicles', [
        'type' => 'boolean',
        'default' => false,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ]);
    register_setting($group, 'owbn_tm_chronicles_mode', [
        'type' => 'string',
        'default' => 'local',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    register_setting($group, 'owbn_tm_chronicles_url', [
        'type' => 'string',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    register_setting($group, 'owbn_tm_chronicles_api_key', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    // Coordinators
    register_setting($group, 'owbn_tm_enable_coordinators', [
        'type' => 'boolean',
        'default' => false,
        'sanitize_callback' => 'rest_sanitize_boolean',
    ]);
    register_setting($group, 'owbn_tm_coordinators_mode', [
        'type' => 'string',
        'default' => 'local',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    register_setting($group, 'owbn_tm_coordinators_url', [
        'type' => 'string',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    register_setting($group, 'owbn_tm_coordinators_api_key', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    // Cache
    register_setting($group, 'owbn_tm_cache_ttl', [
        'type' => 'integer',
        'default' => 3600,
        'sanitize_callback' => 'absint',
    ]);
}

// ─── Render Settings Page ────────────────────────────────────────────────────
function owbn_tm_render_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    // Current values
    $chron_enabled = get_option('owbn_tm_enable_chronicles', false);
    $chron_mode    = get_option('owbn_tm_chronicles_mode', 'local');
    $chron_url     = get_option('owbn_tm_chronicles_url', '');
    $chron_key     = get_option('owbn_tm_chronicles_api_key', '');

    $coord_enabled = get_option('owbn_tm_enable_coordinators', false);
    $coord_mode    = get_option('owbn_tm_coordinators_mode', 'local');
    $coord_url     = get_option('owbn_tm_coordinators_url', '');
    $coord_key     = get_option('owbn_tm_coordinators_api_key', '');

    $cache_ttl = get_option('owbn_tm_cache_ttl', 3600);

    // Handle manual cache refresh
    if (isset($_POST['owbn_tm_refresh_cache']) && check_admin_referer('owbn_tm_refresh_cache')) {
        $result = owbn_tm_refresh_cc_cache();
        if (is_wp_error($result)) {
            add_settings_error('owbn_tm_messages', 'cache_error', $result->get_error_message(), 'error');
        } else {
            add_settings_error('owbn_tm_messages', 'cache_success', __('Cache refreshed successfully.', 'owbn-territory-manager'), 'updated');
        }
    }

?>
    <div class="wrap">
        <h1><?php esc_html_e('Territory Settings', 'owbn-territory-manager'); ?></h1>
        <?php settings_errors('owbn_tm_messages'); ?>

        <form method="post" action="options.php">
            <?php settings_fields('owbn_tm_settings'); ?>

            <!-- Chronicles -->
            <h2><?php esc_html_e('Chronicles', 'owbn-territory-manager'); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable', 'owbn-territory-manager'); ?></th>
                    <td>
                        <label>
                            <input type="hidden" name="owbn_tm_enable_chronicles" value="0" />
                            <input type="checkbox"
                                name="owbn_tm_enable_chronicles"
                                id="owbn_tm_enable_chronicles"
                                value="1"
                                <?php checked($chron_enabled); ?> />
                            <?php esc_html_e('Enable Chronicles', 'owbn-territory-manager'); ?>
                        </label>
                    </td>
                </tr>
                <tr class="owbn-tm-chronicles-options" <?php echo $chron_enabled ? '' : 'style="display:none;"'; ?>>
                    <th scope="row"><?php esc_html_e('Data Source', 'owbn-territory-manager'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio"
                                    name="owbn_tm_chronicles_mode"
                                    class="owbn-tm-chronicles-mode"
                                    value="local"
                                    <?php checked($chron_mode, 'local'); ?> />
                                <?php esc_html_e('Local (same site)', 'owbn-territory-manager'); ?>
                            </label><br>
                            <label>
                                <input type="radio"
                                    name="owbn_tm_chronicles_mode"
                                    class="owbn-tm-chronicles-mode"
                                    value="remote"
                                    <?php checked($chron_mode, 'remote'); ?> />
                                <?php esc_html_e('Remote API', 'owbn-territory-manager'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr class="owbn-tm-chronicles-options owbn-tm-chronicles-remote" <?php echo ($chron_enabled && $chron_mode === 'remote') ? '' : 'style="display:none;"'; ?>>
                    <th scope="row"><?php esc_html_e('API URL', 'owbn-territory-manager'); ?></th>
                    <td>
                        <input type="url"
                            name="owbn_tm_chronicles_url"
                            value="<?php echo esc_url($chron_url); ?>"
                            class="regular-text"
                            placeholder="https://example.com/wp-json/owbn-cc/v1/" />
                    </td>
                </tr>
                <tr class="owbn-tm-chronicles-options owbn-tm-chronicles-remote" <?php echo ($chron_enabled && $chron_mode === 'remote') ? '' : 'style="display:none;"'; ?>>
                    <th scope="row"><?php esc_html_e('API Key', 'owbn-territory-manager'); ?></th>
                    <td>
                        <input type="text"
                            name="owbn_tm_chronicles_api_key"
                            value="<?php echo esc_attr($chron_key); ?>"
                            class="regular-text code" />
                    </td>
                </tr>
            </table>

            <hr />

            <!-- Coordinators -->
            <h2><?php esc_html_e('Coordinators', 'owbn-territory-manager'); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable', 'owbn-territory-manager'); ?></th>
                    <td>
                        <label>
                            <input type="hidden" name="owbn_tm_enable_coordinators" value="0" />
                            <input type="checkbox"
                                name="owbn_tm_enable_coordinators"
                                id="owbn_tm_enable_coordinators"
                                value="1"
                                <?php checked($coord_enabled); ?> />
                            <?php esc_html_e('Enable Coordinators', 'owbn-territory-manager'); ?>
                        </label>
                    </td>
                </tr>
                <tr class="owbn-tm-coordinators-options" <?php echo $coord_enabled ? '' : 'style="display:none;"'; ?>>
                    <th scope="row"><?php esc_html_e('Data Source', 'owbn-territory-manager'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio"
                                    name="owbn_tm_coordinators_mode"
                                    class="owbn-tm-coordinators-mode"
                                    value="local"
                                    <?php checked($coord_mode, 'local'); ?> />
                                <?php esc_html_e('Local (same site)', 'owbn-territory-manager'); ?>
                            </label><br>
                            <label>
                                <input type="radio"
                                    name="owbn_tm_coordinators_mode"
                                    class="owbn-tm-coordinators-mode"
                                    value="remote"
                                    <?php checked($coord_mode, 'remote'); ?> />
                                <?php esc_html_e('Remote API', 'owbn-territory-manager'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr class="owbn-tm-coordinators-options owbn-tm-coordinators-remote" <?php echo ($coord_enabled && $coord_mode === 'remote') ? '' : 'style="display:none;"'; ?>>
                    <th scope="row"><?php esc_html_e('API URL', 'owbn-territory-manager'); ?></th>
                    <td>
                        <input type="url"
                            name="owbn_tm_coordinators_url"
                            value="<?php echo esc_url($coord_url); ?>"
                            class="regular-text"
                            placeholder="https://example.com/wp-json/owbn-cc/v1/" />
                    </td>
                </tr>
                <tr class="owbn-tm-coordinators-options owbn-tm-coordinators-remote" <?php echo ($coord_enabled && $coord_mode === 'remote') ? '' : 'style="display:none;"'; ?>>
                    <th scope="row"><?php esc_html_e('API Key', 'owbn-territory-manager'); ?></th>
                    <td>
                        <input type="text"
                            name="owbn_tm_coordinators_api_key"
                            value="<?php echo esc_attr($coord_key); ?>"
                            class="regular-text code" />
                    </td>
                </tr>
            </table>

            <hr />

            <!-- Cache -->
            <h2><?php esc_html_e('Cache Settings', 'owbn-territory-manager'); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Cache TTL (seconds)', 'owbn-territory-manager'); ?></th>
                    <td>
                        <input type="number" name="owbn_tm_cache_ttl"
                            value="<?php echo esc_attr($cache_ttl); ?>"
                            class="small-text" min="0" />
                        <p class="description"><?php esc_html_e('0 = no caching', 'owbn-territory-manager'); ?></p>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Save Settings', 'owbn-territory-manager')); ?>
        </form>

        <hr />

        <h2><?php esc_html_e('CC Data Cache', 'owbn-territory-manager'); ?></h2>
        <?php
        $chronicles   = get_transient('owbn_tm_chronicles_cache');
        $coordinators = get_transient('owbn_tm_coordinators_cache');
        ?>
        <table class="widefat" style="max-width:400px;">
            <tr>
                <td><strong><?php esc_html_e('Chronicles', 'owbn-territory-manager'); ?></strong></td>
                <td>
                    <?php
                    if (!$chron_enabled) {
                        esc_html_e('Disabled', 'owbn-territory-manager');
                    } elseif (is_array($chronicles)) {
                        echo count($chronicles) . ' (' . esc_html($chron_mode) . ')';
                    } else {
                        echo '—';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td><strong><?php esc_html_e('Coordinators', 'owbn-territory-manager'); ?></strong></td>
                <td>
                    <?php
                    if (!$coord_enabled) {
                        esc_html_e('Disabled', 'owbn-territory-manager');
                    } elseif (is_array($coordinators)) {
                        echo count($coordinators) . ' (' . esc_html($coord_mode) . ')';
                    } else {
                        echo '—';
                    }
                    ?>
                </td>
            </tr>
        </table>

        <form method="post" style="margin-top:1em;">
            <?php wp_nonce_field('owbn_tm_refresh_cache'); ?>
            <button type="submit" name="owbn_tm_refresh_cache" class="button button-secondary">
                <?php esc_html_e('Refresh CC Data', 'owbn-territory-manager'); ?>
            </button>
        </form>
    </div>

    <script>
        (function($) {
            // Chronicles toggle
            $('#owbn_tm_enable_chronicles').on('change', function() {
                $('.owbn-tm-chronicles-options').toggle(this.checked);
                if (!this.checked) {
                    $('.owbn-tm-chronicles-remote').hide();
                } else {
                    var isRemote = $('.owbn-tm-chronicles-mode:checked').val() === 'remote';
                    $('.owbn-tm-chronicles-remote').toggle(isRemote);
                }
            });

            $('.owbn-tm-chronicles-mode').on('change', function() {
                $('.owbn-tm-chronicles-remote').toggle(this.value === 'remote');
            });

            // Coordinators toggle
            $('#owbn_tm_enable_coordinators').on('change', function() {
                $('.owbn-tm-coordinators-options').toggle(this.checked);
                if (!this.checked) {
                    $('.owbn-tm-coordinators-remote').hide();
                } else {
                    var isRemote = $('.owbn-tm-coordinators-mode:checked').val() === 'remote';
                    $('.owbn-tm-coordinators-remote').toggle(isRemote);
                }
            });

            $('.owbn-tm-coordinators-mode').on('change', function() {
                $('.owbn-tm-coordinators-remote').toggle(this.value === 'remote');
            });
        })(jQuery);
    </script>
<?php
}
