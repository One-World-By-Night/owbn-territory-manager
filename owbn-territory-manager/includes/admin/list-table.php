<?php

/** File: admin/list-table.php
 * Text Domain: owbn-territory-manager
 * Version: 0.9.0
 * @author greghacke
 * Function: Customize territory list table columns
 */

defined('ABSPATH') || exit;

/**
 * Define custom columns.
 */
add_filter('manage_owbn_territory_posts_columns', function ($columns) {
    return [
        'cb'    => $columns['cb'],
        'title' => __('Title', 'owbn-territory-manager'),
        'slugs' => __('Slug(s)', 'owbn-territory-manager'),
        'date'  => __('Date', 'owbn-territory-manager'),
    ];
});

/**
 * Render custom column content.
 */
add_action('manage_owbn_territory_posts_custom_column', function ($column, $post_id) {
    if ($column === 'slugs') {
        $slugs = get_post_meta($post_id, '_owbn_tm_slug', true);
        if (!empty($slugs) && is_array($slugs)) {
            echo esc_html(implode(', ', $slugs));
        } else {
            echo '—';
        }
    }
}, 10, 2);

/**
 * Make columns sortable.
 */
add_filter('manage_edit-owbn_territory_sortable_columns', function ($columns) {
    $columns['title'] = 'title';
    $columns['slugs'] = 'slugs';
    $columns['date'] = 'date';
    return $columns;
});

/**
 * Handle sorting by slug.
 */
add_action('pre_get_posts', function ($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->get('post_type') !== 'owbn_territory') {
        return;
    }

    // Handle slug sorting
    if ($query->get('orderby') === 'slugs') {
        $query->set('meta_key', '_owbn_tm_slug');
        $query->set('orderby', 'meta_value');
    }
});

/**
 * Extend search to include slug meta field.
 */
add_action('pre_get_posts', function ($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->get('post_type') !== 'owbn_territory') {
        return;
    }

    $search = $query->get('s');
    if (empty($search)) {
        return;
    }

    // Store search term and clear it (we'll handle it manually)
    $query->set('_owbn_tm_search', $search);
    $query->set('s', '');
});

/**
 * Modify WHERE clause to search title and slug.
 */
add_filter('posts_where', function ($where, $query) {
    if (!is_admin() || !$query->is_main_query()) {
        return $where;
    }

    if ($query->get('post_type') !== 'owbn_territory') {
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
}, 10, 2);
