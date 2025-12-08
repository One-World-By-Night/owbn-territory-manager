<?php

/** File: utils/import-bulk.php
 * Text Domain: owbn-territory-manager
 * Version: 1.0.0
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

    // Direct mappings for exact matches and common variations
    $direct_map = [
        // Special codes
        'Worldwide'      => ['WW'],
        'World'          => ['WW'],
        'Global'         => ['WW'],

        // Fictional/supernatural locations -> ZZ
        'Aetherial Realm' => ['ZZ'],
        'Asia'            => ['ZZ'],
        'Bistritz'        => ['ZZ'],
        'Colonia'         => ['ZZ'],
        'Dreaming'        => ['ZZ'],
        'Lemuria'         => ['ZZ'],
        'Shadowlands'     => ['ZZ'],
        'Southeast Asia'  => ['ZZ'],
        'Stygia'          => ['ZZ'],
        'Umbra'           => ['ZZ'],
        'VM'              => ['ZZ'],

        // US variations
        'Florida'         => ['US'],
        'Palisades-Kepler State Park' => ['US'],
        'US'              => ['US'],
        'USA'             => ['US'],
        'United States'   => ['US'],
        'U.S.A.'          => ['US'],

        // UK variations
        'England'         => ['GB'],
        'Scotland'        => ['GB'],
        'UK'              => ['GB'],
        'United Kingdom'  => ['GB'],
        'Wales'           => ['GB'],

        // Standard country names
        'Afghanistan'          => ['AF'],
        'Argentina'            => ['AR'],
        'Australia'            => ['AU'],
        'Austria'              => ['AT'],
        'Brazil'               => ['BR'],
        'Canada'               => ['CA'],
        'Cayman Islands'       => ['KY'],
        'Chile'                => ['CL'],
        'China'                => ['CN'],
        'Colombia'             => ['CO'],
        'Commonwealth of The Bahamas (a.k.a. the Bahamas)' => ['BS'],
        'Croatia'              => ['HR'],
        'Cuba'                 => ['CU'],
        'Czech Republic'       => ['CZ'],
        'Denmark'              => ['DK'],
        'Dominican Republic'   => ['DO'],
        'Ecuador'              => ['EC'],
        'Egypt'                => ['EG'],
        'Finland'              => ['FI'],
        'France'               => ['FR'],
        'Germany'              => ['DE'],
        'Greece'               => ['GR'],
        'Haiti'                => ['HT'],
        'Hungary'              => ['HU'],
        'India'                => ['IN'],
        'Indonesia'            => ['ID'],
        'Iran'                 => ['IR'],
        'Iraq'                 => ['IQ'],
        'Ireland'              => ['IE'],
        'Israel'               => ['IL'],
        'Italy'                => ['IT'],
        'Jamaica'              => ['JM'],
        'Japan'                => ['JP'],
        'Kazakhstan'           => ['KZ'],
        'Lebanon'              => ['LB'],
        'Macedonia'            => ['MK'],
        'Mexico'               => ['MX'],
        'Mongolia'             => ['MN'],
        'Morocco'              => ['MA'],
        'Netherlands'          => ['NL'],
        'Nigeria'              => ['NG'],
        'North Korea'          => ['KP'],
        'Norway'               => ['NO'],
        'Oman'                 => ['OM'],
        'Pakistan'             => ['PK'],
        'Panama'               => ['PA'],
        'Paraguay'             => ['PY'],
        'Peru'                 => ['PE'],
        'Poland'               => ['PL'],
        'Portugal'             => ['PT'],
        'Romania'              => ['RO'],
        'Russia'               => ['RU'],
        'Saudi Arabia'         => ['SA'],
        'Singapore'            => ['SG'],
        'South Africa'         => ['ZA'],
        'South Korea'          => ['KR'],
        'Spain'                => ['ES'],
        'Sri Lanka'            => ['LK'],
        'Sweden'               => ['SE'],
        'Switzerland'          => ['CH'],
        'Syria'                => ['SY'],
        'Taiwan'               => ['TW'],
        'The Bahamas'          => ['BS'],
        'The Bermudas or Somers Isles' => ['BM'],
        'Tunisia'              => ['TN'],
        'Turk and Calicos Islands' => ['TC'],
        'Turkey'               => ['TR'],
        'Uganda'               => ['UG'],
        'Ukraine'              => ['UA'],
        'United Arab Emirates' => ['AE'],
        'Uruguay'              => ['UY'],
        'Vatican City'         => ['VA'],
    ];

    // Check direct map first (case-sensitive)
    if (isset($direct_map[$country])) {
        return $direct_map[$country];
    }

    // Check for multi-country (border territories)
    if (preg_match('/^(.+?)\s*[&]\s*(.+?)\s+Border$/i', $country, $matches)) {
        $codes = [];
        $codes = array_merge($codes, owbn_tm_map_country_to_iso(trim($matches[1])));
        $codes = array_merge($codes, owbn_tm_map_country_to_iso(trim($matches[2])));
        return array_unique($codes);
    }

    // Check for contains patterns
    $contains_map = [
        'Canada'   => ['CA'],
        'France'   => ['FR'],
        'Jamaica'  => ['JM'],
        'Arizona'  => ['US'],
        'Iowa'     => ['US'],
        'Lemuria'  => ['ZZ'],
    ];

    foreach ($contains_map as $needle => $codes) {
        if (stripos($country, $needle) !== false) {
            return $codes;
        }
    }

    // Reverse lookup from country list (case-insensitive)
    $countries = owbn_tm_get_country_list();
    $country_lower = strtolower($country);

    foreach ($countries as $code => $name) {
        if (strtolower($name) === $country_lower) {
            return [$code];
        }
    }

    // Default to custom/unknown
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
        'warnings' => [],
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
        $row_issues = [];

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
            $results['warnings'][] = sprintf(
                __('Row %d: Skipped - no country, region, or location data', 'owbn-territory-manager'),
                $row_num
            );
            continue;
        }

        // Map country to ISO codes (default to US if empty)
        if (empty($country)) {
            $country_codes = ['US'];
        } else {
            $country_codes = owbn_tm_map_country_to_iso($country);

            // Check for unknown country mapping
            if ($country_codes === ['ZZ']) {
                $row_issues[] = sprintf(
                    __('Unknown country "%s" mapped to Custom/Other (ZZ)', 'owbn-territory-manager'),
                    $country
                );
            }
        }

        // Generate title: Country - Region, Location
        // For ZZ (fictional/custom), use original country name to preserve it
        if ($country_codes === ['ZZ'] && !empty($country)) {
            $title = $country;
        } else {
            $title = owbn_tm_format_countries($country_codes);
        }

        if (!empty($region)) {
            $title .= ' - ' . $region;
        }
        if (!empty($location)) {
            $title .= ', ' . $location;
        }
        if (empty($title)) {
            $title = "Territory Row {$row_num}";
        }

        // Handle slug (could be comma-separated)
        $slugs = [];
        if (!empty($slug)) {
            $slugs = array_filter(array_map('trim', explode(',', $slug)));
        }

        // Log row issues as warnings
        if (!empty($row_issues)) {
            $results['warnings'][] = sprintf(
                __('Row %d: %s', 'owbn-territory-manager'),
                $row_num,
                implode('; ', $row_issues)
            );
        }

        // Dry run - don't insert, just validate
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
                __('Row %d: Failed to create post - %s', 'owbn-territory-manager'),
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

    // Read entire file and remove BOM
    $content = file_get_contents($file_path);
    if ($content === false) {
        return $rows;
    }

    // Remove UTF-8 BOM if present
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

    // Normalize line endings
    $content = str_replace("\r\n", "\n", $content);
    $content = str_replace("\r", "\n", $content);

    // Write to temp file for fgetcsv
    $temp = tmpfile();
    if ($temp === false) {
        return $rows;
    }

    fwrite($temp, $content);
    rewind($temp);

    $headers = fgetcsv($temp);
    if (!$headers) {
        fclose($temp);
        return $rows;
    }

    // Normalize headers
    $headers = array_map('trim', $headers);

    while (($data = fgetcsv($temp)) !== false) {
        if (count($data) === count($headers)) {
            $rows[] = array_combine($headers, $data);
        }
    }

    fclose($temp);
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
            <?php
            $has_errors   = !empty($results['errors']);
            $has_warnings = !empty($results['warnings']);
            $notice_class = $has_errors ? 'error' : ($has_warnings ? 'warning' : 'success');
            ?>
            <div class="notice notice-<?php echo $notice_class; ?>">
                <p>
                    <?php if ($results['dry_run']) : ?>
                        <strong><?php esc_html_e('Dry Run Results:', 'owbn-territory-manager'); ?></strong><br>
                    <?php else : ?>
                        <strong><?php esc_html_e('Import Complete:', 'owbn-territory-manager'); ?></strong><br>
                    <?php endif; ?>
                    <?php printf(
                        esc_html__('Total: %d | Imported: %d | Skipped: %d | Errors: %d | Warnings: %d', 'owbn-territory-manager'),
                        $results['total'],
                        $results['imported'],
                        $results['skipped'],
                        count($results['errors']),
                        count($results['warnings'])
                    ); ?>
                </p>
            </div>

            <?php if ($has_errors) : ?>
                <div class="notice notice-error">
                    <details open>
                        <summary><strong><?php esc_html_e('Errors (import failed for these rows)', 'owbn-territory-manager'); ?></strong></summary>
                        <ul style="margin-left:20px;">
                            <?php foreach ($results['errors'] as $error) : ?>
                                <li><?php echo esc_html($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </details>
                </div>
            <?php endif; ?>

            <?php if ($has_warnings) : ?>
                <div class="notice notice-warning">
                    <details>
                        <summary><strong><?php esc_html_e('Warnings (imported with issues)', 'owbn-territory-manager'); ?></strong></summary>
                        <ul style="margin-left:20px;">
                            <?php foreach ($results['warnings'] as $warning) : ?>
                                <li><?php echo esc_html($warning); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </details>
                </div>
            <?php endif; ?>

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
                            <input type="checkbox" name="dry_run" value="1" checked />
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
