<?php
/**
 * Bootstrap for unit tests (no WordPress required)
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$composer_autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (!file_exists($composer_autoload)) {
    die('Unable to find Composer autoloader: ' . $composer_autoload);
}

require_once $composer_autoload;

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}

if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

if (!defined('FluentcartPayuni_PLUGIN_DIR')) {
    define('FluentcartPayuni_PLUGIN_DIR', dirname(__DIR__) . '/');
}

// WordPress function stubs for unit tests
if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, $options = 0, $depth = 512)
    {
        return json_encode($data, $options, $depth);
    }
}

