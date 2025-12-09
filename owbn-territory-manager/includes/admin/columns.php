<?php

/** File: admin/columns.php
 * Text Domain: owbn-territory-manager
 * Version: 1.1.0
 * @author greghacke
 * Function: Admin list table columns for territories
 */

defined('ABSPATH') || exit;

// ─── Register Columns ────────────────────────────────────────────────────────
add_filter('manage_owbn_territory_posts_columns', 'owbn_tm_register_columns');

function owbn_tm_register_columns($columns)
{
    $new_columns = [];

    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;

        // Insert custom columns after title
        if ($key === 'title') {
            $new_columns['countries'] = __('Countries', 'owbn-territory-manager');
            $new_columns['region']    = __('Region', 'owbn-territory-manager');
            $new_columns['location']  = __('Location', 'owbn-territory-manager');
            $new_columns['detail']    = __('Detail', 'owbn-territory-manager');
            $new_columns['owner']     = __('Owner', 'owbn-territory-manager');
            $new_columns['slugs']     = __('Slugs', 'owbn-territory-manager');
        }
    }

    return $new_columns;
}

// ─── Populate Columns ────────────────────────────────────────────────────────
add_action('manage_owbn_territory_posts_custom_column', 'owbn_tm_populate_columns', 10, 2);

function owbn_tm_populate_columns($column, $post_id)
{
    switch ($column) {
        case 'countries':
            $countries = get_post_meta($post_id, '_owbn_tm_countries', true);
            if (is_array($countries) && !empty($countries)) {
                echo esc_html(implode(', ', $countries));
            } else {
                echo '—';
            }
            break;

        case 'region':
            $value = get_post_meta($post_id, '_owbn_tm_region', true);
            echo $value ? esc_html($value) : '—';
            break;

        case 'location':
            $value = get_post_meta($post_id, '_owbn_tm_location', true);
            echo $value ? esc_html($value) : '—';
            break;

        case 'detail':
            $value = get_post_meta($post_id, '_owbn_tm_detail', true);
            echo $value ? esc_html($value) : '—';
            break;

        case 'owner':
            $value = get_post_meta($post_id, '_owbn_tm_owner', true);
            echo $value ? esc_html($value) : '—';
            break;

        case 'slugs':
            $slugs = get_post_meta($post_id, '_owbn_tm_slug', true);
            if (is_array($slugs) && !empty($slugs)) {
                $slugs = array_values(array_unique(array_filter(array_map('trim', $slugs))));
                echo esc_html(implode(', ', $slugs));
            } else {
                echo '—';
            }
            break;
    }
}

// ─── Make Columns Sortable (Optional) ────────────────────────────────────────
add_filter('manage_edit-owbn_territory_sortable_columns', 'owbn_tm_sortable_columns');

function owbn_tm_sortable_columns($columns)
{
    $columns['region']   = 'region';
    $columns['location'] = 'location';
    $columns['owner']    = 'owner';
    return $columns;
}

add_action('pre_get_posts', 'owbn_tm_sort_columns');

function owbn_tm_sort_columns($query)
{
    if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'owbn_territory') {
        return;
    }

    $orderby = $query->get('orderby');

    switch ($orderby) {
        case 'region':
            $query->set('meta_key', '_owbn_tm_region');
            $query->set('orderby', 'meta_value');
            break;
        case 'location':
            $query->set('meta_key', '_owbn_tm_location');
            $query->set('orderby', 'meta_value');
            break;
        case 'owner':
            $query->set('meta_key', '_owbn_tm_owner');
            $query->set('orderby', 'meta_value');
            break;
    }
}

// ─── Extend Search to Include Meta Fields ────────────────────────────────────
add_action('pre_get_posts', 'owbn_tm_extend_search');

function owbn_tm_extend_search($query)
{
    if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'owbn_territory') {
        return;
    }

    $search = $query->get('s');
    if (empty($search)) {
        return;
    }

    $query->set('_owbn_tm_search', $search);
    $query->set('s', '');
}

add_filter('posts_where', 'owbn_tm_search_where', 10, 2);

function owbn_tm_search_where($where, $query)
{
    if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'owbn_territory') {
        return $where;
    }

    $search = $query->get('_owbn_tm_search');
    if (empty($search)) {
        return $where;
    }

    global $wpdb;
    $like = '%' . $wpdb->esc_like($search) . '%';

    $where .= $wpdb->prepare(
        " AND ({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.ID IN (
            SELECT post_id FROM {$wpdb->postmeta}
            WHERE meta_key = '_owbn_tm_slug'
            AND meta_value LIKE %s
        ))",
        $like,
        $like
    );

    return $where;
}

