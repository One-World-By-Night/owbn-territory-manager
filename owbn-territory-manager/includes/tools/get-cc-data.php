<?php
defined('ABSPATH') || exit;

function owbn_tm_get_chronicles() {
    if (function_exists('owc_get_chronicles')) {
        return owc_get_chronicles();
    }
    return [];
}

function owbn_tm_get_coordinators() {
    if (function_exists('owc_get_coordinators')) {
        return owc_get_coordinators();
    }
    return [];
}

function owbn_tm_refresh_cc_cache() {
    if (function_exists('owc_refresh_all_caches')) {
        return owc_refresh_all_caches();
    }
    return true;
}

/** @return array ['chronicle/{slug}' => 'Label (Chronicle)', 'coordinator/{slug}' => 'Label (Coordinator)'] */
function owbn_tm_get_all_slugs(): array {
    $slugs = [];

    $chronicles = owbn_tm_get_chronicles();
    if (!is_wp_error($chronicles) && is_array($chronicles)) {
        foreach ($chronicles as $c) {
            $slug = $c['slug'] ?? '';
            $name = $c['title'] ?? $c['name'] ?? $slug;
            if ($slug) {
                $key = 'chronicle/' . $slug;
                $slugs[$key] = $key . ' – ' . $name . ' (Chronicle)';
            }
        }
    }

    $coordinators = owbn_tm_get_coordinators();
    if (!is_wp_error($coordinators) && is_array($coordinators)) {
        foreach ($coordinators as $c) {
            $slug = $c['slug'] ?? '';
            $name = $c['title'] ?? $c['name'] ?? $slug;
            if ($slug) {
                $key = 'coordinator/' . $slug;
                $slugs[$key] = $key . ' – ' . $name . ' (Coordinator)';
            }
        }
    }

    asort($slugs);
    return $slugs;
}
