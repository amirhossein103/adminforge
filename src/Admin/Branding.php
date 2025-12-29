<?php
/**
 * Admin Branding Class
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Admin;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

use AdminForge\Core\Config;

/**
 * Branding class for customizing admin appearance
 */
final class Branding
{
    /**
     * Custom colors
     *
     * @var array<string, string>
     */
    private array $colors = [];

    /**
     * Custom logo URL
     *
     * @var string
     */
    private string $logoUrl = '';

    /**
     * Custom login logo URL
     *
     * @var string
     */
    private string $loginLogoUrl = '';

    /**
     * Admin footer text
     *
     * @var string
     */
    private string $footerText = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->loadFromConfig();
    }

    /**
     * Load branding settings from config
     *
     * @return void
     */
    private function loadFromConfig(): void
    {
        if (!Config::get('branding.enabled', false)) {
            return;
        }

        $this->colors = Config::get('ui', []);
        $this->logoUrl = Config::get('branding.logo', '');
        $this->loginLogoUrl = Config::get('branding.login_logo', '');
        $this->footerText = Config::get('branding.admin_footer_text', '');
    }

    /**
     * Register branding hooks
     *
     * @return void
     */
    public function register(): void
    {
        // Custom admin colors
        if (!empty($this->colors)) {
            add_action('admin_head', [$this, 'injectCustomColors']);
        }

        // Custom login logo
        if (!empty($this->loginLogoUrl)) {
            add_action('login_head', [$this, 'customLoginLogo']);
            add_filter('login_headerurl', [$this, 'customLoginLogoUrl']);
        }

        // Custom admin footer
        if (!empty($this->footerText)) {
            add_filter('admin_footer_text', [$this, 'customAdminFooter']);
        }

        // Remove WordPress version from footer
        add_filter('update_footer', '__return_empty_string', 99);
    }

    /**
     * Inject custom CSS colors
     *
     * @return void
     */
    public function injectCustomColors(): void
    {
        $primary = $this->colors['primary_color'] ?? '#0073aa';
        $secondary = $this->colors['secondary_color'] ?? '#00a0d2';
        $success = $this->colors['success_color'] ?? '#46b450';
        $error = $this->colors['error_color'] ?? '#dc3232';

        ?>
        <style>
            :root {
                --af-primary: <?php echo esc_attr($primary); ?>;
                --af-secondary: <?php echo esc_attr($secondary); ?>;
                --af-success: <?php echo esc_attr($success); ?>;
                --af-error: <?php echo esc_attr($error); ?>;
            }

            /* WordPress admin bar */
            #wpadminbar {
                background: <?php echo esc_attr($primary); ?>;
            }

            /* Buttons */
            .wp-core-ui .button-primary {
                background: <?php echo esc_attr($primary); ?>;
                border-color: <?php echo esc_attr($primary); ?>;
            }

            .wp-core-ui .button-primary:hover {
                background: <?php echo esc_attr($secondary); ?>;
                border-color: <?php echo esc_attr($secondary); ?>;
            }

            /* Links */
            #adminmenu a:hover,
            #adminmenu li.menu-top:hover,
            #adminmenu li.opensub > a.menu-top,
            #adminmenu li > a.menu-top:focus {
                color: #fff;
                background-color: <?php echo esc_attr($primary); ?>;
            }
        </style>
        <?php
    }

    /**
     * Custom login logo
     *
     * @return void
     */
    public function customLoginLogo(): void
    {
        ?>
        <style>
            #login h1 a {
                background-image: url('<?php echo esc_url($this->loginLogoUrl); ?>');
                background-size: contain;
                width: 100%;
                height: 80px;
            }
        </style>
        <?php
    }

    /**
     * Custom login logo URL
     *
     * @return string
     */
    public function customLoginLogoUrl(): string
    {
        return home_url();
    }

    /**
     * Custom admin footer text
     *
     * @return string
     */
    public function customAdminFooter(): string
    {
        return $this->footerText;
    }

    /**
     * Set custom colors
     *
     * @param array<string, string> $colors Colors array
     * @return self
     */
    public function setColors(array $colors): self
    {
        $this->colors = $colors;
        return $this;
    }

    /**
     * Set logo URL
     *
     * @param string $url Logo URL
     * @return self
     */
    public function setLogo(string $url): self
    {
        $this->logoUrl = $url;
        return $this;
    }

    /**
     * Set login logo URL
     *
     * @param string $url Login logo URL
     * @return self
     */
    public function setLoginLogo(string $url): self
    {
        $this->loginLogoUrl = $url;
        return $this;
    }

    /**
     * Set footer text
     *
     * @param string $text Footer text
     * @return self
     */
    public function setFooterText(string $text): self
    {
        $this->footerText = $text;
        return $this;
    }

    /**
     * Hide WordPress logo from admin bar
     *
     * @return void
     */
    public static function hideWordPressLogo(): void
    {
        add_action('admin_bar_menu', function ($wp_admin_bar) {
            $wp_admin_bar->remove_node('wp-logo');
        }, 999);
    }

    /**
     * Custom admin menu order
     *
     * @param array<int, string> $menu_order Menu order
     * @return void
     */
    public static function customMenuOrder(array $menu_order): void
    {
        add_filter('custom_menu_order', '__return_true');
        add_filter('menu_order', function () use ($menu_order) {
            return $menu_order;
        });
    }
}

// Auto-register if enabled
if (Config::get('branding.enabled', false)) {
    $branding = new Branding();
    $branding->register();
}
