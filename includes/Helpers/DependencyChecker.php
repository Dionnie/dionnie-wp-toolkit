<?php

declare(strict_types=1);

namespace DionnieBoilerplatePlugin\Helpers;

use DionnieBoilerplatePlugin\Registerable;

class DependencyChecker implements Registerable
{
    private string $plugin_name;
    private array $required_plugins;
    private array $missing_plugins = [];

    public function __construct(
        string $plugin_name,
        array $required_plugins
    ) {
        $this->plugin_name = $plugin_name;
        $this->required_plugins = $required_plugins;
    }

    /**
     * Register hooks.
     */
    public function register(): void
    {
        if (!is_admin()) {
            return;
        }

        add_action(
            'admin_init',
            [$this, 'check_dependencies']
        );
    }

    /**
     * Check dependencies.
     */
    public function check_dependencies(): void
    {
        $this->missing_plugins = $this->get_missing_dependencies();

        if (!empty($this->missing_plugins)) {
            add_action(
                'admin_notices',
                [$this, 'display_admin_notice']
            );
        }
    }

    /**
     * Get missing plugins.
     */
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

    /**
     * Display admin notice.
     */
    public function display_admin_notice(): void
    {
        if (empty($this->missing_plugins)) {
            return;
        }

        $plugins = implode(
            ', ',
            array_map('esc_html', $this->missing_plugins)
        );

        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            sprintf(
                __(
                    '<strong>%1$s</strong> requires: %2$s',
                    'dionnie-boilerplate-plugin'
                ),
                esc_html($this->plugin_name),
                $plugins
            )
        );
    }
}
