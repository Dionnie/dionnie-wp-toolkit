<?php

namespace DionnieWPToolkitWP\Core;

use DionnieWPToolkitWP\Core\Interfaces\Registerable;
use DionnieWPToolkitWP\Core\Modules\SampleModule\SampleModule;
use DionnieWPToolkitWP\Helpers\DependencyChecker;
use DionnieWPToolkitWP\Core\Helpers\ViteManifestHelper;


class Plugin
{

    protected array $modules = [];

    public function __construct()
    {
        $this->bootstrap_modules();
    }

    public function run(): void
    {

        $is_dev_mode = defined('DIONNIE_WP_TOOLKIT_WP_DEV_MODE') && DIONNIE_WP_TOOLKIT_WP_DEV_MODE === true;
        $is_vite_scripts_enabled = false;

        if ($is_dev_mode) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_vite_dev_scripts']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_vite_dev_scripts']);
            add_action('login_enqueue_scripts', [$this, 'enqueue_vite_dev_scripts']);
            $is_vite_scripts_enabled = true;

            if ($is_vite_scripts_enabled) {
                add_action('wp_enqueue_scripts', [$this, 'enqueue_public_assets']);
                add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
                add_action('login_enqueue_scripts', [$this, 'enqueue_login_assets']);
                add_action('login_footer', [$this, 'print_vite_script_modules'], 20);
            }
        } else {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_public_assets']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
            add_action('login_enqueue_scripts', [$this, 'enqueue_login_assets']);
        }

        add_action('plugins_loaded', [$this, 'registerModules']);
    }

    //** wp_enqueue_script_module doesnt work in the login page by default, so we need to manually print the script modules in the footer **/ 
    public function print_vite_script_modules(): void
    {
        if (function_exists('wp_script_modules')) {
            $script_modules = wp_script_modules();
            $script_modules->print_import_map();
            $script_modules->print_enqueued_script_modules();
            $script_modules->print_script_module_preloads();
        }
    }


    function enqueue_vite_dev_scripts()
    {
        wp_enqueue_script_module('vite-client', 'http://localhost:5173/@vite/client', [], null);
        wp_enqueue_script_module('vite-reload', 'http://localhost:5173/src/reload.js', [], null);
    }

    public function enqueue_login_assets(): void
    {
        $vite_helper = new ViteManifestHelper();

        $entries = [];

        $vite_helper->enqueue($entries);
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
                DIONNIE_WP_TOOLKIT_WP_NAME,
                [
                    'Secure Custom Fields' => 'secure-custom-fields/secure-custom-fields.php'
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
