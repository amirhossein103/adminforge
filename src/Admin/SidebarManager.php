<?php
/**
 * Sidebar Manager Class
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Admin;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * SidebarManager class for custom sidebar registration
 */
final class SidebarManager
{
    /**
     * Registered sidebars
     *
     * @var array<string, array<string, mixed>>
     */
    private static array $sidebars = [];

    /**
     * Register a sidebar
     *
     * @param string $id Sidebar ID
     * @param array<string, mixed> $args Sidebar arguments
     * @return bool
     */
    public static function register(string $id, array $args = []): bool
    {
        $defaults = [
            'name' => ucfirst(str_replace(['_', '-'], ' ', $id)),
            'id' => $id,
            'description' => '',
            'class' => '',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h3 class="widget-title">',
            'after_title' => '</h3>',
        ];

        $sidebar = array_merge($defaults, $args);

        // Store in our registry
        self::$sidebars[$id] = $sidebar;

        // Register with WordPress
        register_sidebar($sidebar);

        return true;
    }

    /**
     * Register multiple sidebars
     *
     * @param array<string, array<string, mixed>> $sidebars Array of sidebars
     * @return void
     */
    public static function registerMultiple(array $sidebars): void
    {
        foreach ($sidebars as $id => $args) {
            self::register($id, $args);
        }
    }

    /**
     * Unregister a sidebar
     *
     * @param string $id Sidebar ID
     * @return void
     */
    public static function unregister(string $id): void
    {
        unset(self::$sidebars[$id]);
        unregister_sidebar($id);
    }

    /**
     * Check if sidebar is registered
     *
     * @param string $id Sidebar ID
     * @return bool
     */
    public static function isRegistered(string $id): bool
    {
        return isset(self::$sidebars[$id]);
    }

    /**
     * Get sidebar arguments
     *
     * @param string $id Sidebar ID
     * @return array<string, mixed>|null
     */
    public static function get(string $id): ?array
    {
        return self::$sidebars[$id] ?? null;
    }

    /**
     * Get all registered sidebars
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getAll(): array
    {
        return self::$sidebars;
    }

    /**
     * Display sidebar
     *
     * @param string $id Sidebar ID
     * @return void
     */
    public static function display(string $id): void
    {
        if (is_active_sidebar($id)) {
            dynamic_sidebar($id);
        }
    }

    /**
     * Register default AdminForge sidebars
     *
     * @return void
     */
    public static function registerDefaults(): void
    {
        self::register('adminforge-sidebar', [
            'name' => __('AdminForge Sidebar', 'adminforge'),
            'description' => __('Default AdminForge sidebar', 'adminforge'),
        ]);

        self::register('adminforge-footer-1', [
            'name' => __('Footer Column 1', 'adminforge'),
            'description' => __('First footer widget area', 'adminforge'),
        ]);

        self::register('adminforge-footer-2', [
            'name' => __('Footer Column 2', 'adminforge'),
            'description' => __('Second footer widget area', 'adminforge'),
        ]);

        self::register('adminforge-footer-3', [
            'name' => __('Footer Column 3', 'adminforge'),
            'description' => __('Third footer widget area', 'adminforge'),
        ]);
    }
}

// Auto-register defaults on widgets_init
add_action('widgets_init', [SidebarManager::class, 'registerDefaults']);
