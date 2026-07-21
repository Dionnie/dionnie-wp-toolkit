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

$plugin = new \DionnieBoilerplatePlugin\Plugin();
$plugin->run();
