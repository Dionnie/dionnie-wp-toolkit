<?php
declare(strict_types=1);

namespace DionnieWPToolkit\Core\Modules\LoginCustomizer;

use DionnieWPToolkit\Core\Interfaces\Registerable;

class LoginCustomizer implements Registerable {

    /**
     * Register WordPress hooks for the login page.
     */


     public function register(): void {
 // Hook into scripts to load our CSS over the default WP login CSS
        add_action('login_enqueue_scripts', [$this, 'enqueue_custom_styles']);
        
        // Dequeue unnecessary assets to improve performance
        add_action('login_enqueue_scripts', [$this, 'dequeue_unnecessary_assets'], 100);
        
        // Inject layout wrappers before and after the default WP login box
        add_action('login_header', [$this, 'inject_hero_section']);
        add_action('login_footer', [$this, 'close_login_container']);
        
        // Inject our custom logo/name inside the login box (overriding the WP logo)
        add_filter('login_message', [$this, 'inject_custom_logo']);

     }



    /**
     * Output custom CSS to restyle standard WordPress form fields to match our design.
     */
    public function enqueue_custom_styles(): void {
        ?>
        <style>
            /* Overriding default WP Body */
            html body.login { 
                margin: 0; 
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                background-image: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?q=80&w=2070');
                background-size: cover;
                background-position: center;
                min-height: 100vh; 
                display: flex; 
                align-items: center; 
                justify-content: center;
                padding: 2rem;
                box-sizing: border-box;
            }
            
            /* Custom Layout Grid wrapping the default #login box */
            #dionnie-login-wrapper {
                display: grid;
                grid-template-columns: 1fr 1fr;
                width: 100%;
                max-width: 1000px;
                min-height: 600px;
                background: rgba(30, 30, 30, 0.25);
                backdrop-filter: blur(20px) saturate(1.5);
                -webkit-backdrop-filter: blur(20px) saturate(1.5);
                border-radius: 20px;
                box-shadow: 0 20px 50px rgba(0,0,0,0.3);
                border: 1px solid rgba(255, 255, 255, 0.1);
                overflow: hidden;
            }

