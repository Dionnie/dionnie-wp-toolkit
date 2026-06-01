<?php
declare(strict_types=1);

namespace Dionnie\Modules\Tasks;

// Ensure the base WP_List_Table class is loaded
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class TasksListTable extends \WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => 'task',
            'plural'   => 'tasks',
            'ajax'     => false
        ]);
    }

    public function get_columns(): array {
        return [
            'cb'          => '<input type="checkbox" />',
            'title'       => 'Title',
            'description' => 'Description',
            'status'      => 'Status',
            'created_at'  => 'Date Created'
        ];
    }

    protected function get_sortable_columns(): array {
        return [
            'title'      => ['title', false],
            'status'     => ['status', false],
            'created_at' => ['created_at', true] // true means it's already sorted
        ];
    }

    protected function column_default($item, $column_name) {
        return esc_html((string) $item[$column_name]);
    }

    protected function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="task_id[]" value="%s" />',
            esc_attr((string) $item['id'])
        );
    }

    protected function column_title($item) {
        $page = $_REQUEST['page'] ?? 'dionnie-tasks-list';
        
        // Define row actions for hovering
        $actions = [
            'edit'   => sprintf('<a href="?page=%s&action=%s&task=%s">Edit</a>', $page, 'edit', $item['id']),
            'delete' => sprintf('<a href="?page=%s&action=%s&task=%s" class="delete">Delete</a>', $page, 'delete', $item['id']),
        ];

        return sprintf('%1$s %2$s',
            '<strong>' . esc_html($item['title']) . '</strong>',
            $this->row_actions($actions)
        );
    }

    protected function column_status($item) {
        $status = esc_html($item['status']);
        $color = $status === 'completed' ? '#00a32a' : '#d63638';
        return sprintf('<span style="color: %s; font-weight: bold;">%s</span>', $color, ucfirst($status));
    }

    public function get_bulk_actions(): array {
        return [
            'delete' => 'Delete'
        ];
    }

    public function prepare_items(): void {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dionnie_tasks';

        $per_page = 10;
        $current_page = $this->get_pagenum();

        $this->_column_headers = [
            $this->get_columns(),
            [], // hidden columns
            $this->get_sortable_columns()
        ];

        // Handle ordering and sorting (with basic validation to prevent SQLi)
        $orderby = isset($_GET['orderby']) ? sanitize_sql_orderby($_GET['orderby']) : 'created_at';
        $order = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'ASC' : 'DESC';
        
        $allowed_columns = ['title', 'status', 'created_at'];
        if (!in_array($orderby, $allowed_columns, true)) {
            $orderby = 'created_at';
        }

        $offset = ($current_page - 1) * $per_page;
        $total_items = (int) $wpdb->get_var("SELECT COUNT(id) FROM {$table_name}");

        $this->items = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table_name} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d", $per_page, $offset),
            ARRAY_A
        );

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => (int) ceil($total_items / $per_page)
        ]);
    }
}