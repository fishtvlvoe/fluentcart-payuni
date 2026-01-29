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

if (!function_exists('site_url')) {
    function site_url($path = '', $scheme = null)
    {
        $url = 'http://example.com';
        if ($path && is_string($path)) {
            $url .= '/' . ltrim($path, '/');
        }
        return $url;
    }
}

if (!function_exists('add_query_arg')) {
    function add_query_arg($args, $url = '')
    {
        if (empty($url)) {
            $url = 'http://example.com';
        }

        $parsed = parse_url($url);
        $query = isset($parsed['query']) ? $parsed['query'] : '';

        parse_str($query, $queryParams);

        if (is_array($args)) {
            $queryParams = array_merge($queryParams, $args);
        }

        $newQuery = http_build_query($queryParams);

        $scheme = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : '';
        $host = isset($parsed['host']) ? $parsed['host'] : '';
        $path = isset($parsed['path']) ? $parsed['path'] : '';
        $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';

        return $scheme . $host . $path . ($newQuery ? '?' . $newQuery : '') . $fragment;
    }
}

