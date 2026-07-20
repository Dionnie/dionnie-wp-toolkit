<?php

namespace DionnieBoilerplatePlugin;

use DionnieBoilerplatePlugin\Registerable;
use DionnieBoilerplatePlugin\Helpers\DependencyChecker;
use DionnieBoilerplatePlugin\Helpers\ViteManifestHelper;
use DionnieBoilerplatePlugin\Modules\SampleModule\SampleModule;

class Plugin
{
    protected array $modules = [];

    public function __construct()
    {
        $this->bootstrap_modules();
    }

    public function run(): void
    {
        // 1. Core Infrastructure & Lifecycle Gatekeeper
        $dependency_checker = new DependencyChecker(
            DIONNIE_BOILERPLATE_PLUGIN_NAME,
            DIONNIE_BOILERPLATE_PLUGIN_FILE,
            [
                'Elementor' => 'elementor/elementor.php',
                'Elementor Pro' => 'elementor-pro/elementor-pro.php'
            ]
        );

        if ($dependency_checker->has_missing_dependencies()) {
            // Trigger shutdown and exit the run method immediately.
            $dependency_checker->handle_missing_dependencies();
            return;
        }

        // 2. Standard Lifecycle Hooks (Only reached if dependencies are met)
        register_activation_hook(DIONNIE_BOILERPLATE_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(DIONNIE_BOILERPLATE_PLUGIN_FILE, [$this, 'deactivate']);
        register_uninstall_hook(DIONNIE_BOILERPLATE_PLUGIN_FILE, [self::class, 'uninstall']);

        // 3. Assets Enqueueing
        if (defined('DIONNIE_BOILERPLATE_PLUGIN_DEV_MODE') && DIONNIE_BOILERPLATE_PLUGIN_DEV_MODE === true) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_vite_dev_scripts']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_vite_dev_scripts']);
        }

        add_action('wp_enqueue_scripts', [$this, 'enqueue_public_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        // 4. Business Logic Modules (Now safely protected!)
        add_action('plugins_loaded', [$this, 'register_modules']);
    }

    public function enqueue_vite_dev_scripts(): void
    {
        wp_enqueue_script_module('vite-client', 'http://localhost:5173/@vite/client', [], null);
        wp_enqueue_script_module('vite-reload', 'http://localhost:5173/src/reload.js', [], null);
    }

    public function enqueue_public_assets(): void
    {
        $vite_helper = new ViteManifestHelper();

        $entries = [
            'src/css/app.css',
            'src/js/app.js',
        ];

        $vite_helper->enqueue($entries);
    }

    public function enqueue_admin_assets(): void
    {
        $vite_helper = new ViteManifestHelper();
        $entries = [];
        $vite_helper->enqueue($entries);
    }

    private function bootstrap_modules(): void
    {
        $this->modules = [
            new SampleModule(),
        ];
    }

    public function register_modules(): void
    {
        foreach ($this->modules as $module) {
            if ($module instanceof Registerable) {
                $module->register();
            }
        }
    }

    public function activate(): void {}

    public function deactivate(): void {}

    public static function uninstall(): void {}
}
