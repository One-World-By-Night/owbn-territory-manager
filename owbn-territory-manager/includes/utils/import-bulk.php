<?php

/** File: utils/import-bulk.php
 * Text Domain: owbn-territory-manager
 * Version: 0.9.0
 * @author greghacke
 * Function: Bulk import territories from CSV
 */

defined('ABSPATH') || exit;

/**
 * Map country name to ISO code(s).
 */
function owbn_tm_map_country_to_iso(string $country): array
{
    $country = trim($country);

    if (empty($country)) {
        return [];
    }

    // Direct mappings for common variations
    $direct_map = [
        'USA'            => ['US'],
        'United States'  => ['US'],
        'U.S.A.'         => ['US'],
        'UK'             => ['GB'],
        'United Kingdom' => ['GB'],
        'England'        => ['GB'],
        'Scotland'       => ['GB'],
        'Wales'          => ['GB'],
        'Worldwide'      => ['WW'],
        'World'          => ['WW'],
        'Global'         => ['WW'],
        'Vatican City'   => ['VA'],
        'Russia'         => ['RU'],
        'South Korea'    => ['KR'],
        'North Korea'    => ['KP'],
        'Taiwan'         => ['TW'],
        'Czech Republic' => ['CZ'],
        'The Bahamas'    => ['BS'],
        'Commonwealth of The Bahamas (a.k.a. the Bahamas)' => ['BS'],
    ];

    // Check direct map first
    if (isset($direct_map[$country])) {
        return $direct_map[$country];
    }

    // Check for multi-country (border territories)
    $border_patterns = [
        '/^(.+?)\s*[&]\s*(.+?)\s+Border$/i',
        '/^(.+?)\s*[\/]\s*(.+?)$/i',
    ];

    foreach ($border_patterns as $pattern) {
        if (preg_match($pattern, $country, $matches)) {
            $codes = [];
            $codes = array_merge($codes, owbn_tm_map_country_to_iso(trim($matches[1])));
            $codes = array_merge($codes, owbn_tm_map_country_to_iso(trim($matches[2])));
            return array_unique($codes);
        }
    }

    // Reverse lookup from country list
    $countries = owbn_tm_get_country_list();
    $country_lower = strtolower($country);

    foreach ($countries as $code => $name) {
        if (strtolower($name) === $country_lower) {
            return [$code];
        }
    }

    // Check if country contains a known country name (e.g., "BC, Canada")
    foreach ($countries as $code => $name) {
        if (stripos($country, $name) !== false) {
            return [$code];
        }
    }

    // Fictional/special locations
    $fictional = [
        'Aetherial Realm',
        'Dreaming',
        'Umbra',
        'Shadowlands',
        'Bistritz',
        'Colonia',
        'Lemuria',
        'VM',
    ];

    foreach ($fictional as $f) {
        if (stripos($country, $f) !== false) {
            return ['ZZ'];
        }
    }

    // If it looks like a US state or location with state
    $us_states = ['Florida', 'Arizona', 'California', 'Texas', 'New York', 'Ohio', 'Pennsylvania'];
    foreach ($us_states as $state) {
        if (stripos($country, $state) !== false) {
            return ['US'];
        }
    }

    // Default to custom
    return ['ZZ'];
}

/**
 * Process CSV import.
 *
 * @param string $file_path     Path to uploaded temp file
 * @param string $original_name Original filename for extension detection
 * @param bool   $dry_run       Validate without importing
 * @return array Results
 */
function owbn_tm_process_import(string $file_path, string $original_name, bool $dry_run = false): array
{
    $results = [
        'total'    => 0,
        'imported' => 0,
        'skipped'  => 0,
        'errors'   => [],
        'dry_run'  => $dry_run,
    ];

    // Determine file type from original filename
    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    if ($ext !== 'csv') {
        $results['errors'][] = sprintf(
            __('Unsupported file type: %s. Use CSV.', 'owbn-territory-manager'),
            $ext ?: 'unknown'
        );
        return $results;
    }

    $rows = owbn_tm_parse_csv($file_path);

    if (empty($rows)) {
        $results['errors'][] = __('No data found in file.', 'owbn-territory-manager');
        return $results;
    }

    $results['total'] = count($rows);

    foreach ($rows as $index => $row) {
        $row_num = $index + 2; // Account for header row

        // Extract fields (case-insensitive column matching)
        $country     = $row['country'] ?? $row['Country'] ?? '';
        $region      = $row['region'] ?? $row['Region'] ?? '';
        $location    = $row['location'] ?? $row['Location'] ?? '';
        $detail      = $row['detail'] ?? $row['Detail'] ?? '';
        $description = $row['description'] ?? $row['Description'] ?? '';
        $owner       = $row['owner'] ?? $row['Owner'] ?? '';
        $slug        = $row['slug'] ?? $row['Slug'] ?? '';

        // Skip empty rows
        if (empty($country) && empty($region) && empty($location)) {
            $results['skipped']++;
            continue;
        }

        // Map country to ISO codes
        $country_codes = owbn_tm_map_country_to_iso($country);

        // Generate title
        $title_parts = array_filter([
            owbn_tm_format_countries($country_codes),
            $region,
            $location,
        ]);
        $title = !empty($title_parts) ? implode(' > ', $title_parts) : "Territory Row {$row_num}";

        // Handle slug (could be comma-separated)
        $slugs = [];
        if (!empty($slug)) {
            $slugs = array_filter(array_map('trim', explode(',', $slug)));
        }

        if ($dry_run) {
            $results['imported']++;
            continue;
        }

        // Create post
        $post_id = wp_insert_post([
            'post_type'    => 'owbn_territory',
            'post_status'  => 'publish',
            'post_title'   => sanitize_text_field($title),
            'post_content' => wp_kses_post($description),
        ], true);

        if (is_wp_error($post_id)) {
            $results['errors'][] = sprintf(
                __('Row %d: %s', 'owbn-territory-manager'),
                $row_num,
                $post_id->get_error_message()
            );
            continue;
        }

        // Save meta
        update_post_meta($post_id, '_owbn_tm_countries', $country_codes);
        update_post_meta($post_id, '_owbn_tm_region', sanitize_text_field($region));
        update_post_meta($post_id, '_owbn_tm_location', sanitize_text_field($location));
        update_post_meta($post_id, '_owbn_tm_detail', sanitize_text_field($detail));
        update_post_meta($post_id, '_owbn_tm_owner', sanitize_text_field($owner));
        update_post_meta($post_id, '_owbn_tm_slug', $slugs);

        $results['imported']++;
    }

    return $results;
}

