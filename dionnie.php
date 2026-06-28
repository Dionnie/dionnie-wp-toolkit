<?php

/**
 * Plugin Name: Dionnie Toolkit
 * Description: This is a basic plugin that adds a custom message to posts.
 * Version: 1.0
 * Author: Your Name
 */

declare(strict_types=1);


if (! defined('ABSPATH')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

define('DIONNIE_WP_NAME', 'Dionnie WP Toolkit');
define('DIONNIE_WP_VERSION', '1.0.0');
define('DIONNIE_WP_SLUG', 'dionnie-wp-toolkit');
define('DIONNIE_WP_TEXT_DOMAIN', 'dionnie-wp-toolkit');
define('DIONNIE_WP_PATH', plugin_dir_path(__FILE__));
define('DIONNIE_WP_URL', plugin_dir_url(__FILE__));

if (!defined('DIONNIE_WP_DEV_MODE')) {
    define('DIONNIE_WP_DEV_MODE', file_exists(DIONNIE_WP_PATH . 'public/hot'));
}

$plugin = new \DionnieWPToolkit\Core\Plugin();
$plugin->run();


register_activation_hook(__FILE__, [$plugin, 'activate']);
register_deactivation_hook(__FILE__, [$plugin, 'deactivate']);
