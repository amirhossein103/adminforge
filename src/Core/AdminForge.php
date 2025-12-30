<?php
/**
 * Main AdminForge Class (Singleton Pattern)
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Core;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

use AdminForge\Core\Config;

/**
 * AdminForge Main Class
 */
final class AdminForge
{
    /**
     * Singleton instance
     *
     * @var AdminForge|null
     */
    private static ?AdminForge $instance = null;

    /**
     * Configuration instance
     *
     * @var Config
     */
    private Config $config;

    /**
     * Private constructor (Singleton)
     */
    private function __construct()
    {
        $this->config = new Config();

        $this->initHooks();
    }

    /**
     * Get singleton instance
     *
     * @return AdminForge
     */
    public static function getInstance(): AdminForge
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Initialize WordPress hooks
     *
     * @return void
     */
    private function initHooks(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('init', [$this, 'loadTextDomain']);
    }

    /**
     * Load plugin text domain for translations
     *
     * @return void
     */
    private function loadTextDomain(): void
    {
        load_plugin_textdomain(
            'adminforge',
            false,
            dirname(ADMINFORGE_BASENAME) . '/languages'
        );
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    private function enqueueAssets(string $hook): void
    {
        $version = ADMINFORGE_VERSION;

        // Use minified assets in production
        $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

        // Enqueue CSS
        wp_enqueue_style(
            'adminforge-admin',
            ADMINFORGE_URL . "assets/css/admin{$suffix}.css",
            ['wp-color-picker'],
            $version
        );

        // Enqueue JS
        wp_enqueue_script(
            'adminforge-admin',
            ADMINFORGE_URL . "assets/js/admin{$suffix}.js",
            ['jquery', 'jquery-ui-sortable', 'wp-color-picker'],
            $version,
            true
        );

        // Localize script
        wp_localize_script('adminforge-admin', 'adminforge', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('adminforge_nonce'),
            'strings' => [
                'save' => __('Save', 'adminforge'),
                'saving' => __('Saving...', 'adminforge'),
                'saved' => __('Saved!', 'adminforge'),
                'error' => __('Error occurred', 'adminforge'),
            ],
        ]);
    }


    /**
     * Get config instance
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserializing
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
