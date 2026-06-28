<?php

declare(strict_types=1);

namespace DionnieWPToolkit\Core\Helpers;

class ViteManifestHelper
{
    private string $devServer = 'http://localhost:5173/';
    private string $baseDir;
    private string $baseUrl;

    public function __construct()
    {
        $this->baseDir = WP_PLUGIN_DIR . '/dionnie-wp-toolkit';
        $this->baseUrl = plugins_url('/dionnie-wp-toolkit');
    }

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
        return file_exists($this->baseDir . '/public/hot');
    }

    private function enqueueForDevelopment(array $entries): void
    {
        wp_enqueue_script_module('vite-client', $this->devServer . '@vite/client');

        foreach ($entries as $entryKey) {
            $fileUrl = $this->devServer . ltrim($entryKey, '/');
            $handle  = 'dionnie-' . sanitize_title($entryKey);

            if (preg_match('/\.(css|scss|sass)$/', $entryKey)) {
                wp_enqueue_style($handle, $fileUrl, [], null);
            } elseif (preg_match('/\.(js|jsx|ts|tsx)$/', $entryKey)) {
                wp_enqueue_script_module($handle, $fileUrl);
            }
        }
    }

    private function enqueueForProduction(array $entries): void
    {
        $manifestPath = $this->baseDir . '/public/build/manifest.json';

        if (!file_exists($manifestPath)) {
            return;
        }

        $manifestData = json_decode(file_get_contents($manifestPath), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($manifestData)) {
            return;
        }

        $assetsUrl = $this->baseUrl . '/public/build/';

        foreach ($entries as $entryKey) {
            if (!isset($manifestData[$entryKey])) {
                continue;
            }

            $asset = $manifestData[$entryKey];

            if (!isset($asset['file'])) {
                continue;
            }

            $fileUrl = $assetsUrl . ltrim($asset['file'], '/');
            $handle  = 'dionnie-' . sanitize_title($entryKey);

            if (preg_match('/\.(css|scss|sass)$/', $entryKey)) {
                wp_enqueue_style($handle, $fileUrl, [], null);
                continue;
            }

            if (preg_match('/\.(js|jsx|ts|tsx)$/', $entryKey)) {
                wp_enqueue_script($handle, $fileUrl, [], null, true);

                if (!empty($asset['css']) && is_array($asset['css'])) {
                    foreach ($asset['css'] as $index => $cssFile) {
                        $cssHandle = $handle . '-css-' . (string)$index;
                        wp_enqueue_style($cssHandle, $assetsUrl . ltrim($cssFile, '/'), [], null);
                    }
                }
            }
        }
    }
}
