<?php
/**
 * Fired when the plugin is uninstalled.
 */
declare(strict_types=1);

// If uninstall is not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Load the Composer autoloader to access our classes
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use Dionnie\Helpers\DatabaseTable;

// Drop the dionnie_tasks table
$tasksTable = new DatabaseTable('dionnie_tasks');
$tasksTable->dropTable();