<?php

/** File: admin/settings.php
 * Text Domain: owbn-territory-manager
 * Version: 0.9.1
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

    // Current values - Data Sources
    $chron_enabled = get_option('owbn_tm_enable_chronicles', false);
    $chron_mode    = get_option('owbn_tm_chronicles_mode', 'local');
    $chron_url     = get_option('owbn_tm_chronicles_url', '');
    $chron_key     = get_option('owbn_tm_chronicles_api_key', '');

    $coord_enabled = get_option('owbn_tm_enable_coordinators', false);
    $coord_mode    = get_option('owbn_tm_coordinators_mode', 'local');
    $coord_url     = get_option('owbn_tm_coordinators_url', '');
    $coord_key     = get_option('owbn_tm_coordinators_api_key', '');

    $cache_ttl = get_option('owbn_tm_cache_ttl', 3600);

    // Current values - API
    $api_enabled   = get_option('owbn_tm_api_enabled', false);
    $api_local_only = get_option('owbn_tm_local_only', false);
    $api_key       = get_option('owbn_tm_api_key', '');

    // Handle manual cache refresh
    if (isset($_POST['owbn_tm_refresh_cache']) && check_admin_referer('owbn_tm_refresh_cache')) {
        $result = owbn_tm_refresh_cc_cache();
        if (is_wp_error($result)) {
            add_settings_error('owbn_tm_messages', 'cache_error', $result->get_error_message(), 'error');
        } else {
            add_settings_error('owbn_tm_messages', 'cache_success', __('Cache refreshed successfully.', 'owbn-territory-manager'), 'updated');
        }
    }

    // Handle API settings save
    if (
        isset($_POST['owbn_tm_api_nonce']) &&
        wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['owbn_tm_api_nonce'])), 'save_owbn_tm_api')
    ) {
        $new_api_enabled = isset($_POST['owbn_tm_api_enabled']) && $_POST['owbn_tm_api_enabled'] === '1';
        $new_local_only  = isset($_POST['owbn_tm_local_only']) && $_POST['owbn_tm_local_only'] === '1';
        $new_api_key     = sanitize_text_field(wp_unslash($_POST['owbn_tm_api_key'] ?? ''));

        update_option('owbn_tm_api_enabled', $new_api_enabled);
        update_option('owbn_tm_local_only', $new_local_only);
        update_option('owbn_tm_api_key', $new_api_key);

        // Refresh local vars
        $api_enabled    = $new_api_enabled;
        $api_local_only = $new_local_only;
        $api_key        = $new_api_key;

        add_settings_error('owbn_tm_messages', 'api_success', __('API settings saved.', 'owbn-territory-manager'), 'updated');
    }

?>
    <div class="wrap">
        <h1><?php esc_html_e('Territory Settings', 'owbn-territory-manager'); ?></h1>
        <?php settings_errors('owbn_tm_messages'); ?>

        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <!-- DATA SOURCE SETTINGS -->
        <!-- ═══════════════════════════════════════════════════════════════════ -->

        <form method="post" action="options.php">
            <?php settings_fields('owbn_tm_settings'); ?>

            <!-- Chronicles -->
            <h2><?php esc_html_e('Chronicles Data Source', 'owbn-territory-manager'); ?></h2>
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
            <h2><?php esc_html_e('Coordinators Data Source', 'owbn-territory-manager'); ?></h2>
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

        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <!-- TERRITORY API SETTINGS -->
        <!-- ═══════════════════════════════════════════════════════════════════ -->

        <h2><?php esc_html_e('Territory API', 'owbn-territory-manager'); ?></h2>
        <p class="description"><?php esc_html_e('Allow external sites to fetch territory data from this installation.', 'owbn-territory-manager'); ?></p>

        <form method="post">
            <?php wp_nonce_field('save_owbn_tm_api', 'owbn_tm_api_nonce'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable API', 'owbn-territory-manager'); ?></th>
                    <td>
                        <label>
                            <input type="hidden" name="owbn_tm_api_enabled" value="0" />
                            <input type="checkbox"
                                name="owbn_tm_api_enabled"
                                id="owbn_tm_api_enabled"
                                value="1"
                                <?php checked($api_enabled); ?> />
                            <?php esc_html_e('Enable Territory REST API', 'owbn-territory-manager'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Local Only Mode', 'owbn-territory-manager'); ?></th>
                    <td>
                        <label>
                            <input type="hidden" name="owbn_tm_local_only" value="0" />
                            <input type="checkbox"
                                name="owbn_tm_local_only"
                                id="owbn_tm_local_only"
                                value="1"
                                <?php checked($api_local_only); ?> />
                            <?php esc_html_e('Disable external API access (local use only)', 'owbn-territory-manager'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('When enabled, API requests from external sites are rejected.', 'owbn-territory-manager'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('API URL', 'owbn-territory-manager'); ?></th>
                    <td>
                        <code id="owbn_tm_api_url"><?php echo esc_url(rest_url('owbn-tm/v1/')); ?></code>
                        <button type="button" class="button" onclick="navigator.clipboard.writeText(document.getElementById('owbn_tm_api_url').textContent)">
                            <?php esc_html_e('Copy', 'owbn-territory-manager'); ?>
                        </button>
                        <p class="description"><?php esc_html_e('Base URL for client connections.', 'owbn-territory-manager'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('API Key', 'owbn-territory-manager'); ?></th>
                    <td>
                        <input type="text"
                            name="owbn_tm_api_key"
                            id="owbn_tm_api_key"
                            value="<?php echo esc_attr($api_key); ?>"
                            class="regular-text code"
                            readonly />
                        <button type="button" class="button" onclick="owbnTmGenerateApiKey()">
                            <?php esc_html_e('Generate New', 'owbn-territory-manager'); ?>
                        </button>
                        <p class="description"><?php esc_html_e('Required for API access. Share with authorized clients.', 'owbn-territory-manager'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Save API Settings', 'owbn-territory-manager')); ?>
        </form>

        <hr />

        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <!-- STATUS -->
        <!-- ═══════════════════════════════════════════════════════════════════ -->

        <h2><?php esc_html_e('Status', 'owbn-territory-manager'); ?></h2>

        <?php
        $chronicles   = get_transient('owbn_tm_chronicles_cache');
        $coordinators = get_transient('owbn_tm_coordinators_cache');
        $territory_count = wp_count_posts('owbn_territory');
        ?>

        <table class="widefat" style="max-width:400px;">
            <tr>
                <td><strong><?php esc_html_e('Territories', 'owbn-territory-manager'); ?></strong></td>
                <td><?php echo absint($territory_count->publish ?? 0); ?> <?php esc_html_e('published', 'owbn-territory-manager'); ?></td>
            </tr>
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
            <tr>
                <td><strong><?php esc_html_e('Territory API', 'owbn-territory-manager'); ?></strong></td>
                <td>
                    <?php
                    if (!$api_enabled) {
                        esc_html_e('Disabled', 'owbn-territory-manager');
                    } elseif ($api_local_only) {
                        esc_html_e('Local Only', 'owbn-territory-manager');
                    } else {
                        esc_html_e('Enabled', 'owbn-territory-manager');
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

        function owbnTmGenerateApiKey() {
            var field = document.getElementById('owbn_tm_api_key');
            var key = 'tm_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
            field.value = key;
            field.removeAttribute('readonly');
        }
    </script>
<?php
}
