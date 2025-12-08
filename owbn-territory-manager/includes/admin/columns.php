<?php

/** File: admin/columns.php
 * Text Domain: owbn-territory-manager
 * Version: 1.0.0
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
