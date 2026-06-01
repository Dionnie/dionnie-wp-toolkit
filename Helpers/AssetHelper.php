<?php
declare(strict_types=1);

namespace Dionnie\Helpers;

class AssetHelper {
    
    /**
     * Enqueues a CSS stylesheet with automatic cache-busting based on file modification time.
     *
     * @param string $handle The script handle.
     * @param string $relative_path Path to the file relative to the plugin root (e.g., 'assets/css/style.css').
     * @param array  $deps   An array of registered stylesheet handles this stylesheet depends on.
     */
    public static function enqueue_style(string $handle, string $relative_path, array $deps = []): void {
        $plugin_dir = dirname(__DIR__); // Assumes this is in plugin-root/Helpers/
        $file_path = $plugin_dir . '/' . ltrim($relative_path, '/');
        $file_url = plugin_dir_url($plugin_dir) . basename($plugin_dir) . '/' . ltrim($relative_path, '/');
        
        $version = file_exists($file_path) ? (string) filemtime($file_path) : '1.0.0';
        wp_enqueue_style($handle, $file_url, $deps, $version);
    }

    /**
     * Enqueues a JavaScript file with automatic cache-busting.
     *
     * @param string $handle The script handle.
     * @param string $relative_path Path to the file relative to the plugin root.
     * @param array  $deps   Dependencies.
     * @param bool   $in_footer Whether to enqueue the script before </body> instead of in the <head>.
     */
    public static function enqueue_script(string $handle, string $relative_path, array $deps = [], bool $in_footer = true): void {
        $plugin_dir = dirname(__DIR__);
        $file_path = $plugin_dir . '/' . ltrim($relative_path, '/');
        $file_url = plugin_dir_url($plugin_dir) . basename($plugin_dir) . '/' . ltrim($relative_path, '/');
        
        $version = file_exists($file_path) ? (string) filemtime($file_path) : '1.0.0';
        wp_enqueue_script($handle, $file_url, $deps, $version, $in_footer);
    }
}