<?php
namespace Newewewewe;

class ViteHelper {
    private string $buildPath;
    private string $buildUri;
    private string $viteUri;
    private ?array $manifest = null;
    private ?array $enqueuedScripts = [];

    /**
     * @param string $buildPath The absolute server path to the build directory (e.g., plugin_dir_path(__FILE__) . 'public/build')
     * @param string $buildUri The URL to the build directory (e.g., plugin_dir_url(__FILE__) . 'public/build')
     * @param string $viteUri The URL to the Vite dev server (e.g., 'http://localhost:5173/src')
     */
    public function __construct(string $buildPath, string $buildUri, string $viteUri, $enqueuedScripts = []) {
        $this->buildPath = rtrim($buildPath, '/\\');
        $this->buildUri = rtrim($buildUri, '/');
        $this->viteUri = rtrim($viteUri, '/');
        $this->enqueuedScripts = $enqueuedScripts;

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



   public function setModuleTypeAttribute(string $tag, string $handle, string $src): string {

    if ($this->isDev()) {
        $src = str_replace($this->buildUri, 'http://localhost:5173', $src);
        $tag = preg_replace('/src=(["\'])(.*?)\1/', 'src="' . $src . '"', $tag);
        $tag = preg_replace('/\stype=(["\'])[^\1]*\1/', '', $tag);
        return preg_replace('/<script\s/', '<script type="module" ', $tag);
    }

    return $tag;
}

   
}