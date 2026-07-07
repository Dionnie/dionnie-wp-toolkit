<?php

namespace DionnieWPToolkit\Core;

use DionnieWPToolkit\Core\Interfaces\Registerable;
use DionnieWPToolkit\Core\Modules\GoogleCalendar\GoogleCalendarModule;
use DionnieWPToolkit\Core\Modules\LoginCustomizer\LoginCustomizer;
use DionnieWPToolkit\Core\Modules\TaskTable\TasksTableModule;
use DionnieWPToolkit\Core\Modules\ACF\ACFExtraFields;
use DionnieWPToolkit\Core\Modules\Menus\Menus;
use DionnieWPToolkit\Helpers\DependencyChecker;
use DionnieWPToolkit\Helpers\DatabaseTable;
use DionnieWPToolkit\Core\Helpers\ViteManifestHelper;
use DionnieWPToolkit\Wp\UserBuilder\Inductee;


class Plugin
{

    protected array $modules = [];

    public function __construct()
    {
        $this->bootstrap_modules();
    }

    public function run(): void
    {

        $is_dev_mode = defined('DIONNIE_WP_DEV_MODE') && DIONNIE_WP_DEV_MODE === true;
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
        wp_enqueue_script_module('vite-client', 'http://localhost:5173/@vite/client');
        wp_enqueue_script_module('vite-reload', 'http://localhost:5173/src/reload.js');
    }

    function activate()
    {
        $tasksTable = new DatabaseTable('dionnie_tasks');
        $schema = "
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        description text NOT NULL,
        status varchar(50) DEFAULT 'pending' NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ";
        $tasksTable->createTable($schema);
    }

    function deactivate() {}


    function uninstall()
    {
        $tasksTable = new DatabaseTable('dionnie_tasks');
        $tasksTable->dropTable();
    }


    public function enqueue_public_assets(): void
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

    public function enqueue_admin_assets(): void
    {
        $vite_helper = new ViteManifestHelper();

        $entries = [
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
