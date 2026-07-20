<?php

/**
 * Plugin Name: DionnieBoilerplatePlugin
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

define('DIONNIE_BOILERPLATE_PLUGIN_NAME', 'DionnieBoilerplatePlugin');
define('DIONNIE_BOILERPLATE_PLUGIN_VERSION', '1.0.0');
define('DIONNIE_BOILERPLATE_PLUGIN_SLUG', 'dionnie-boilerplate-plugin');
define('DIONNIE_BOILERPLATE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DIONNIE_BOILERPLATE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DIONNIE_BOILERPLATE_PLUGIN_FILE', __FILE__);

if (!defined('DIONNIE_BOILERPLATE_PLUGIN_DEV_MODE')) {
    define('DIONNIE_BOILERPLATE_PLUGIN_DEV_MODE', file_exists(DIONNIE_BOILERPLATE_PLUGIN_PATH . 'public/hot'));
}

$plugin = new \DionnieBoilerplatePlugin\Plugin();
$plugin->run();
