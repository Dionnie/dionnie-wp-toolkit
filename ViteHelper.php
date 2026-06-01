<?php
namespace Newewewewe;

class ViteHelper {
    private string $buildPath;
    private string $buildUri;
    private string $viteUri;
    private ?array $manifest = null;

    /**
     * @param string $buildPath The absolute server path to the build directory (e.g., plugin_dir_path(__FILE__) . 'public/build')
     * @param string $buildUri The URL to the build directory (e.g., plugin_dir_url(__FILE__) . 'public/build')
     * @param string $viteUri The URL to the Vite dev server (e.g., 'http://localhost:5173/src')
     */
    public function __construct(string $buildPath, string $buildUri, string $viteUri) {
        $this->buildPath = rtrim($buildPath, '/\\');
        $this->buildUri = rtrim($buildUri, '/');
        $this->viteUri = rtrim($viteUri, '/');
        
        // Add script_loader_tag filter to inject type="module" required by Vite
        add_filter('script_loader_tag', [$this, 'setModuleTypeAttribute'], 10, 3);
    }

    /**
     * Checks if the Vite development server is running via the "hot" file.
     */
    public function isDev(): bool {
        return file_exists($this->buildPath . '/hot');
    }

    /**
     * Gets the Vite dev server URL from the "hot" file.
     */
    public function getDevServerUrl(): string {
        return trim(file_get_contents($this->buildPath . '/hot'));
    }

    /**
     * Parses and returns the manifest.json file.
     */
    private function getManifest(): array {
        if ($this->manifest === null) {
            $manifestPath = $this->buildPath . '/manifest.json';
            if (file_exists($manifestPath)) {
                $this->manifest = json_decode(file_get_contents($manifestPath), true) ?: [];
            } else {
                $this->manifest = [];
            }
        }
        return $this->manifest;
    }

    /**
     * Enqueues the Vite asset (and its CSS dependencies) into WordPress.
     */
    public function enqueue(string $entry, string $handle, array $deps = []): void {
        if ($this->isDev()) {
            $devServer = $this->getDevServerUrl();
            // Enqueue React Refresh preamble for HMR
        
            // Enqueue the actual entry point (JS or CSS)
            if (preg_match('/\.css$/i', $entry)) {
                wp_enqueue_style($handle, $devServer . '/' . $entry, $deps, null);
            } else {
                wp_enqueue_script($handle, $devServer . '/' . $entry, $deps, null, true);
            }
        } else {
            $manifest = $this->getManifest();
            if (!isset($manifest[$entry])) {
                return;
            }

            $chunk = $manifest[$entry];

            // Enqueue JS or CSS
            if (isset($chunk['file'])) {
                if (preg_match('/\.css$/i', $chunk['file'])) {
                    wp_enqueue_style($handle, $this->buildUri . '/' . $chunk['file'], $deps, null);
                } else {
                    wp_enqueue_script($handle, $this->buildUri . '/' . $chunk['file'], $deps, null, true);
                }
            }

          
        }
    }

    /**
     * Injects type="module" to scripts enqueued from Vite.
     */
   public function setModuleTypeAttribute(string $tag, string $handle, string $src): string {

    if ($this->isDev()) {

        // Replace current build URL with Vite dev server
        $src = str_replace($this->buildUri, 'http://localhost:5173', $src);

        // Update the script src inside the tag
        $tag = preg_replace('/src=(["\'])(.*?)\1/', 'src="' . $src . '"', $tag);

        // Remove existing type attribute if any
        $tag = preg_replace('/\stype=(["\'])[^\1]*\1/', '', $tag);

        // Inject type="module"
        return preg_replace('/<script\s/', '<script type="module" ', $tag);
    }

    return $tag;
}

   
}