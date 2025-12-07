<?php

/** File: admin/metabox.php
 * Text Domain: owbn-territory-manager
 * Version: 0.9.0
 * @author greghacke
 * Function: Territory metabox for CPT editor
 */

defined('ABSPATH') || exit;

add_action('add_meta_boxes', 'owbn_tm_add_metaboxes');

function owbn_tm_add_metaboxes()
{
    add_meta_box(
        'owbn_tm_territory_details',
        __('Territory Details', 'owbn-territory-manager'),
        'owbn_tm_render_metabox',
        'owbn_territory',
        'normal',
        'high'
    );
}

function owbn_tm_render_metabox($post)
{
    wp_nonce_field('owbn_tm_save_metabox', 'owbn_tm_metabox_nonce');

    $countries = get_post_meta($post->ID, '_owbn_tm_countries', true) ?: [];
    $region    = get_post_meta($post->ID, '_owbn_tm_region', true) ?: '';
    $location  = get_post_meta($post->ID, '_owbn_tm_location', true) ?: '';
    $detail    = get_post_meta($post->ID, '_owbn_tm_detail', true) ?: '';
    $owner     = get_post_meta($post->ID, '_owbn_tm_owner', true) ?: '';
    $slugs     = get_post_meta($post->ID, '_owbn_tm_slug', true) ?: [];

    // Handle legacy single value
    if (!is_array($slugs)) {
        $slugs = !empty($slugs) ? [$slugs] : [];
    }

    $country_list = owbn_tm_get_country_list();
    $all_slugs    = owbn_tm_get_all_slugs();
?>
    <div class="owbn-tm-metabox">
        <table class="form-table">
            <!-- Countries (multi-select) -->
            <tr>
                <th scope="row">
                    <label for="owbn_tm_countries"><?php esc_html_e('Countries', 'owbn-territory-manager'); ?></label>
                </th>
                <td>
                    <select name="owbn_tm_countries[]" id="owbn_tm_countries" class="owbn-tm-select2" multiple="multiple" style="width:100%;">
                        <?php foreach ($country_list as $code => $name) : ?>
                            <option value="<?php echo esc_attr($code); ?>" <?php echo in_array($code, $countries, true) ? 'selected' : ''; ?>>
                                <?php echo esc_html($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e('Select one or more countries. Use Worldwide for global territories.', 'owbn-territory-manager'); ?></p>
                </td>
            </tr>

            <!-- Region -->
            <tr>
                <th scope="row">
                    <label for="owbn_tm_region"><?php esc_html_e('Region', 'owbn-territory-manager'); ?></label>
                </th>
                <td>
                    <input type="text" name="owbn_tm_region" id="owbn_tm_region"
                        value="<?php echo esc_attr($region); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e('State, province, or regional area.', 'owbn-territory-manager'); ?></p>
                </td>
            </tr>

            <!-- Location -->
            <tr>
                <th scope="row">
                    <label for="owbn_tm_location"><?php esc_html_e('Location', 'owbn-territory-manager'); ?></label>
                </th>
                <td>
                    <input type="text" name="owbn_tm_location" id="owbn_tm_location"
                        value="<?php echo esc_attr($location); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e('City, county, or specific location name.', 'owbn-territory-manager'); ?></p>
                </td>
            </tr>

            <!-- Detail -->
            <tr>
                <th scope="row">
                    <label for="owbn_tm_detail"><?php esc_html_e('Detail', 'owbn-territory-manager'); ?></label>
                </th>
                <td>
                    <input type="text" name="owbn_tm_detail" id="owbn_tm_detail"
                        value="<?php echo esc_attr($detail); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e('Additional location detail (building, landmark, etc.).', 'owbn-territory-manager'); ?></p>
                </td>
            </tr>

            <!-- Owner -->
            <tr>
                <th scope="row">
                    <label for="owbn_tm_owner"><?php esc_html_e('Owner', 'owbn-territory-manager'); ?></label>
                </th>
                <td>
                    <input type="text" name="owbn_tm_owner" id="owbn_tm_owner"
                        value="<?php echo esc_attr($owner); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e('Display name of the owner (Coordinator or Chronicle name).', 'owbn-territory-manager'); ?></p>
                </td>
            </tr>

            <!-- Slugs (multi-select, linked to CC data) -->
            <tr>
                <th scope="row">
                    <label for="owbn_tm_slug"><?php esc_html_e('Linked Slugs', 'owbn-territory-manager'); ?></label>
                </th>
                <td>
                    <?php if (!empty($all_slugs)) : ?>
                        <select name="owbn_tm_slug[]" id="owbn_tm_slug" class="owbn-tm-select2" multiple="multiple" style="width:100%;">
                            <?php foreach ($all_slugs as $s => $label) : ?>
                                <option value="<?php echo esc_attr($s); ?>" <?php echo in_array($s, $slugs, true) ? 'selected' : ''; ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select one or more Chronicles/Coordinators that share this territory.', 'owbn-territory-manager'); ?></p>
                    <?php else : ?>
                        <input type="text" name="owbn_tm_slug_text" id="owbn_tm_slug"
                            value="<?php echo esc_attr(implode(', ', $slugs)); ?>" class="regular-text" />
                        <p class="description"><?php esc_html_e('No CC data available. Enter comma-separated slugs or enable in Settings.', 'owbn-territory-manager'); ?></p>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <script>
        jQuery(function($) {
            $('.owbn-tm-select2').select2({
                placeholder: '<?php echo esc_js(__('Select...', 'owbn-territory-manager')); ?>',
                allowClear: true,
                width: '100%'
            });
        });
    </script>
<?php
}

add_action('save_post_owbn_territory', 'owbn_tm_save_metabox', 10, 2);

function owbn_tm_save_metabox($post_id, $post)
{
    // Verify nonce
    if (
        !isset($_POST['owbn_tm_metabox_nonce']) ||
        !wp_verify_nonce($_POST['owbn_tm_metabox_nonce'], 'owbn_tm_save_metabox')
    ) {
        return;
    }

    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Countries (array)
    $countries = isset($_POST['owbn_tm_countries']) && is_array($_POST['owbn_tm_countries'])
        ? array_map('sanitize_text_field', $_POST['owbn_tm_countries'])
        : [];
    update_post_meta($post_id, '_owbn_tm_countries', $countries);

    // Text fields
    $text_fields = ['region', 'location', 'detail', 'owner'];
    foreach ($text_fields as $field) {
        $key   = '_owbn_tm_' . $field;
        $value = isset($_POST['owbn_tm_' . $field])
            ? sanitize_text_field($_POST['owbn_tm_' . $field])
            : '';
        update_post_meta($post_id, $key, $value);
    }

    // Slugs (array)
    if (isset($_POST['owbn_tm_slug']) && is_array($_POST['owbn_tm_slug'])) {
        $slugs = array_map('sanitize_text_field', $_POST['owbn_tm_slug']);
    } elseif (isset($_POST['owbn_tm_slug_text'])) {
        $slugs = array_filter(array_map('trim', explode(',', sanitize_text_field($_POST['owbn_tm_slug_text']))));
    } else {
        $slugs = [];
    }
    update_post_meta($post_id, '_owbn_tm_slug', $slugs);

    // Auto-generate title if empty
    if (empty($post->post_title) || $post->post_title === __('Auto Draft')) {
        $title_parts = array_filter([
            owbn_tm_format_countries($countries),
            sanitize_text_field($_POST['owbn_tm_region'] ?? ''),
            sanitize_text_field($_POST['owbn_tm_location'] ?? ''),
        ]);

        if (!empty($title_parts)) {
            remove_action('save_post_owbn_territory', 'owbn_tm_save_metabox', 10);
            wp_update_post([
                'ID'         => $post_id,
                'post_title' => implode(' > ', $title_parts),
            ]);
            add_action('save_post_owbn_territory', 'owbn_tm_save_metabox', 10, 2);
        }
    }
}
