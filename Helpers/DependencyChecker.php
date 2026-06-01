<?php
declare(strict_types=1);

namespace Dionnie\Helpers;

class DependencyChecker {
    private string $plugin_name;
    private array $required_plugins;
    private array $missing_plugins = [];

    /**
     * @param string $plugin_name The name of your plugin (used in the error message).
     * @param array $required_plugins Array of required plugins ['Friendly Name' => 'plugin-folder/plugin-file.php']
     */
    public function __construct(string $plugin_name, array $required_plugins) {
        $this->plugin_name = $plugin_name;
        $this->required_plugins = $required_plugins;
    }

    /**
     * Checks if any of the required plugins are missing or inactive.
     */
    public function has_missing_dependencies(): bool {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $this->missing_plugins = [];

        foreach ($this->required_plugins as $name => $slug) {
            if (!is_plugin_active($slug)) {
                $this->missing_plugins[] = $name;
            }
        }

        return !empty($this->missing_plugins);
    }

    public function register_admin_notices(): void {
        add_action('admin_notices', [$this, 'display_admin_notice']);
    }

    public function display_admin_notice(): void {
        if (empty($this->missing_plugins)) {
            return;
        }

        $plugins_list = implode(', ', array_map('esc_html', $this->missing_plugins));
        $message = sprintf(
            __('<strong>%1$s</strong> requires the following plugin(s) to be installed and active: %2$s.', 'dionnie-wp'),
            esc_html($this->plugin_name),
            $plugins_list
        );

        echo '<div class="notice notice-error"><p>' . $message . '</p></div>';
    }
}