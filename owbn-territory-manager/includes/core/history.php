<?php
defined('ABSPATH') || exit;

/**
 * Metadata versioning for territory fields.
 * Tracks changes to: countries, region, location, detail, owner, linked_slugs
 */

define('OWBN_TM_HISTORY_KEY', '_owbn_tm_history');
define('OWBN_TM_HISTORY_MAX', 50);

$_owbn_tm_before = [];

/**
 * Capture field values before the save fires (priority 5, before metabox save at 10).
 */
add_action('save_post_owbn_territory', function ($post_id) {
    global $_owbn_tm_before;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $_owbn_tm_before[$post_id] = [
        'countries' => get_post_meta($post_id, '_owbn_tm_countries', true) ?: [],
        'region'    => get_post_meta($post_id, '_owbn_tm_region',    true) ?: '',
        'location'  => get_post_meta($post_id, '_owbn_tm_location',  true) ?: '',
        'detail'    => get_post_meta($post_id, '_owbn_tm_detail',    true) ?: '',
        'owner'     => get_post_meta($post_id, '_owbn_tm_owner',     true) ?: '',
        'slugs'     => get_post_meta($post_id, '_owbn_tm_slug',      true) ?: [],
    ];
}, 5);

/**
 * After the save fires (priority 15), diff before vs after and record if anything changed.
 */
add_action('save_post_owbn_territory', function ($post_id) {
    global $_owbn_tm_before;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (!isset($_owbn_tm_before[$post_id])) return;

    $before = $_owbn_tm_before[$post_id];
    unset($_owbn_tm_before[$post_id]);

    $after = [
        'countries' => get_post_meta($post_id, '_owbn_tm_countries', true) ?: [],
        'region'    => get_post_meta($post_id, '_owbn_tm_region',    true) ?: '',
        'location'  => get_post_meta($post_id, '_owbn_tm_location',  true) ?: '',
        'detail'    => get_post_meta($post_id, '_owbn_tm_detail',    true) ?: '',
        'owner'     => get_post_meta($post_id, '_owbn_tm_owner',     true) ?: '',
        'slugs'     => get_post_meta($post_id, '_owbn_tm_slug',      true) ?: [],
    ];

    $changes = [];
    foreach ($before as $field => $old_val) {
        $new_val = $after[$field];
        // Normalize arrays for comparison
        if (is_array($old_val)) sort($old_val);
        if (is_array($new_val)) sort($new_val);
        if ($old_val !== $new_val) {
            $changes[$field] = ['from' => $before[$field], 'to' => $after[$field]];
        }
    }

    if (empty($changes)) return;

    $user     = wp_get_current_user();
    $entry    = [
        'timestamp' => current_time('mysql'),
        'user_id'   => $user->ID,
        'user_name' => $user->display_name ?: $user->user_login,
        'changes'   => $changes,
    ];

    $history = get_post_meta($post_id, OWBN_TM_HISTORY_KEY, true);
    if (!is_array($history)) $history = [];

    array_unshift($history, $entry);
    $history = array_slice($history, 0, OWBN_TM_HISTORY_MAX);

    update_post_meta($post_id, OWBN_TM_HISTORY_KEY, $history);
}, 15);

/**
 * Register the history meta field.
 */
add_action('init', function () {
    register_post_meta('owbn_territory', OWBN_TM_HISTORY_KEY, [
        'type'         => 'array',
        'description'  => 'Territory field change history',
        'single'       => true,
        'default'      => [],
        'show_in_rest' => false,
    ]);
});

/**
 * History metabox — sidebar, read-only.
 */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'owbn_tm_history',
        __('Change History', 'owbn-territory-manager'),
        'owbn_tm_render_history_metabox',
        'owbn_territory',
        'side',
        'low'
    );
});

function owbn_tm_render_history_metabox($post) {
    $history = get_post_meta($post->ID, OWBN_TM_HISTORY_KEY, true);

    if (empty($history) || !is_array($history)) {
        echo '<p style="color:#888;">' . esc_html__('No changes recorded yet.', 'owbn-territory-manager') . '</p>';
        return;
    }

    $field_labels = [
        'countries' => __('Countries', 'owbn-territory-manager'),
        'region'    => __('Region', 'owbn-territory-manager'),
        'location'  => __('Location', 'owbn-territory-manager'),
        'detail'    => __('Detail', 'owbn-territory-manager'),
        'owner'     => __('Owner', 'owbn-territory-manager'),
        'slugs'     => __('Linked Slugs', 'owbn-territory-manager'),
    ];

    echo '<div style="max-height:400px;overflow-y:auto;font-size:12px;">';
    foreach ($history as $entry) {
        $ts   = esc_html($entry['timestamp'] ?? '');
        $user = esc_html($entry['user_name'] ?? '—');
        echo '<div style="border-bottom:1px solid #ddd;padding:6px 0;">';
        echo '<strong>' . $ts . '</strong> &mdash; ' . $user . '<br>';
        foreach ($entry['changes'] as $field => $diff) {
            $label = esc_html($field_labels[$field] ?? $field);
            $from  = owbn_tm_history_format_value($diff['from']);
            $to    = owbn_tm_history_format_value($diff['to']);
            echo '<span style="color:#555;">' . $label . ':</span> ';
            echo '<span style="color:#a00;">' . esc_html($from) . '</span>';
            echo ' &rarr; ';
            echo '<span style="color:#080;">' . esc_html($to) . '</span><br>';
        }
        echo '</div>';
    }
    echo '</div>';
}

function owbn_tm_history_format_value($val) {
    if (is_array($val)) {
        return empty($val) ? '(none)' : implode(', ', $val);
    }
    return $val === '' ? '(empty)' : $val;
}
