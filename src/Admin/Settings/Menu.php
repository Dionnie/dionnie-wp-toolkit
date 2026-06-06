<?php
declare(strict_types=1);

namespace DionnieWPToolkit\Wp\Admin\Settings;

/**
 * Handles the API Settings registration, validation, and rendering in a single, cohesive class.
 */
class Menu {

    public const API_DB_OPTION_KEY = 'dionnie_api_settings';

    private string $settings_page_slug;

    public function __construct(string $settings_page_slug) {
        $this->settings_page_slug = $settings_page_slug;
    }
    
    public function register_hooks(): void {
        add_action('admin_menu', [$this, 'add_options_page']);
        
    }

    public function add_options_page(): void {
        add_options_page(
            'API Settings',
            'API Settings',
            'manage_options',
            $this->settings_page_slug,
            [$this, 'render_layout']
        );
    }

    public function render_layout(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields(self::API_DB_OPTION_KEY);
                do_settings_sections($this->settings_page_slug);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }


}