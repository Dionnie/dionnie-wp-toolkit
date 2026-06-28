<?php

namespace DionnieWPToolkit\Core\Modules\Menus;


use DionnieWPToolkit\Core\Interfaces\Registerable;
use DionnieWPToolkit\Helpers\Views;



class Menus implements Registerable
{

    public function register(): void
    {


        add_action('admin_menu', function () {
            add_menu_page(
                DIONNIE_WP_NAME,   // Page title
                "ddddd",             // Menu title
                'manage_options',             // Required capability
                DIONNIE_WP_SLUG,         // Menu slug
                function () {
                    echo Views::render('plugin.php');
                },
                'dashicons-admin-generic',    // Dashicon icon
                1                          // Position
            );
        });
    }
}
