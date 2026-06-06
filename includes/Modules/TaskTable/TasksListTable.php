<?php

namespace DionnieWPToolkit\Core\Modules\TaskTable;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class TasksListTable extends \WP_List_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => 'task',
            'plural'   => 'tasks',
            'ajax'     => false
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'          => '<input type="checkbox">',
            'title'       => 'Title',
            'description' => 'Description',
            'status'      => 'Status',
            'created_at'  => 'Date Created'
        ];
    }

    protected function column_default($item, $column_name)
    {
        return $item[$column_name] ?? '';
    }

    public function prepare_items()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'dionnie_tasks';

        $columns = $this->get_columns();
        $hidden = [];
        $sortable = [];

        $this->_column_headers = [
            $columns,
            $hidden,
            $sortable
        ];

        $this->items = $wpdb->get_results(
            "SELECT * FROM {$table_name}",
            ARRAY_A
        );
    }
}