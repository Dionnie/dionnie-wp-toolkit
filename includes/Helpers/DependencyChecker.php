<?php

declare(strict_types=1);

namespace DionnieBoilerplatePlugin\Helpers;

class DependencyChecker
{
    private string $plugin_name;
    private string $plugin_file;
    private array $required_plugins;
    private array $missing_plugins = [];

    public function __construct(
        string $plugin_name,
        string $plugin_file,
        array $required_plugins
    ) {
        $this->plugin_name = $plugin_name;
        $this->plugin_file = $plugin_file;
        $this->required_plugins = $required_plugins;
    }

    /**
     * Synchronously checks if dependencies are missing.
     */
    public function has_missing_dependencies(): bool
    {
        $this->missing_plugins = $this->get_missing_dependencies();

        return !empty($this->missing_plugins);
    }

    /**
     * Registers the hooks to deactivate the plugin and show the error.
     */
    public function handle_missing_dependencies(): void
    {
        if (!is_admin()) {
            return;
        }

        add_action('admin_init', [$this, 'deactivate_and_notify']);
    }

    /**
     * Safely deactivates the plugin and queues the admin notice.
     */
    public function deactivate_and_notify(): void
    {
        deactivate_plugins(plugin_basename($this->plugin_file));

        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        add_action('admin_notices', [$this, 'display_admin_notice']);
    }

    private function get_missing_dependencies(): array
    {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $missing = [];

        foreach ($this->required_plugins as $name => $slug) {
            if (!is_plugin_active($slug)) {
                $missing[] = $name;
            }
        }

        return $missing;
    }

    public function display_admin_notice(): void
    {
        if (empty($this->missing_plugins)) {
            return;
        }

        $plugins = implode(', ', array_map('esc_html', $this->missing_plugins));

        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            sprintf(
                __('<strong>%1$s</strong> requires: %2$s', 'dionnie-boilerplate-plugin'),
                esc_html($this->plugin_name),
                $plugins
            )
        );
    }
}
