<?php
declare(strict_types=1);

namespace Dionnie\Wp\Admin\Settings;

/**
 * Handles the API Settings registration, validation, and rendering in a single, cohesive class.
 */
class ApiSettings {

    public const DB_OPTION_KEY = 'dionnie_api_settings';

    private string $settings_page_slug;

    public function __construct(string $settings_page_slug) {
        $this->settings_page_slug = $settings_page_slug;
    }
    

    /**
     * Hooks into WordPress.
     */
    public function register_hooks(): void {

        add_action('admin_init', function() {
        
          register_setting(
            self::DB_OPTION_KEY,
            self::DB_OPTION_KEY,
            [
                'type'              => 'array',
                'description'       => 'Settings for Dionnie API Integration',
                'sanitize_callback' => [self::class, 'clean'],
                'show_in_rest'      => false,
                'default'           => self::get_defaults(),
            ]
        );

            add_settings_section(
            'dionnie_api_settings_section',
            'API Integration Settings',
            [$this, 'render_section'],
            $this->settings_page_slug
        );

      

          $this->add_section_fields();

        });

     
        
    }

   
    public static function get_defaults(): array {
        return [
            'api_key'       => '',
            'debug_mode'    => '0',
            'engine_type'   => 'standard',
            'sync_interval' => 'hourly',
            'custom_css'    => '',
        ];
    }

    public static function get_engine_choices(): array {
        return [
            'standard' => __('Standard Sync Engine', 'dionnie-wp'),
            'redis'    => __('Redis-Backed Pipeline', 'dionnie-wp'),
            'async'    => __('Asynchronous Background Worker', 'dionnie-wp'),
        ];
    }


    public static function clean(array $input): array {
        $sanitized = [];

        if (isset($input['api_key'])) {
            $sanitized['api_key'] = sanitize_text_field($input['api_key']);
        }

        $sanitized['debug_mode'] = !empty($input['debug_mode']) ? '1' : '0';

        $valid_engines = array_keys(self::get_engine_choices());
        if (isset($input['engine_type']) && in_array($input['engine_type'], $valid_engines, true)) {
            $sanitized['engine_type'] = $input['engine_type'];
        }

        if (isset($input['custom_css'])) {
            $sanitized['custom_css'] = sanitize_textarea_field($input['custom_css']);
        }

        return $sanitized;
    }

  
    public function render_section(): void {
   echo '<div class="notice notice-info inline">';
    echo '<p>To use this plugin, you must first generate an API key. You can get your free key from your <a href="https://example.com/dashboard" target="_blank">developer dashboard</a>.</p>';
    echo '</div>';
    }

    /**
     * Registers individual form fields.
     */
    private function add_section_fields(): void {
        add_settings_field(
            'api_key',
            __('API Endpoint Token', 'dionnie-wp'),
            [$this, 'render_text_field'],
            $this->settings_page_slug,
            'dionnie_api_settings_section',
            ['key' => 'api_key']
        );

        add_settings_field(
            'engine_type',
            __('Processing Engine', 'dionnie-wp'),
            [$this, 'render_select_field'],
            $this->settings_page_slug,
            'dionnie_api_settings_section',
            ['key' => 'engine_type', 'options' => self::get_engine_choices()]
        );
    }

    public function render_text_field(array $args): void {
        $options = get_option(self::DB_OPTION_KEY, self::get_defaults());
        $key     = $args['key'];
        $value   = $options[$key] ?? '';
        
        printf(
            '<input type="text" class="regular-text" name="%s[%s]" value="%s" />',
            esc_attr(self::DB_OPTION_KEY),
            esc_attr($key),
            esc_attr($value)
        );
    }

    public function render_select_field(array $args): void {
        $options  = get_option(self::DB_OPTION_KEY, self::get_defaults());
        $key      = $args['key'];
        $selected = $options[$key] ?? '';
        
        printf('<select name="%s[%s]">', esc_attr(self::DB_OPTION_KEY), esc_attr($key));
        foreach ($args['options'] as $val => $label) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr($val),
                selected($selected, $val, false),
                esc_html($label)
            );
        }
        echo '</select>';
    }
}