/**
 * Parse CSV file.
 */
function owbn_tm_parse_csv(string $file_path): array
{
    $rows = [];

    if (($handle = fopen($file_path, 'r')) === false) {
        return $rows;
    }

    $headers = fgetcsv($handle);
    if (!$headers) {
        fclose($handle);
        return $rows;
    }

    // Normalize headers
    $headers = array_map('trim', $headers);

    while (($data = fgetcsv($handle)) !== false) {
        if (count($data) === count($headers)) {
            $rows[] = array_combine($headers, $data);
        }
    }

    fclose($handle);
    return $rows;
}

/**
 * Render import page.
 */
function owbn_tm_render_import_page()
{
    $results = null;

    // Handle form submission
    if (isset($_POST['owbn_tm_import']) && check_admin_referer('owbn_tm_import_nonce')) {
        if (!empty($_FILES['import_file']['tmp_name']) && !empty($_FILES['import_file']['name'])) {
            $dry_run = isset($_POST['dry_run']);
            $original_name = sanitize_file_name($_FILES['import_file']['name']);
            $results = owbn_tm_process_import($_FILES['import_file']['tmp_name'], $original_name, $dry_run);
        }
    }

?>
    <div class="wrap">
        <h1><?php esc_html_e('Import Territories', 'owbn-territory-manager'); ?></h1>

        <?php if ($results) : ?>
            <div class="notice notice-<?php echo empty($results['errors']) ? 'success' : 'warning'; ?>">
                <p>
                    <?php if ($results['dry_run']) : ?>
                        <strong><?php esc_html_e('Dry Run Results:', 'owbn-territory-manager'); ?></strong><br>
                    <?php else : ?>
                        <strong><?php esc_html_e('Import Complete:', 'owbn-territory-manager'); ?></strong><br>
                    <?php endif; ?>
                    <?php printf(
                        esc_html__('Total: %d | Imported: %d | Skipped: %d | Errors: %d', 'owbn-territory-manager'),
                        $results['total'],
                        $results['imported'],
                        $results['skipped'],
                        count($results['errors'])
                    ); ?>
                </p>
                <?php if (!empty($results['errors'])) : ?>
                    <details>
                        <summary><?php esc_html_e('View Errors', 'owbn-territory-manager'); ?></summary>
                        <ul>
                            <?php foreach ($results['errors'] as $error) : ?>
                                <li><?php echo esc_html($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </details>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('owbn_tm_import_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="import_file"><?php esc_html_e('File', 'owbn-territory-manager'); ?></label>
                    </th>
                    <td>
                        <input type="file" name="import_file" id="import_file" accept=".csv" required />
                        <p class="description"><?php esc_html_e('CSV file with columns: Country, Region, Location, Detail, Description, Owner, Slug', 'owbn-territory-manager'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Options', 'owbn-territory-manager'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="dry_run" value="1" />
                            <?php esc_html_e('Dry run (validate without importing)', 'owbn-territory-manager'); ?>
                        </label>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" name="owbn_tm_import" class="button button-primary">
                    <?php esc_html_e('Import Territories', 'owbn-territory-manager'); ?>
                </button>
            </p>
        </form>

        <hr>

        <h2><?php esc_html_e('Expected Format', 'owbn-territory-manager'); ?></h2>
        <table class="widefat" style="max-width:800px;">
            <thead>
                <tr>
                    <th>Country</th>
                    <th>Region</th>
                    <th>Location</th>
                    <th>Detail</th>
                    <th>Description</th>
                    <th>Owner</th>
                    <th>Slug</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>USA</td>
                    <td>Wisconsin</td>
                    <td>Wood County</td>
                    <td></td>
                    <td>Shared territory...</td>
                    <td>Green Bay, WI</td>
                    <td>green-bay-wi</td>
                </tr>
                <tr>
                    <td>Worldwide</td>
                    <td>Coord Level</td>
                    <td>NPC Chantries</td>
                    <td></td>
                    <td>Geographical...</td>
                    <td>Tremere</td>
                    <td>tremere</td>
                </tr>
            </tbody>
        </table>
    </div>
<?php
}
