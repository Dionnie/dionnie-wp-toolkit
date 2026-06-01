<?php
declare(strict_types=1);

namespace Dionnie\Helpers;

class TemplateHelper {

    /**
     * Renders a template file and returns the HTML as a string.
     *
     * @param string $relative_path The path to the template file relative to the plugin root (e.g., 'views/admin.php').
     * @param array $variables Variables to pass to the template.
     * @return string The rendered HTML.
     */
    public static function render(string $relative_path, array $variables = []): string {
        $template_path = dirname(__DIR__) . '/' . ltrim($relative_path, '/');

        if (!file_exists($template_path)) {
            return '<!-- Template not found: ' . esc_html($relative_path) . ' -->';
        }

        extract($variables, EXTR_SKIP);
        
        ob_start();
        include $template_path;
        return ob_get_clean();
    }
}