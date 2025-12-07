<?php

/** File: utils/init.php
 * Text Domain: owbn-territory-manager
 * Version: 0.9.0
 * @author greghacke
 * Function: Load utility files
 */

defined('ABSPATH') || exit;

require_once __DIR__ . '/import-bulk.php';
require_once __DIR__ . '/export-bulk.php';
require_once __DIR__ . '/import-record.php';
require_once __DIR__ . '/export-record.php';
