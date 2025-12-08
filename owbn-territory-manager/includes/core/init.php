<?php

/** File: core/init.php
 * Text Domain: owbn-territory-manager
 * Version: 1.0.0
 * @author greghacke
 * Function: Load core components
 */

defined('ABSPATH') || exit;

require_once __DIR__ . '/post-type.php';
require_once __DIR__ . '/activation.php';
require_once __DIR__ . '/cache.php';
require_once __DIR__ . '/logging.php';
