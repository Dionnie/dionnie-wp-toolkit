<?php

namespace DionnieWPToolkit\Core\Modules\TaskTable;

use DionnieWPToolkit\Core\Interfaces\Registerable;
use DionnieWPToolkit\Core\Modules\TaskTable\TasksListTable;

class TasksTableModule implements Registerable
{
    public function register(): void
    {
        add_action('admin_menu', [$this, 'register_menu']);
    }

    public function register_menu(): void
    {
        add_submenu_page(
            'dionnie-wp-toolkit',
            'Tasks',
            'Tasks List',
            'manage_options',
            'dionnie-tasks-list',
            [$this, 'render_page']
        );
    }

    public function render_page(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        echo '<div class="wrap">';
        echo '<h1>Tasks</h1>';

        $table = new TasksListTable();

        $table->prepare_items();

        echo '<form method="get">';

        echo '<input type="hidden" 
                     name="page" 
                     value="dionnie-tasks-list">';

        $table->display();

        echo '</form>';
        echo '</div>';
    }
}
