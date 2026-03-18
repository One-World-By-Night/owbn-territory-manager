<?php
defined('ABSPATH') || exit;

add_action('admin_init', 'owbn_tm_register_settings');

function owbn_tm_register_settings() {
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

    register_setting('owbn_tm_settings', 'owbn_tm_custom_countries', [
        'type'              => 'array',
        'default'           => [],
        'sanitize_callback' => 'owbn_tm_sanitize_custom_countries',
    ]);
}

function owbn_tm_sanitize_custom_countries($input) {
    if (!is_array($input)) return [];
    $result = [];
    foreach ($input as $entry) {
        $code = strtoupper(sanitize_text_field($entry['code'] ?? ''));
        $name = sanitize_text_field($entry['name'] ?? '');
        if ($code === '' || $name === '') continue;
        $result[] = ['code' => $code, 'name' => $name];
    }
    return $result;
}

function owbn_tm_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $default_country  = get_option('owbn_tm_import_default_country', 'US');
    $unknown_country  = get_option('owbn_tm_import_unknown_country', 'ZZ');
    $custom_countries = get_option('owbn_tm_custom_countries', []);
    if (!is_array($custom_countries)) $custom_countries = [];

    $countries = owbn_tm_get_country_list();

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

            <h2><?php esc_html_e('Custom Country / Location Entries', 'owbn-territory-manager'); ?></h2>
            <p class="description" style="margin-bottom:12px;">
                <?php esc_html_e('Add game-specific or non-ISO locations (e.g. Virtual, Online, Umbra). These appear in all country dropdowns alongside the standard ISO list.', 'owbn-territory-manager'); ?>
            </p>

            <table class="widefat fixed striped" id="owbn-tm-custom-countries" style="max-width:600px;">
                <thead>
                    <tr>
                        <th style="width:120px;"><?php esc_html_e('Code', 'owbn-territory-manager'); ?></th>
                        <th><?php esc_html_e('Name', 'owbn-territory-manager'); ?></th>
                        <th style="width:80px;"></th>
                    </tr>
                </thead>
                <tbody id="owbn-tm-custom-rows">
                    <?php foreach ($custom_countries as $i => $entry) : ?>
                        <tr class="owbn-tm-custom-row">
                            <td>
                                <input type="text"
                                    name="owbn_tm_custom_countries[<?php echo $i; ?>][code]"
                                    value="<?php echo esc_attr(strtoupper($entry['code'])); ?>"
                                    class="small-text"
                                    maxlength="10"
                                    placeholder="e.g. VI"
                                    style="text-transform:uppercase;width:80px;" />
                            </td>
                            <td>
                                <input type="text"
                                    name="owbn_tm_custom_countries[<?php echo $i; ?>][name]"
                                    value="<?php echo esc_attr($entry['name']); ?>"
                                    class="regular-text"
                                    placeholder="e.g. Virtual" />
                            </td>
                            <td>
                                <button type="button" class="button button-small owbn-tm-remove-custom">
                                    <?php esc_html_e('Remove', 'owbn-territory-manager'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">
                            <button type="button" class="button" id="owbn-tm-add-custom">
                                <?php esc_html_e('+ Add Entry', 'owbn-territory-manager'); ?>
                            </button>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>

    <script>
    jQuery(function ($) {
        var rowIndex = <?php echo max(count($custom_countries), 0); ?>;

        $('#owbn-tm-add-custom').on('click', function () {
            var row = $('<tr class="owbn-tm-custom-row">' +
                '<td><input type="text" name="owbn_tm_custom_countries[' + rowIndex + '][code]" value="" class="small-text" maxlength="10" placeholder="e.g. VI" style="text-transform:uppercase;width:80px;" /></td>' +
                '<td><input type="text" name="owbn_tm_custom_countries[' + rowIndex + '][name]" value="" class="regular-text" placeholder="e.g. Virtual" /></td>' +
                '<td><button type="button" class="button button-small owbn-tm-remove-custom"><?php echo esc_js(__('Remove', 'owbn-territory-manager')); ?></button></td>' +
                '</tr>');
            $('#owbn-tm-custom-rows').append(row);
            rowIndex++;
        });

        $(document).on('click', '.owbn-tm-remove-custom', function () {
            $(this).closest('tr').remove();
            // Re-index remaining rows so there are no gaps in POST array keys
            $('#owbn-tm-custom-rows .owbn-tm-custom-row').each(function (i) {
                $(this).find('input').each(function () {
                    var name = $(this).attr('name').replace(/\[\d+\]/, '[' + i + ']');
                    $(this).attr('name', name);
                });
            });
            rowIndex = $('#owbn-tm-custom-rows .owbn-tm-custom-row').length;
        });

        // Force code inputs to uppercase on input
        $(document).on('input', 'input[name*="[code]"]', function () {
            this.value = this.value.toUpperCase();
        });
    });
    </script>
    <?php
}
