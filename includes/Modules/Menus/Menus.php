<?php

namespace DionnieWPToolkit\Core\Modules\Menus;


use DionnieWPToolkit\Core\Interfaces\Registerable;
use DionnieWPToolkit\Helpers\Views;



class Menus implements Registerable
{

    /** @var string */
    private $plugin_slug;

    public function __construct(string $plugin_slug)
    {
        $this->plugin_slug = $plugin_slug;
    }

    public function register(): void
    {
        add_menu_page(
            'Dionnie Toolkit Dashboard',
            'Dionnie Toolkit',
            'manage_options',
            $this->plugin_slug . '-settings',
            Views::render('admin.php'),
            'dashicons-admin-generic',
            25
        );


        add_action('admin_menu', function () {
            add_menu_page(
                'Dionnie Toolkit Dashboard',   // Page title
                'Dionnie Toolkit',             // Menu title
                'manage_options',             // Required capability
                'dionnie-wp-toolkit',         // Menu slug
                function () {
                    echo Views::render('plugin.php');
                },
                'dashicons-admin-generic',    // Dashicon icon
                25                            // Position
            );
        });
    }
}