// ─── Bulk Edit Support ───────────────────────────────────────────────────────
add_action('bulk_edit_custom_box', 'owbn_tm_bulk_edit_custom_box', 10, 2);

function owbn_tm_bulk_edit_custom_box($column_name, $post_type)
{
    if ($post_type !== 'owbn_territory' || $column_name !== 'slugs') {
        return;
    }

    $all_slugs = owbn_tm_get_all_slugs();
?>
    <fieldset class="inline-edit-col-right">
        <div class="inline-edit-col">
            <label class="inline-edit-slugs">
                <span class="title"><?php esc_html_e('Slugs', 'owbn-territory-manager'); ?></span>
                <select name="owbn_tm_bulk_slug[]" id="owbn_tm_bulk_slug" multiple="multiple" style="width:100%;">
                    <?php foreach ($all_slugs as $s => $label) : ?>
                        <option value="<?php echo esc_attr($s); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <p class="description" style="margin-top:5px;">
                <label>
                    <input type="checkbox" name="owbn_tm_bulk_slug_replace" value="1" checked />
                    <?php esc_html_e('Replace existing slugs (uncheck to skip)', 'owbn-territory-manager'); ?>
                </label>
            </p>
        </div>
    </fieldset>
<?php
}

add_action('admin_footer-edit.php', 'owbn_tm_bulk_edit_js');

function owbn_tm_bulk_edit_js()
{
    global $post_type;
    if ($post_type !== 'owbn_territory') {
        return;
    }
?>
    <script>
        jQuery(function($) {
            var select2Initialized = false;

            // Initialize Select2 when bulk edit row appears
            $(document).ajaxComplete(function(event, xhr, settings) {
                if (settings.data && settings.data.indexOf('action=inline-save') === -1) {
                    initBulkSelect2();
                }
            });

            // Also init on click
            $(document).on('click', '.editinline', function() {
                setTimeout(initBulkSelect2, 200);
            });

            function initBulkSelect2() {
                var $select = $('#owbn_tm_bulk_slug');
                if ($select.length && !$select.hasClass('select2-hidden-accessible')) {
                    $select.select2({
                        placeholder: '<?php echo esc_js(__('Type to search...', 'owbn-territory-manager')); ?>',
                        allowClear: true,
                        width: '100%',
                        minimumInputLength: 1
                    });
                    select2Initialized = true;
                }
            }

            // Handle bulk edit save
            $(document).on('click', '#bulk_edit', function() {
                var $bulk_row = $('#bulk-edit');
                var $post_ids = $bulk_row.find('#bulk-titles-list .ntdelbutton').map(function() {
                    return $(this).attr('id').replace('_', '');
                }).get();

                var slugs = $('#owbn_tm_bulk_slug').val() || [];
                var replace = $('input[name="owbn_tm_bulk_slug_replace"]').is(':checked');

                if (slugs.length > 0 || replace) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'owbn_tm_bulk_edit_save',
                            post_ids: $post_ids,
                            slugs: slugs,
                            replace: replace ? 1 : 0,
                            nonce: '<?php echo wp_create_nonce('owbn_tm_bulk_edit'); ?>'
                        }
                    });
                }
            });
        });
    </script>
<?php
}

add_action('wp_ajax_owbn_tm_bulk_edit_save', 'owbn_tm_bulk_edit_save');

function owbn_tm_bulk_edit_save()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'owbn_tm_bulk_edit')) {
        wp_die();
    }

    if (!current_user_can('edit_posts')) {
        wp_die();
    }

    $post_ids = isset($_POST['post_ids']) ? array_map('absint', $_POST['post_ids']) : [];
    $slugs = isset($_POST['slugs']) && is_array($_POST['slugs']) ? array_map('sanitize_text_field', $_POST['slugs']) : [];
    $replace = isset($_POST['replace']) && $_POST['replace'] === '1';

    if (empty($post_ids)) {
        wp_die();
    }

    if ($replace) {
        foreach ($post_ids as $post_id) {
            if (get_post_type($post_id) === 'owbn_territory') {
                update_post_meta($post_id, '_owbn_tm_slug', $slugs);
            }
        }
    }

    wp_die();
}
