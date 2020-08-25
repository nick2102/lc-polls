<?php
/*
    Plugin Name: LC Polls
    Version: 1.0.1
    Plugin URI: https://labscreative.com
    Author: Nikola Nikoloski
    Author URI: https://labscreative.com
    Text Domain: lcpolls
    Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit; // don't access directly
}

/**
 * define plugin constants
 */

define('LCPOLLS_PLUGIN_DIR', str_replace(['\\\\', '\\'], '/', plugin_dir_path(__FILE__)));
define('LCPOLLS_PLUGIN_RELATIVE_DIR', dirname(plugin_basename(__FILE__)));
define('LCPOLLS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LCPOLLS_PLUGIN_FILE', str_replace(['\\\\', '\\'], '/', __FILE__));
define('LCPOLLS_PLUGIN_API_NAMESPACE', 'lcpolls-api/v1');


/**
 * Set Language direcotory
 */
load_plugin_textdomain('lcpolls', false, 'lcpolls/languages/');


/**
 * Get instance of main Class
 */
require_once(LCPOLLS_PLUGIN_DIR . 'include/class-lcpolls.php');
LcPolls::getInstance();
