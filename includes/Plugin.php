<?php

namespace DionnieWPToolkit\Core;

use DionnieWPToolkit\Core\Interfaces\Registerable;
use DionnieWPToolkit\Core\Modules\GoogleCalendar\GoogleCalendarModule;
use DionnieWPToolkit\Core\Modules\LoginCustomizer\LoginCustomizer;
use DionnieWPToolkit\Core\Modules\TaskTable\TasksTableModule;
use DionnieWPToolkit\Core\Modules\ACF\ACFExtraFields;
use DionnieWPToolkit\Core\Modules\Menus\Menus;
use DionnieWPToolkit\Helpers\DependencyChecker;
use DionnieWPToolkit\Core\Helpers\ViteManifestHelper;

class Plugin
{

    protected array $modules = [];

    public function __construct()
    {
        $this->bootstrap_modules();
    }

    public function run(): void
    {
        add_action('plugins_loaded', [$this, 'registerModules']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        if (defined('DIONNIE_WP_DEV_MODE') && DIONNIE_WP_DEV_MODE === true) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_vite_dev_scripts']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_vite_dev_scripts']);
        }
    }


    function enqueue_vite_dev_scripts()
    {
        wp_enqueue_script_module('vite-client', 'http://localhost:5173/@vite/client');
        wp_enqueue_script_module('vite-reload', 'http://localhost:5173/src/reload.js');
    }


    public function enqueue_assets(): void
    {
        $vite_helper = new ViteManifestHelper();

        $entries = [
            'src/css/app.css',
            'src/js/app.js',
            'src/upholstery-previz/upholstery-previz.tsx',
            'includes/Modules/ACF/CourseBuilderField/acf-course-builder.js' => [
                'deps'      => ['jquery', 'acf-input'],

            ],
            'includes/Modules/ACF/QuizChoiceField/acf-quiz-choices.js' => [
                'deps'      => ['jquery', 'acf-input'],
            ]
        ];

        $vite_helper->enqueue($entries);
    }


    private function bootstrap_modules(): void
    {
        $this->modules = [
            new DependencyChecker(
                DIONNIE_WP_NAME,
                [
                    'Secure Custom Fields' => 'secure-custom-fields/secure-custom-fields.php'
                ]
            ),
            new Menus('dionnie-toolkit'),
            new ACFExtraFields(),
            new TasksTableModule(),
            new GoogleCalendarModule(),
            new LoginCustomizer(),
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
}
