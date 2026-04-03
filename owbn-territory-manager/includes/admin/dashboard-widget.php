<?php
defined('ABSPATH') || exit;

add_action('wp_dashboard_setup', 'owbn_tm_register_dashboard_widget');

function owbn_tm_register_dashboard_widget() {
    if ( ! owbn_tm_dashboard_widget_visible() ) {
        return;
    }

    wp_add_dashboard_widget(
        'owbn_tm_dashboard',
        __( 'Territory Manager', 'owbn-territory-manager' ),
        'owbn_tm_render_dashboard_widget'
    );
}

/**
 * Check if the current user should see the widget.
 * WP admin or exec/(head-coordinator|ahc1|ahc2|admin|membership|web|archivist).
 */
function owbn_tm_dashboard_widget_visible() {
    if ( current_user_can( 'manage_options' ) ) {
        return true;
    }

    if ( ! function_exists( 'owc_asc_get_user_roles' ) ) {
        return false;
    }

    $user  = wp_get_current_user();
    $roles = owc_asc_get_user_roles( 'territory', $user->user_email );
    if ( isset( $roles['roles'] ) ) {
        $roles = $roles['roles'];
    }
    if ( ! is_array( $roles ) ) {
        return false;
    }

    foreach ( $roles as $role ) {
        if ( preg_match( '#^exec/(head-coordinator|ahc1|ahc2|admin|membership|web|archivist)/coordinator$#i', $role ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Render the dashboard widget content.
 */
function owbn_tm_render_dashboard_widget() {
    $all_url    = admin_url( 'edit.php?post_type=owbn_territory' );
    $import_url = admin_url( 'admin.php?page=owbn-territory-import' );

    echo '<div style="display:flex;gap:8px;margin-bottom:12px;">';
    echo '<a href="' . esc_url( $all_url ) . '" class="button">' . esc_html__( 'All Territories', 'owbn-territory-manager' ) . '</a>';
    echo '<a href="' . esc_url( $import_url ) . '" class="button">' . esc_html__( 'Import / Export', 'owbn-territory-manager' ) . '</a>';
    echo '</div>';

    // Last 5 updated territories by WP modified date (reliable sort).
    $posts = get_posts( [
        'post_type'      => 'owbn_territory',
        'post_status'    => 'publish',
        'posts_per_page' => 5,
        'orderby'        => 'modified',
        'order'          => 'DESC',
    ] );

    if ( empty( $posts ) ) {
        echo '<p style="color:#888;">' . esc_html__( 'No territories found.', 'owbn-territory-manager' ) . '</p>';
        return;
    }

    echo '<table class="widefat striped" style="margin-top:4px;">';
    echo '<thead><tr>';
    echo '<th>' . esc_html__( 'Territory', 'owbn-territory-manager' ) . '</th>';
    echo '<th>' . esc_html__( 'Owner', 'owbn-territory-manager' ) . '</th>';
    echo '<th>' . esc_html__( 'Updated', 'owbn-territory-manager' ) . '</th>';
    echo '</tr></thead><tbody>';

    foreach ( $posts as $post ) {
        $owner       = get_post_meta( $post->ID, '_owbn_tm_owner', true ) ?: '—';
        $update_date = get_the_modified_date( 'Y-m-d', $post );
        $edit_url    = get_edit_post_link( $post->ID );

        echo '<tr>';
        echo '<td><a href="' . esc_url( $edit_url ) . '">' . esc_html( $post->post_title ) . '</a></td>';
        echo '<td>' . esc_html( $owner ) . '</td>';
        echo '<td>' . esc_html( $update_date ) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}
