<?php
/**
 * Plugin Name: Dionnie WP Toolki
 * Description: This is a basic plugin that adds a custom message to posts.
 * Version: 1.0
 * Author: Your Name
 */
declare(strict_types=1);

// Prevent direct access to the file for security
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    add_action('admin_notices', function(): void {
        echo '<div class="error"><p>' . esc_html__('Autoloader missing. Run "composer install" inside the plugin directory.', 'dionnie-wp') . '</p></div>';
    });
    return;
}

use Dionnie\Wp\Admin\Settings\Menu;
use Dionnie\Helpers\DependencyChecker;
use Dionnie\Modules\LoginCustomizer\LoginCustomizer;
use Dionnie\Modules\Tasks\Tasks;
use Dionnie\Wp\Users\UserSetup;
use Dionnie\Helpers\DatabaseTable;


register_activation_hook(__FILE__, function(): void {
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
});


add_action('plugins_loaded', function(): void {


    $login_customizer = new LoginCustomizer();
    $login_customizer->register_hooks();

    if (is_admin()) {
     
        $dependency_checker = new DependencyChecker('Dionnie WP Toolki', [
            'Secure Custom Fields' => 'secure-custom-fields/secure-custom-fields.php'
        ]);

        if ($dependency_checker->has_missing_dependencies()) {
            $dependency_checker->register_admin_notices();
            return; 
        }
     

        $admin_menu = new Menu( 'dionnie-api-settings' );
        $admin_menu->register_hooks();
    }

    // Setup custom roles and profile fields
    $roles_to_add = [
        'shop_manager_lite' => [
            'display_name' => __('Shop Manager Lite', 'dionnie-wp'),
            'capabilities' => [
                'read' => true,
                'edit_posts' => true,
                'delete_posts' => false,
            ],
        ],
    ];

    $profile_fields = [
        'employee_id' => [
            'label' => __('Employee ID', 'dionnie-wp'),
            'description' => __('Enter the employee identification number.', 'dionnie-wp'),
        ],
        'department' => [
            'label' => __('Department', 'dionnie-wp'),
            'description' => __("Enter the user's department.", 'dionnie-wp'),
        ],
    ];

    $user_setup = new UserSetup($roles_to_add, $profile_fields);
    $user_setup->register_hooks();
});

if(file_exists( plugin_dir_path(__FILE__) . 'public/hot')){


function enqueue_vite_dev_scripts() {
    wp_enqueue_script_module( 'vite-client', 'http://localhost:5173/@vite/client');
    wp_enqueue_script_module( 'vite-reload', 'http://localhost:5173/src/reload.js'); //reload.js must load first
}

add_action('wp_enqueue_scripts', 'enqueue_vite_dev_scripts');
add_action('admin_enqueue_scripts', 'enqueue_vite_dev_scripts');

add_action('wp_enqueue_scripts', function () {
wp_enqueue_script_module( 'app-css', 'http://localhost:5173/src/css/app.css', array() );
wp_enqueue_script_module( 'app-js', 'http://localhost:5173/src/js/app.js', array() );


wp_enqueue_script_module(
    'upholstery-previz-js', 
   'http://localhost:5173/src/upholstery-previz/upholstery-previz.tsx', array(), null); 
});

}

// Hook to add the menu item to the WordPress admin dashboard
add_action('admin_menu', 'my_custom_plugin_admin_menu');

function my_custom_plugin_admin_menu() {
    add_menu_page(
        'My Plugin Dashboard',          // Page title
        'My Plugin',                    // Menu title
        'manage_options',               // Required capability
        'my-custom-plugin-dashboard',   // Menu slug
        'my_custom_plugin_dashboard_ui',// Callback function to render the UI
        'dashicons-admin-generic',      // Dashicon icon
        25                              // Position in the menu
    );

    add_submenu_page(
        'my-custom-plugin-dashboard',   // Parent slug
        'Tasks',                        // Page title
        'Tasks List',                   // Menu title
        'manage_options',               // Required capability
        'dionnie-tasks-list',           // Menu slug
        'dionnie_tasks_list_ui'         // Callback function
    );
}

// Callback function to render the dashboard UI
function my_custom_plugin_dashboard_ui() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // The values you requested
    $build_path = plugin_dir_path(__FILE__) . 'public/build';
    $build_url = plugin_dir_url(__FILE__) . 'public/build';

    // Check for the Vite 'hot' file to determine environment and dev server URL
    $is_dev = false;
    $vite_url = '';
    $hot_file_build = $build_path . '/hot';
    $hot_file_public = plugin_dir_path(__FILE__) . 'public/hot';
    
    if (file_exists($hot_file_build)) {
        $is_dev = true;
        $vite_url = trim(file_get_contents($hot_file_build));
    } elseif (file_exists($hot_file_public)) {
        $is_dev = true;
        $vite_url = trim(file_get_contents($hot_file_public));
    }

    // Output the UI
    ?>
    <div class="wrap">
        <h1>Mark Dionnie</h1>
        
        <div class="card" style="max-width: 800px; margin-top: 20px; padding: 20px;">
            <h2 class="title">Environment VariablesX</h2>
            <p>Here are the current build paths and URLs configured for this plugin:</p>
            
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                <tr>
                    <td><strong>Environment Mode</strong></td>
                    <td>
                        <?php if ($is_dev): ?>
                            <span style="color: #d63638; font-weight: bold;">Development (HMR)</span>
                        <?php else: ?>
                            <span style="color: #00a32a; font-weight: bold;">Live (Production)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Vite Dev Server URL</strong></td>
                    <td><code><?php echo $is_dev ? esc_url($vite_url) : 'N/A'; ?></code></td>
                </tr>
                    <tr>
                        <td><strong>Build Path</strong><br><em>(Absolute server path)</em></td>
                        <td><code><?php echo esc_html($build_path); ?></code></td>
                    </tr>
                    <tr>
                        <td><strong>Build URL</strong><br><em>(Public web URL)</em></td>
                        <td><code><?php echo esc_url($build_url); ?></code></td>
                    </tr>
                </tbody>
            </table>

            <h2 class="title" style="margin-top: 30px;">Developer Notes</h2>
            <p>To enable <strong>Hot Module Replacement (HMR)</strong> for local development, you need to start the Vite dev server. Open your terminal in the plugin directory and run:</p>
            <p style="background: #f0f0f1; padding: 10px; border-left: 4px solid #00a32a; font-family: monospace; font-size: 14px;">npm run dev --host</p>
            <p><em><strong>Note:</strong> The <code>--host</code> flag exposes the dev server to your local network, which is required if you are using Laragon, Docker, or other virtualized local development environments. </em></p>
            <p><em>Once the server is running, a <code>hot</code> file is generated in the build directory, and this dashboard will detect the development environment and switch over automatically. To prepare the plugin for production, stop the dev server and run <code>npm run build</code>.</em></p>
        </div>
    </div>
    <?php
}

// Callback function to render the Tasks List table UI
function dionnie_tasks_list_ui() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    $tasksTable = new \Dionnie\Modules\Tasks\TasksListTable();
    $tasksTable->prepare_items();
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Tasks</h1>
        <form method="get">
            <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page'] ?? 'dionnie-tasks-list'); ?>" />
            <?php $tasksTable->display(); ?>
        </form>
    </div>
    <?php
}
