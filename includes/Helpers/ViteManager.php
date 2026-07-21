<?php

declare(strict_types=1);

namespace DionnieBoilerplatePlugin\Helpers;

class ViteManager
{
    private string $pluginPath;
    private string $pluginUrl;
    private string $pluginSlug;
    private string $pluginVersion;
    private string $devServer;

    /**
     * @param string $pluginPath Absolute path to the main plugin directory.
     * @param string $pluginUrl  Base URL to the plugin directory.
     * @param string $pluginSlug Unique slug used for asset handles.
     * @param string $pluginVersion Current version of the plugin.
     * @param string $devServer  URL of the Vite development server.
     */
    public function __construct(
        string $pluginPath,
        string $pluginUrl,
        string $pluginSlug,
        string $pluginVersion,
        string $devServer = 'http://localhost:5173/'
    ) {
        $this->pluginPath = $pluginPath;
        $this->pluginUrl = $pluginUrl;
        $this->pluginSlug = $pluginSlug;
        $this->pluginVersion = $pluginVersion;
        $this->devServer = $devServer;
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
        return file_exists(rtrim($this->pluginPath, '/') . '/public/hot');
    }

    private function enqueueForDevelopment(array $entries): void
    {
        foreach ($entries as $entryKey => $config) {
            $options = is_array($config) ? $config : [];
            $deps    = $options['deps'] ?? [];

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
        $manifestPath = rtrim($this->pluginPath, '/') . '/public/build/manifest.json';

        if (!file_exists($manifestPath)) {
            return;
        }

        $manifestData = json_decode(file_get_contents($manifestPath), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($manifestData)) {
            return;
        }

        $assetsUrl = rtrim($this->pluginUrl, '/') . '/public/build/';

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
                wp_enqueue_script($handle, $fileUrl, $deps, $this->pluginVersion, $inFooter);

                if (!empty($asset['css']) && is_array($asset['css'])) {
                    foreach ($asset['css'] as $index => $cssFile) {
                        $cssHandle = $handle . '-css-' . (string)$index;
                        wp_enqueue_style($cssHandle, $assetsUrl . ltrim($cssFile, '/'), [], $this->pluginVersion);
                    }
                }
            }
        }
    }

    private function generateHandle(string $entryKey): string
    {
        $filename  = basename($entryKey);
        $cleanName = (string) preg_replace('/\.(css|scss|sass|js|jsx|ts|tsx)$/', '', $filename);

        return $this->pluginSlug . '-' . sanitize_title($cleanName);
    }
}
