<?php

/**
 * Plugin Name: OWBN Territory Manager
 * Description: Manage chronicle and coordinator territory assignments with admin tools and bulk import.
 * Version: 1.6.2
 * Author: greghacke
 * Author URI: https://www.owbn.net
 * Text Domain: owbn-territory-manager
 * Domain Path: /languages
 * License: GPL-2.0-or-later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * GitHub Plugin URI: https://github.com/One-World-By-Night/owbn-territory-manager
 * GitHub Branch: main
 */

defined('ABSPATH') || exit;

define('OWBN_TM_VERSION', '1.6.2');
define('OWBN_TM_PLUGIN_FILE', __FILE__);
define('OWBN_TM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OWBN_TM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OWBN_TM_PLUGIN_BASENAME', plugin_basename(__FILE__));

require_once OWBN_TM_PLUGIN_DIR . 'includes/init.php';

add_filter('owbn_gateway_data_sources', function ($sources) {
    $sources['territory'] = [
        'label'    => 'Territories',
        'provider' => 'owbn-territory-manager',
        'types'    => ['territory'],
    ];
    return $sources;
});
