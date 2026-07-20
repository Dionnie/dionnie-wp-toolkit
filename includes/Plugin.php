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

        $is_dev_mode = defined('DIONNIE_BOILERPLATE_PLUGIN_DEV_MODE') && DIONNIE_BOILERPLATE_PLUGIN_DEV_MODE === true;
        $is_vite_scripts_enabled = false;

        if ($is_dev_mode) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_vite_dev_scripts']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_vite_dev_scripts']);

            $is_vite_scripts_enabled = true;

            if ($is_vite_scripts_enabled) {
                add_action('wp_enqueue_scripts', [$this, 'enqueue_public_assets']);
                add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
            }
        } else {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_public_assets']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        }

        add_action('plugins_loaded', [$this, 'registerModules']);
    }



    function enqueue_vite_dev_scripts()
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
            new DependencyChecker(
                DIONNIE_BOILERPLATE_PLUGIN_NAME,
                [
                    'Elementor' => 'elementor/elementor.php',
                    'Elementor Pro' => 'elementor-pro/elementor-pro.php'
                ]
            ),
            new SampleModule(),
        ];
    }

    public function registerModules(): void
    {
        foreach ($this->modules as $module) {
            if ($module instanceof Registerable) {
                $module->register();
            }
        }
    }

    function activate() {}

    function deactivate() {}

    function uninstall() {}
}
