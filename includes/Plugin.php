<?php
namespace DionnieWPToolkit\Core;

use DionnieWPToolkit\Core\Interfaces\Registerable;
use DionnieWPToolkit\Core\Modules\GoogleCalendar\GoogleCalendarModule;
use DionnieWPToolkit\Core\Modules\LoginCustomizer\LoginCustomizer;


class Plugin {

    protected string $plugin_name = 'coderockz-woo-delivery';
    protected string $version = '1.0.0';
    protected array $modules = [];

    public function __construct() {
        if ( defined( 'CODEROCKZ_WOO_DELIVERY_VERSION' ) ) {
            $this->version = CODEROCKZ_WOO_DELIVERY_VERSION;
        }
        
        $this->bootstrap_modules();
    }

    /**
     * Declare and instantiate feature module slices
     */
    private function bootstrap_modules(): void {
        $this->modules = [
           
            new GoogleCalendarModule(),
            new LoginCustomizer(),
            // Add new feature modules here as your plugin grows:
            // new Modules\CheckoutFields\CheckoutFieldsModule(),
            // new Modules\OrderFilters\OrderFiltersModule(),
        ];
    }

    /**
     * Initialize the system by triggering all registered structural modules
     */
    public function run(): void {
        foreach ( $this->modules as $module ) {
            if ( $module instanceof Registerable ) {
                $module->register();
            }
        }
    }

    public function get_plugin_name(): string { return $this->plugin_name; }
    public function get_version(): string { return $this->version; }
}