            #dionnie-login-wrapper .login-hero {
                display: flex;
                flex-direction: column;
                justify-content: center;
                padding: 60px;
                color: #fff;
                text-shadow: 0 2px 10px rgba(0,0,0,0.3);
            }

            #dionnie-login-wrapper .login-hero h1 {
                font-size: 3.5rem;
                font-weight: 800;
                line-height: 1.2;
                margin-bottom: 1rem;
                color: #fff;
            }

            #dionnie-login-wrapper .login-hero p {
                font-size: 1.2rem;
                line-height: 1.6;
                max-width: 400px;
                opacity: 0.9;
            }

            #dionnie-login-wrapper .login-box-wrapper { 
                background: rgba(255, 255, 255, 0.98); 
                padding: 50px; 
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            /* Reskinning the WP Default Elements */
            #dionnie-login-wrapper #login {
                width: 100%;
                padding: 0;
                margin: 0;
            }
            
            #dionnie-login-wrapper h1 a {
                display: none; /* Hide default WP logo */
            }

            #dionnie-login-wrapper .wp-hide-pw {
                top: 8.5px;
                right: 3px;
                border-radius: 4px;
                outline: none;
            }

            #dionnie-login-wrapper .login-logo { 
                text-align: center; 
                font-size: 24px; 
                font-weight: bold; 
                margin-bottom: 24px; 
                color: #333; 
            }

            #dionnie-login-wrapper form {
               display: inline-block;
                background: transparent;
                border: none;
                box-shadow: none;
                overflow: visible;
                margin: 0;
            }

              #dionnie-login-wrapper a { 
                white-space: nowrap;
              }

            #dionnie-login-wrapper label { 
                display: inline-block; 
                font-size: 14px; 
                margin-bottom: 8px; 
                color: #555; 
                font-weight: 600; 
            }

            #dionnie-login-wrapper input[type="text"], 
            #dionnie-login-wrapper input[type="password"], 
            #dionnie-login-wrapper input[type="email"] { 
                width: 100%; 
                padding: 12px; 
                border: 1px solid #d1d5db; 
                border-radius: 8px; 
                box-sizing: border-box; 
                background: #f9fafb; 
                transition: all 0.2s ease; 
                margin-top: 5px;
                margin-bottom: 20px;
                font-size: 16px;
                box-shadow: none;
            }

            #dionnie-login-wrapper input[type="text"]:focus, 
            #dionnie-login-wrapper input[type="password"]:focus, 
            #dionnie-login-wrapper input[type="email"]:focus { 
                outline: none; 
                border-color: #4338ca; 
                background: #fff; 
                box-shadow: 0 0 0 3px rgba(67, 56, 202, 0.2); 
            }

            #dionnie-login-wrapper .button-primary { 
                width: 100%; 
                padding: 14px; 
                background: #4338ca; 
                color: white; 
                border: none; 
                border-radius: 8px; 
                font-size: 16px; 
                font-weight: 600; 
                cursor: pointer; 
                transition: all 0.3s; 
                margin-top: 10px; 
                height: auto;
                line-height: normal;
                text-shadow: none;
            }

            #dionnie-login-wrapper .button-primary:hover { 
                background: #3730a3; 
                transform: translateY(-2px); 
                box-shadow: 0 4px 10px rgba(0,0,0,0.1); 
            }

            #dionnie-login-wrapper .forgetmenot {
                float: none;
                margin-bottom: 20px;
            }

            #dionnie-login-wrapper .submit {
                float: none;
                clear: both;
            }

            #dionnie-login-wrapper #login_error, 
            #dionnie-login-wrapper .message { 
                background: #ffebee; 
                color: #d32f2f; 
                padding: 10px; 
                border-radius: 4px; 
                font-size: 14px; 
                margin-bottom: 20px; 
                border-left: 4px solid #d32f2f; 
                border-right: none;
                border-top: none;
                border-bottom: none;
                box-shadow: none;
            }

            #dionnie-login-wrapper .message { 
                color: #155724; 
                background: #d4edda; 
                border-left: 4px solid #155724; 
            }

            #dionnie-login-wrapper #nav, 
            #dionnie-login-wrapper #backtoblog { 
                margin: 20px 0 0 0; 
                padding: 0; 
                text-align: center; 
                font-size: 14px; 
            }

            #dionnie-login-wrapper #nav a, 
            #dionnie-login-wrapper #backtoblog a { 
                display: inline-block;
                color: #4338ca; 
                text-decoration: none; 
                transition: color 0.3s;
            }

            #dionnie-login-wrapper #nav a:hover, 
            #dionnie-login-wrapper #backtoblog a:hover { 
                text-decoration: underline; 
                color: #3730a3;
            }
            
            #dionnie-login-wrapper .language-switcher {
                margin-top: 20px;
                text-align: center;
            }

            /* Mobile responsiveness */
            @media (max-width: 900px) {
                html body.login { align-items: flex-start; padding-top: 5vh; }
                #dionnie-login-wrapper { 
                    grid-template-columns: 1fr; 
                    max-width: 450px; 
                    min-height: auto; 
                    background: transparent; 
                    backdrop-filter: none; 
                    -webkit-backdrop-filter: none; 
                    border: none; 
                    box-shadow: none; 
                }
                #dionnie-login-wrapper .login-hero { display: none; }
                #dionnie-login-wrapper .login-box-wrapper { 
                    border-radius: 20px; 
                    box-shadow: 0 20px 50px rgba(0,0,0,0.2); 
                }
            }
        </style>
        <?php
    }

    /**
     * Dequeue unnecessary scripts and styles to improve page load speed based on Lighthouse recommendations.
     */
    public function dequeue_unnecessary_assets(): void {
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
    public function inject_hero_section(): void {
        $action = $_GET['action'] ?? 'login';
        $hero_title = __('Welcome Back', 'dionnie-wp');
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
    public function close_login_container(): void {
        echo '  </main>'; // close .login-box-wrapper
        echo '</div>';   // close .login-container
    }

    /**
     * Injects the site name as a custom text logo right where standard WP error/info messages appear.
     * 
     * @param string $message The standard WordPress message HTML
     * @return string Modified HTML
     */
    public function inject_custom_logo(string $message): string {
        $logo = '<div class="login-logo">' . esc_html(get_bloginfo('name')) . '</div>';
        return $logo . $message;
    }
}
