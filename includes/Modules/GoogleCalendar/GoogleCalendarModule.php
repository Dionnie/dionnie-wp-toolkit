<?php
namespace DionnieWPToolkit\Core\Modules\GoogleCalendar;

use DionnieWPToolkit\Core\Interfaces\Registerable;

class GoogleCalendarModule implements Registerable {

    private array $settings;

    public function __construct() {
        $this->settings = get_option('coderockz_woo_delivery_google_calendar_settings', []);
    }

    public function register(): void {
        if ( ! $this->should_load() ) {
            return;
        }

        // Admin Integrations
        if ( is_admin() ) {
            add_action( 'wp_ajax_coderockz_woo_delivery_process_google_calendar_settings', [ $this, 'save_settings' ] );
            add_action( 'wp_ajax_coderockz_woo_delivery_make_google_calendar_sync_bulk', [ $this, 'bulk_sync' ] );
            add_action( 'wp_ajax_coderockz_woo_delivery_make_google_unauthenticate', [ $this, 'deauthenticate' ] );
        }

        // Public Integrations
        add_action( 'woocommerce_thankyou', [ $this, 'sync_order_to_calendar' ], 10, 1 );
        add_filter( 'woocommerce_thankyou_order_received_text', [ $this, 'add_calendar_button' ], 999, 2 );
    }

    private function should_load(): bool {
        $customer_sync = $this->settings['google_calendar_customer_sync'] ?? false;
        $client_sync   = $this->settings['google_calendar_sync'] ?? false;
        $client_id     = $this->settings['google_calendar_client_id'] ?? '';
        $client_secret = $this->settings['google_calendar_client_secret'] ?? '';

        return ( $customer_sync || $client_sync ) && ! empty( $client_id ) && ! empty( $client_secret );
    }

    public function save_settings(): void { /* ... */ }
    public function bulk_sync(): void { /* ... */ }
    public function deauthenticate(): void { /* ... */ }
    public function sync_order_to_calendar( $order_id ): void { /* ... */ }
    public function add_calendar_button( $text, $order ) { return $text; }
}