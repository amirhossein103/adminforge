<?php
/**
 * Editor Support Class (Gutenberg & Classic)
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Admin;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * EditorSupport class
 */
final class EditorSupport
{
    /**
     * Check if Gutenberg is active
     *
     * @return bool
     */
    public static function isGutenbergActive(): bool
    {
        return function_exists('register_block_type');
    }

    /**
     * Check if Classic Editor is active
     *
     * @return bool
     */
    public static function isClassicEditorActive(): bool
    {
        return !self::isGutenbergActive() || class_exists('Classic_Editor');
    }

    /**
     * Disable Gutenberg for specific post types
     *
     * @param array<string> $postTypes Post types to disable Gutenberg
     * @return void
     */
    public static function disableGutenberg(array $postTypes): void
    {
        add_filter('use_block_editor_for_post_type', function ($useBlockEditor, $postType) use ($postTypes) {
            if (in_array($postType, $postTypes, true)) {
                return false;
            }
            return $useBlockEditor;
        }, 10, 2);
    }

    /**
     * Enable Gutenberg for specific post types
     *
     * @param array<string> $postTypes Post types to enable Gutenberg
     * @return void
     */
    public static function enableGutenberg(array $postTypes): void
    {
        add_filter('use_block_editor_for_post_type', function ($useBlockEditor, $postType) use ($postTypes) {
            if (in_array($postType, $postTypes, true)) {
                return true;
            }
            return $useBlockEditor;
        }, 10, 2);
    }

    /**
     * Disable Gutenberg completely
     *
     * @return void
     */
    public static function disableGutenbergCompletely(): void
    {
        add_filter('use_block_editor_for_post_type', '__return_false', 100);
    }

    /**
     * Add custom Gutenberg block category
     *
     * @param string $slug Category slug
     * @param string $title Category title
     * @return void
     */
    public static function addBlockCategory(string $slug, string $title): void
    {
        add_filter('block_categories_all', function ($categories) use ($slug, $title) {
            return array_merge(
                $categories,
                [
                    [
                        'slug' => $slug,
                        'title' => $title,
                    ],
                ]
            );
        });
    }

    /**
     * Register custom Gutenberg block
     *
     * @param string $name Block name
     * @param array<string, mixed> $args Block arguments
     * @return void
     */
    public static function registerBlock(string $name, array $args = []): void
    {
        if (!self::isGutenbergActive()) {
            return;
        }

        register_block_type($name, $args);
    }

    /**
     * Enqueue Gutenberg editor assets
     *
     * @param string $handle Script handle
     * @param string $src Script source
     * @param array<string> $deps Dependencies
     * @return void
     */
    public static function enqueueBlockEditorAssets(string $handle, string $src, array $deps = []): void
    {
        add_action('enqueue_block_editor_assets', function () use ($handle, $src, $deps) {
            wp_enqueue_script($handle, $src, $deps, ADMINFORGE_VERSION, true);
        });
    }

    /**
     * Remove default block patterns
     *
     * @return void
     */
    public static function removeDefaultBlockPatterns(): void
    {
        remove_theme_support('core-block-patterns');
    }

    /**
     * Disable Gutenberg widgets
     *
     * @return void
     */
    public static function disableGutenbergWidgets(): void
    {
        add_filter('use_widgets_block_editor', '__return_false');
    }

    /**
     * Add theme support for Gutenberg features
     *
     * @param array<string> $features Features to add support for
     * @return void
     */
    public static function addThemeSupport(array $features): void
    {
        foreach ($features as $feature) {
            add_theme_support($feature);
        }
    }

    /**
     * Add Gutenberg color palette
     *
     * @param array<array<string, string>> $colors Array of colors
     * @return void
     */
    public static function addColorPalette(array $colors): void
    {
        add_theme_support('editor-color-palette', $colors);
    }

    /**
     * Add Gutenberg font sizes
     *
     * @param array<array<string, mixed>> $sizes Array of font sizes
     * @return void
     */
    public static function addFontSizes(array $sizes): void
    {
        add_theme_support('editor-font-sizes', $sizes);
    }

    /**
     * Get current editor type for post
     *
     * @param int|null $postId Post ID
     * @return string 'gutenberg' or 'classic'
     */
    public static function getCurrentEditor(?int $postId = null): string
    {
        if ($postId === null) {
            $postId = get_the_ID();
        }

        if (!$postId) {
            return 'classic';
        }

        $postType = get_post_type($postId);

        if (use_block_editor_for_post_type($postType)) {
            return 'gutenberg';
        }

        return 'classic';
    }

    /**
     * Check if current screen is using Gutenberg
     *
     * @return bool
     */
    public static function isGutenbergScreen(): bool
    {
        if (!is_admin()) {
            return false;
        }

        $screen = get_current_screen();

        if (!$screen) {
            return false;
        }

        return $screen->is_block_editor();
    }
}
