<?php
/**
 * General Helper Class
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Helpers;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

use AdminForge\Core\ErrorHandler;

/**
 * Helper class with common utility functions
 */
final class Helper
{
    /**
     * Get all WordPress pages
     *
     * @param bool $addEmpty Add empty option
     * @return array<int|string, string>
     */
    public static function getPages(bool $addEmpty = true): array
    {
        $pages = [];

        if ($addEmpty) {
            $pages[0] = __('— Select Page —', 'adminforge');
        }

        $pagesList = get_pages();

        foreach ($pagesList as $page) {
            $pages[$page->ID] = $page->post_title;
        }

        return $pages;
    }

    /**
     * Get all WordPress menus
     *
     * @param bool $addEmpty Add empty option
     * @return array<int|string, string>
     */
    public static function getMenus(bool $addEmpty = true): array
    {
        $menus = [];

        if ($addEmpty) {
            $menus[0] = __('— Select Menu —', 'adminforge');
        }

        $menusList = wp_get_nav_menus();

        foreach ($menusList as $menu) {
            $menus[$menu->term_id] = $menu->name;
        }

        return $menus;
    }

    /**
     * Get all categories
     *
     * @param array<string, mixed> $args get_categories arguments
     * @param bool $addEmpty Add empty option
     * @return array<int|string, string>
     */
    public static function getCategories(array $args = [], bool $addEmpty = true): array
    {
        $categories = [];

        if ($addEmpty) {
            $categories[0] = __('— Select Category —', 'adminforge');
        }

        $defaultArgs = [
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ];

        $args = array_merge($defaultArgs, $args);
        $categoriesList = get_categories($args);

        foreach ($categoriesList as $category) {
            $categories[$category->term_id] = $category->name;
        }

        return $categories;
    }

    /**
     * Get all post types
     *
     * @param array<string, mixed> $args get_post_types arguments
     * @param bool $addEmpty Add empty option
     * @return array<string, string>
     */
    public static function getPostTypes(array $args = [], bool $addEmpty = true): array
    {
        $postTypes = [];

        if ($addEmpty) {
            $postTypes[''] = __('— Select Post Type —', 'adminforge');
        }

        $defaultArgs = [
            'public' => true,
        ];

        $args = array_merge($defaultArgs, $args);
        $postTypesList = get_post_types($args, 'objects');

        foreach ($postTypesList as $postType) {
            $postTypes[$postType->name] = $postType->label;
        }

        return $postTypes;
    }

    /**
     * Get all sidebars
     *
     * @param bool $addEmpty Add empty option
     * @return array<string, string>
     */
    public static function getSidebars(bool $addEmpty = true): array
    {
        global $wp_registered_sidebars;

        $sidebars = [];

        if ($addEmpty) {
            $sidebars[''] = __('— Select Sidebar —', 'adminforge');
        }

        foreach ($wp_registered_sidebars as $sidebar) {
            $sidebars[$sidebar['id']] = $sidebar['name'];
        }

        return $sidebars;
    }

    /**
     * Get all users
     *
     * @param array<string, mixed> $args get_users arguments
     * @param bool $addEmpty Add empty option
     * @return array<int|string, string>
     */
    public static function getUsers(array $args = [], bool $addEmpty = true): array
    {
        $users = [];

        if ($addEmpty) {
            $users[0] = __('— Select User —', 'adminforge');
        }

        $defaultArgs = [
            'orderby' => 'display_name',
            'order' => 'ASC',
        ];

        $args = array_merge($defaultArgs, $args);
        $usersList = get_users($args);

        foreach ($usersList as $user) {
            $users[$user->ID] = $user->display_name;
        }

        return $users;
    }

    /**
     * Get taxonomy terms
     *
     * @param string $taxonomy Taxonomy name
     * @param array<string, mixed> $args get_terms arguments
     * @param bool $addEmpty Add empty option
     * @return array<int|string, string>
     */
    public static function getTerms(string $taxonomy, array $args = [], bool $addEmpty = true): array
    {
        $terms = [];

        if ($addEmpty) {
            $terms[0] = __('— Select Term —', 'adminforge');
        }

        $defaultArgs = [
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ];

        $args = array_merge($defaultArgs, $args);
        $termsList = get_terms($args);

        if (!is_wp_error($termsList)) {
            foreach ($termsList as $term) {
                $terms[$term->term_id] = $term->name;
            }
        }

        return $terms;
    }


    /**
     * Check if current page is in admin
     *
     * @return bool
     */
    public static function isAdmin(): bool
    {
        return is_admin() && !wp_doing_ajax();
    }

    /**
     * Check if doing AJAX
     *
     * @return bool
     */
    public static function isAjax(): bool
    {
        return wp_doing_ajax();
    }

    /**
     * Get current URL
     *
     * @return string
     */
    public static function getCurrentUrl(): string
    {
        global $wp;
        return home_url(add_query_arg([], $wp->request));
    }
}
