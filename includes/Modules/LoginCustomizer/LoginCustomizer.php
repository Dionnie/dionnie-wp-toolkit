<?php

declare(strict_types=1);

namespace DionnieWPToolkit\Core\Modules\LoginCustomizer;

use DionnieWPToolkit\Core\Interfaces\Registerable;
use DionnieWPToolkit\Core\Helpers\ViteManifestHelper;

class LoginCustomizer implements Registerable
{
    public function register(): void
    {


        // Dequeue unnecessary assets to improve performance
        //  add_action('login_enqueue_scripts', [$this, 'dequeue_unnecessary_assets'], 100);

        // Inject layout wrappers before and after the default WP login box
        add_action('login_header', [$this, 'inject_hero_section']);
        add_action('login_footer', [$this, 'close_login_container']);

        // Inject our custom logo/name inside the login box (overriding the WP logo)
        add_filter('login_message', [$this, 'inject_custom_logo']);
    }




    /**
     * Dequeue unnecessary scripts and styles to improve page load speed based on Lighthouse recommendations.
     */
    public function dequeue_unnecessary_assets(): void
    {
        // Dequeue unused CSS
        wp_dequeue_style('dashicons');
        wp_dequeue_style('forms');
        wp_dequeue_style('wp-block-library'); // Gutenberg default block styles

        // Dequeue unused JS
        wp_dequeue_script('jquery');
        wp_dequeue_script('jquery-core');
        wp_dequeue_script('jquery-migrate');

        // Dequeue password strength meter (zxcvbn) unless we are on a password reset or registration page
        $action = $_GET['action'] ?? 'login';
        if (!in_array($action, ['resetpass', 'rp', 'register'], true)) {
            wp_dequeue_script('zxcvbn-async');
            wp_deregister_script('zxcvbn-async');
            wp_dequeue_script('password-strength-meter');
            wp_deregister_script('password-strength-meter');
        }
    }

    /**
     * Opens the grid container and outputs the left-hand hero section.
     * The default WP form (#login) will fall into the right-hand column natively.
     */
    public function inject_hero_section(): void
    {
        $action = $_GET['action'] ?? 'login';
        $hero_title = __('Welcome Back Awesome!!', 'dionnie-wp');
        $hero_subtitle = __('Your journey continues here. Log in to access your dashboard and manage your site.', 'dionnie-wp');

        if ($action === 'register') {
            $hero_title = __('Join Our Community', 'dionnie-wp');
            $hero_subtitle = __('Create an account to get started. It\'s fast and easy.', 'dionnie-wp');
        } elseif (in_array($action, ['lostpassword', 'retrievepassword'], true)) {
            $hero_title = __('Forgot Your Password?', 'dionnie-wp');
            $hero_subtitle = __('No worries. Enter your email and we\'ll send you a reset link.', 'dionnie-wp');
        } elseif (in_array($action, ['resetpass', 'rp'], true)) {
            $hero_title = __('Reset Password', 'dionnie-wp');
            $hero_subtitle = __('Enter your new password below.', 'dionnie-wp');
        }

        echo '<div id="dionnie-login-wrapper" class="login-container">';
        echo '  <div class="login-hero">';
        echo '      <h1>' . esc_html($hero_title) . '</h1>';
        echo '      <p>' . esc_html($hero_subtitle) . '</p>';
        echo '  </div>';
        echo '  <main class="login-box-wrapper">';
    }

    /**
     * Closes the structural wrappers opened in inject_hero_section().
     */
    public function close_login_container(): void
    {
        echo '  </main>'; // close .login-box-wrapper
        echo '</div>';   // close .login-container
    }

    /**
     * Injects the site name as a custom text logo right where standard WP error/info messages appear.
     * * @param string $message The standard WordPress message HTML
     * @return string Modified HTML
     */
    public function inject_custom_logo(string $message): string
    {
        $logo = '<div class="login-logo">' . esc_html(get_bloginfo('name')) . '</div>';
        return $logo . $message;
    }
}
