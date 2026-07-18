<?php

/**
 * Plugin Name: DionnieWPToolkit
 * Description: A boilerplate plugin for WordPress development powered by Vite.
 * Version: 1.0
 * Author: Mark Dionnie Bulingit
 */

declare(strict_types=1);


if (! defined('ABSPATH')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

define('DIONNIE_WP_TOOLKIT_WP_NAME', 'DionnieWPToolkit');
define('DIONNIE_WP_TOOLKIT_WP_VERSION', '1.0.0');
define('DIONNIE_WP_TOOLKIT_WP_SLUG', 'dionnie-wp-toolkit-wp');
define('DIONNIE_WP_TOOLKIT_WP_TEXT_DOMAIN', 'dionnie-wp-toolkit-wp');
define('DIONNIE_WP_TOOLKIT_WP_PATH', plugin_dir_path(__FILE__));
define('DIONNIE_WP_TOOLKIT_WP_URL', plugin_dir_url(__FILE__));

if (!defined('DIONNIE_WP_TOOLKIT_WP_DEV_MODE')) {
    define('DIONNIE_WP_TOOLKIT_WP_DEV_MODE', file_exists(DIONNIE_WP_TOOLKIT_WP_PATH . 'public/hot'));
}

$plugin = new \DionnieWPToolkitWP\Plugin();
$plugin->run();

register_activation_hook(__FILE__, [$plugin, 'activate']);
register_deactivation_hook(__FILE__, [$plugin, 'deactivate']);
