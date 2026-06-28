<?php

namespace DionnieWPToolkit\Core;

use DionnieWPToolkit\Core\Interfaces\Registerable;
use DionnieWPToolkit\Core\Modules\GoogleCalendar\GoogleCalendarModule;
use DionnieWPToolkit\Core\Modules\LoginCustomizer\LoginCustomizer;
use DionnieWPToolkit\Core\Modules\TaskTable\TasksTableModule;
use DionnieWPToolkit\Core\Modules\ACF\ACFExtraFields;
use DionnieWPToolkit\Core\Modules\Menus\Menus;
use DionnieWPToolkit\Helpers\DependencyChecker;

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
