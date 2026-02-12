<?php

/**
 * Plugin Name: OWBN Territory Manager
 * Description: Unified listing of chronicle and coordinator territories with slug associations. Provides admin management, public display, and REST API access.
 * Version: 1.2.0
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

// ─── Plugin Constants ────────────────────────────────────────────────────────
define('OWBN_TM_VERSION', '1.2.0');
define('OWBN_TM_PLUGIN_FILE', __FILE__);
define('OWBN_TM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OWBN_TM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OWBN_TM_PLUGIN_BASENAME', plugin_basename(__FILE__));

// ─── Bootstrap ───────────────────────────────────────────────────────────────
require_once OWBN_TM_PLUGIN_DIR . 'includes/init.php';
