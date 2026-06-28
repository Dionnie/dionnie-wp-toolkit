<?php

declare(strict_types=1);

namespace DionnieWPToolkit\Core\Helpers;

class ViteManifestHelper
{
    private string $devServer = 'http://localhost:5173/';

    /**
     * Enqueues Vite entries with support for WordPress dependencies and options.
     * * @param array $entries Associative array of entry files or flat list of strings.
     */

    public function __construct() {}


    public function enqueue(array $entries): void
    {
        if (empty($entries)) {
            return;
        }

        if ($this->isDevMode()) {
            $this->enqueueForDevelopment($entries);
            return;
        }

        $this->enqueueForProduction($entries);
    }

    private function isDevMode(): bool
    {
        return file_exists(rtrim(DIONNIE_WP_PATH, '/') . '/public/hot');
    }




    private function enqueueForDevelopment(array $entries): void
    {

        foreach ($entries as $entryKey => $config) {
            $options = is_array($config) ? $config : [];
            $deps    = $options['deps'] ?? [];

            // If entry key is a numeric index, the config is actually the file path string
            $fileKey = is_int($entryKey) ? $config : $entryKey;

            $fileUrl = $this->devServer . ltrim($fileKey, '/');
            $handle  = $this->generateHandle($fileKey);

            if (preg_match('/\.(css|scss|sass)$/', $fileKey)) {
                wp_enqueue_style($handle, $fileUrl, $deps, null);
            } elseif (preg_match('/\.(js|jsx|ts|tsx)$/', $fileKey)) {
                wp_enqueue_script_module($handle, $fileUrl, $deps, null);
            }
        }
    }

    private function enqueueForProduction(array $entries): void
    {
        $manifestPath = rtrim(DIONNIE_WP_PATH, '/') . '/public/build/manifest.json';

        if (!file_exists($manifestPath)) {
            return;
        }

        $manifestData = json_decode(file_get_contents($manifestPath), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($manifestData)) {
            return;
        }

        $assetsUrl = rtrim(DIONNIE_WP_URL, '/') . '/public/build/';

        foreach ($entries as $entryKey => $config) {
            $options  = is_array($config) ? $config : [];
            $deps     = $options['deps'] ?? [];
            $inFooter = $options['in_footer'] ?? true;

            $fileKey = is_int($entryKey) ? $config : $entryKey;

            if (!isset($manifestData[$fileKey])) {
                continue;
            }

            $asset = $manifestData[$fileKey];

            if (!isset($asset['file'])) {
                continue;
            }

            $fileUrl = $assetsUrl . ltrim($asset['file'], '/');
            $handle  = $this->generateHandle($fileKey);

            if (preg_match('/\.(css|scss|sass)$/', $fileKey)) {
                wp_enqueue_style($handle, $fileUrl, $deps, null);
                continue;
            }

            if (preg_match('/\.(js|jsx|ts|tsx)$/', $fileKey)) {
                wp_enqueue_script($handle, $fileUrl, $deps, DIONNIE_WP_VERSION, $inFooter);

                if (!empty($asset['css']) && is_array($asset['css'])) {
                    foreach ($asset['css'] as $index => $cssFile) {
                        $cssHandle = $handle . '-css-' . (string)$index;
                        wp_enqueue_style($cssHandle, $assetsUrl . ltrim($cssFile, '/'), [], DIONNIE_WP_VERSION);
                    }
                }
            }
        }
    }

    private function generateHandle(string $entryKey): string
    {
        $filename  = basename($entryKey);
        $cleanName = (string) preg_replace('/\.(css|scss|sass|js|jsx|ts|tsx)$/', '', $filename);

        return DIONNIE_WP_SLUG . '-' . sanitize_title($cleanName);
    }
}
