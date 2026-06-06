<?php 
namespace Dionnie_WP_Toolkit\Menus;

// Importing other classes via namespaces
use DionnieWPToolkit\Admin\SettingsPage;
use MyPlugin\Frontend\Assets;

class Menu {


 private $plugin_slug;
    public function __construct( string $plugin_slug ) {
        $this->plugin_slug = $plugin_slug;
    }

function my_custom_plugin_admin_menu() {

    add_menu_page(
        'Dionnie Toolkit Dashboard',          
        'Dionnie Toolkit',                    
        'manage_options',              
        $plugin_slug,  
         DionnieWPToolkit\Helpers\TemplateHelper::render('views/admin.php'),
        'dashicons-admin-generic',   
        25                        
    );

    add_submenu_page(
        $plugin_slug,  
        'Tasks',                  
        'Tasks List',                  
        'manage_options',              
        'dionnie-tasks-list',           
        'dionnie_tasks_list_ui'         
    );
}
}
?>


