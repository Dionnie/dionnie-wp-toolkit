<?php

/**
 * Plugin Name: Dionnie Toolkit
 * Description: This is a basic plugin that adds a custom message to posts.
 * Version: 1.0
 * Author: Your Name
 */

declare(strict_types=1);


if (! defined('ABSPATH')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

define('PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PLUGIN_URL', plugin_dir_url(__FILE__));




use DionnieWPToolkit\Wp\Admin\Settings\Menu;


(new \DionnieWPToolkit\Helpers\DependencyChecker(
    'Dionnie Toolkit',
    [
        'Secure Custom Fields' => 'secure-custom-fields/secure-custom-fields.php'
    ]
))->register();


(new \DionnieWPToolkit\Core\Plugin())->run();

register_activation_hook(__FILE__, [new \DionnieWPToolkit\Core\Activator(), 'activate']);
register_deactivation_hook(__FILE__, [new \DionnieWPToolkit\Core\Deactivator(), 'deactivate']);

add_action('plugins_loaded', function (): void {


    if (is_admin()) {

        $admin_menu = new Menu('dionnie-api-settings');
        $admin_menu->register_hooks();
    }
});

if (file_exists(plugin_dir_path(__FILE__) . 'public/hot')) {


    function enqueue_vite_dev_scripts()
    {
        wp_enqueue_script_module('vite-client', 'http://localhost:5173/@vite/client');
        wp_enqueue_script_module('vite-reload', 'http://localhost:5173/src/reload.js'); //reload.js must load first


        $dependencies = [

            'scf-commands-custom-post-types', // From your list
            'scf-commands-admin'              // From your list
        ];

        wp_enqueue_script_module(
            'acf-quiz-choices-js',
            'http://localhost:5173/includes/Modules/ACF/QuizChoiceField/acf-quiz-choices.js'
        );

        wp_enqueue_script_module(
            'acf-course-builder-js',
            'http://localhost:5173/includes/Modules/ACF/CourseBuilderField/acf-course-builder.js'
        );
    }

    add_action('wp_enqueue_scripts', 'enqueue_vite_dev_scripts');
    add_action('admin_enqueue_scripts', 'enqueue_vite_dev_scripts');

    add_action('wp_enqueue_scripts', function () {
        wp_enqueue_script_module('app-css', 'http://localhost:5173/src/css/app.css', array());
        wp_enqueue_script_module('app-js', 'http://localhost:5173/src/js/app.js', array());


        wp_enqueue_script_module(
            'upholstery-previz-js',
            'http://localhost:5173/src/upholstery-previz/upholstery-previz.tsx',
            array(),
            null
        );
    });
}
