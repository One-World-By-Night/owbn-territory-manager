<?php

/** File: utils/import-bulk.php
 * Text Domain: owbn-territory-manager
 * Version: 1.1.0
 * @author greghacke
 * Function: Bulk import territories from CSV or JSON
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
        'Lithuania'            => ['LT'],
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
        'Philippines'          => ['PH'],
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

    // Default to configured unknown country code
    return [get_option('owbn_tm_import_unknown_country', 'ZZ')];
}

/**
 * Extract normalized fields from a row (CSV or JSON).
 *
 * @param array $row Raw row data
 * @return array Normalized fields
 */
function owbn_tm_extract_row_fields(array $row): array
{
    // CSV column names
    $country     = $row['country'] ?? $row['Country'] ?? '';
    $region      = $row['region'] ?? $row['Region'] ?? '';
    $location    = $row['location'] ?? $row['Location'] ?? '';
    $detail      = $row['detail'] ?? $row['Detail'] ?? '';
    $description = $row['description'] ?? $row['Description'] ?? '';
    $owner       = $row['owner'] ?? $row['Owner'] ?? '';
    $slug        = $row['slug'] ?? $row['Slug'] ?? '';
    $update_date = $row['update_date'] ?? $row['UpdateDate'] ?? '';
    $update_user = $row['update_user'] ?? $row['UpdateUser'] ?? '';

    // JSON column names (Drupal export format)
    if (empty($region) && isset($row['State/Province'])) {
        $region = $row['State/Province'];
    }
    if (empty($location) && isset($row['County'])) {
        $location = $row['County'];
    }
    if (empty($detail) && isset($row['City'])) {
        $detail = $row['City'];
    }
    if (empty($description) && isset($row['Notes'])) {
        $description = $row['Notes'];
    }
    if (empty($owner) && isset($row['Controlled By'])) {
        $owner = $row['Controlled By'];
    }
    if (empty($update_date) && isset($row['Last Updated'])) {
        $update_date = $row['Last Updated'];
    }
    if (empty($update_user) && isset($row['Updated By'])) {
        $update_user = $row['Updated By'];
    }

    return [
        'country'     => trim($country),
        'region'      => trim($region),
        'location'    => trim($location),
        'detail'      => trim($detail),
        'description' => trim($description),
        'owner'       => trim($owner),
        'slug'        => trim($slug),
        'update_date' => trim($update_date),
        'update_user' => trim($update_user),
    ];
}

/**
 * Process CSV or JSON import.
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

    if (!in_array($ext, ['csv', 'json'], true)) {
        $results['errors'][] = sprintf(
            __('Unsupported file type: %s. Use CSV or JSON.', 'owbn-territory-manager'),
            $ext ?: 'unknown'
        );
        return $results;
    }

    // Parse file based on type
    $rows = ($ext === 'json')
        ? owbn_tm_parse_json($file_path)
        : owbn_tm_parse_csv($file_path);

    if (empty($rows)) {
        $results['errors'][] = __('No data found in file.', 'owbn-territory-manager');
        return $results;
    }

    $results['total'] = count($rows);

    foreach ($rows as $index => $row) {
        $row_num = $index + 1;
        $row_issues = [];

        // Extract and normalize fields
        $fields = owbn_tm_extract_row_fields($row);

        $country     = $fields['country'];
        $region      = $fields['region'];
        $location    = $fields['location'];
        $detail      = $fields['detail'];
        $description = $fields['description'];
        $owner       = $fields['owner'];
        $slug        = $fields['slug'];
        $update_date = $fields['update_date'];
        $update_user = $fields['update_user'];

        // Skip empty rows
        if (empty($country) && empty($region) && empty($location)) {
            $results['skipped']++;
            $results['warnings'][] = sprintf(
                __('Row %d: Skipped - no country, region, or location data', 'owbn-territory-manager'),
                $row_num
            );
            continue;
        }

        // Map country to ISO codes (default to configured default if empty)
        if (empty($country)) {
            $country_codes = [get_option('owbn_tm_import_default_country', 'US')];
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
        if (!empty($detail)) {
            $title .= ' - ' . $detail;
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
        update_post_meta($post_id, '_owbn_tm_update_date', sanitize_text_field($update_date));
        update_post_meta($post_id, '_owbn_tm_update_user', sanitize_text_field($update_user));

        $results['imported']++;
    }

    return $results;
}

/**
 * Parse JSON file (Drupal export format).
 *
 * Expected format: {"nodes":[{"node":{...}},{"node":{...}}]}
 *
 * @param string $file_path Path to JSON file
 * @return array Rows
 */
function owbn_tm_parse_json(string $file_path): array
{
    $rows = [];

    $content = file_get_contents($file_path);
    if ($content === false) {
        return $rows;
    }

    // Remove UTF-8 BOM if present
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

    $data = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        return $rows;
    }

    // Handle {"nodes":[{"node":{...}}]} format
    if (isset($data['nodes']) && is_array($data['nodes'])) {
        foreach ($data['nodes'] as $item) {
            if (isset($item['node']) && is_array($item['node'])) {
                $rows[] = $item['node'];
            }
        }
    }

    return $rows;
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
                        <input type="file" name="import_file" id="import_file" accept=".csv,.json" required />
                        <p class="description">
                            <?php esc_html_e('CSV columns: Country, Region, Location, Detail, Description, Owner, Slug, UpdateDate, UpdateUser', 'owbn-territory-manager'); ?><br>
                            <?php esc_html_e('JSON format: {"nodes":[{"node":{...}}]} with Drupal export fields', 'owbn-territory-manager'); ?>
                        </p>
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

        <h2><?php esc_html_e('Expected Formats', 'owbn-territory-manager'); ?></h2>

        <h3><?php esc_html_e('CSV Format', 'owbn-territory-manager'); ?></h3>
        <table class="widefat" style="max-width:900px;">
            <thead>
                <tr>
                    <th>Country</th>
                    <th>Region</th>
                    <th>Location</th>
                    <th>Detail</th>
                    <th>Description</th>
                    <th>Owner</th>
                    <th>Slug</th>
                    <th>UpdateDate</th>
                    <th>UpdateUser</th>
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
                    <td>2024-01-15</td>
                    <td>admin</td>
                </tr>
            </tbody>
        </table>

        <h3><?php esc_html_e('JSON Format (Drupal Export)', 'owbn-territory-manager'); ?></h3>
        <table class="widefat" style="max-width:600px;">
            <thead>
                <tr>
                    <th><?php esc_html_e('JSON Field', 'owbn-territory-manager'); ?></th>
                    <th><?php esc_html_e('Maps To', 'owbn-territory-manager'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Country</td>
                    <td>Country</td>
                </tr>
                <tr>
                    <td>State/Province</td>
                    <td>Region</td>
                </tr>
                <tr>
                    <td>County</td>
                    <td>Location</td>
                </tr>
                <tr>
                    <td>City</td>
                    <td>Detail</td>
                </tr>
                <tr>
                    <td>Notes</td>
                    <td>Description</td>
                </tr>
                <tr>
                    <td>Controlled By</td>
                    <td>Owner</td>
                </tr>
                <tr>
                    <td>Last Updated</td>
                    <td>UpdateDate</td>
                </tr>
                <tr>
                    <td>Updated By</td>
                    <td>UpdateUser</td>
                </tr>
            </tbody>
        </table>
    </div>
<?php
}
