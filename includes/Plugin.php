<?php

namespace DionnieWPToolkit\Core;

use DionnieWPToolkit\Core\Interfaces\Registerable;
use DionnieWPToolkit\Core\Modules\GoogleCalendar\GoogleCalendarModule;
use DionnieWPToolkit\Core\Modules\LoginCustomizer\LoginCustomizer;
use DionnieWPToolkit\Core\Modules\TaskTable\TasksTableModule;
use DionnieWPToolkit\Core\Modules\LMS\QuizField;
use DionnieWPToolkit\Core\Modules\ACFFieldsModule\AcfFieldsModule;

class Plugin
{
    protected string $plugin_name = 'dionnie-wp-toolkit';
    protected string $version = '1.0.0';
    protected array $modules = [];

    public function __construct()
    {
        if (defined('CODEROCKZ_WOO_DELIVERY_VERSION')) {
            $this->version = CODEROCKZ_WOO_DELIVERY_VERSION;
        }

        // ONLY instantiate, do NOT run anything yet
        $this->bootstrap_modules();
    }

    /**
     * Instantiate modules only (safe)
     */
    private function bootstrap_modules(): void
    {
        $this->modules = [

         new AcfFieldsModule(),
         new TasksTableModule(),
         new GoogleCalendarModule(),
         new LoginCustomizer(),
        ];
    }

    /**
     * Run modules AFTER WordPress is ready
     */
public function run(): void
{
    add_action('plugins_loaded', [$this, 'registerModules']);
}

public function registerModules(): void
{
    foreach ($this->modules as $module) {
        if ($module instanceof Registerable) {
            $module->register();
        }
    }
}

    public function get_plugin_name(): string
    {
        return $this->plugin_name;
    }

    public function get_version(): string
    {
        return $this->version;
    }
}