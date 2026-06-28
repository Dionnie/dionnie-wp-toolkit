<?php

declare(strict_types=1);


if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

$plugin = new \DionnieWPToolkit\Core\Plugin();
$plugin->uninstall();
