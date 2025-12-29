<?php
/**
 * Data Hydrator Class
 *
 * Injects custom field data into frontend for optimal performance
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Helpers;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * DataHydrator class
 */
final class DataHydrator
{
    /**
     * Registered field keys to hydrate
     *
     * @var array<string>
     */
    private static array $fields = [];

    /**
     * Hydrated data
     *
     * @var array<int, array<string, mixed>>
     */
    private static array $data = [];

    /**
     * Register fields to hydrate
     *
     * @param array<string> $fields Field keys
     * @return void
     */
    public static function registerFields(array $fields): void
    {
        self::$fields = array_merge(self::$fields, $fields);
        self::$fields = array_unique(self::$fields);
    }

    /**
     * Hydrate data for current post
     *
     * @param int|null $postId Post ID (null = current post)
     * @return void
     */
    public static function hydrate(?int $postId = null): void
    {
        if ($postId === null) {
            $postId = get_the_ID();
        }

        if (!$postId) {
            return;
        }

        // Use MetaHelper for optimized retrieval
        if (class_exists('AdminForge\Helpers\MetaHelper')) {
            MetaHelper::loadAll($postId);
            $data = MetaHelper::getMultiple($postId, self::$fields);
        } else {
            // Fallback to standard get_post_meta
            $data = [];
            foreach (self::$fields as $field) {
                $data[$field] = get_post_meta($postId, $field, true);
            }
        }

        self::$data[$postId] = $data;
    }

    /**
     * Hydrate data for multiple posts
     *
     * @param array<int> $postIds Post IDs
     * @return void
     */
    public static function hydrateMultiple(array $postIds): void
    {
        foreach ($postIds as $postId) {
            self::hydrate($postId);
        }
    }

    /**
     * Get hydrated data for a post
     *
     * @param int|null $postId Post ID
     * @param string|null $key Specific field key (null = all)
     * @return mixed
     */
    public static function get(?int $postId = null, ?string $key = null)
    {
        if ($postId === null) {
            $postId = get_the_ID();
        }

        if (!isset(self::$data[$postId])) {
            self::hydrate($postId);
        }

        if ($key === null) {
            return self::$data[$postId] ?? [];
        }

        return self::$data[$postId][$key] ?? null;
    }

    /**
     * Inject data into JavaScript global object
     *
     * @param string $objectName Global JS object name
     * @param int|null $postId Post ID
     * @return void
     */
    public static function injectToJS(string $objectName = 'adminforgeData', ?int $postId = null): void
    {
        if ($postId === null) {
            $postId = get_the_ID();
        }

        if (!$postId) {
            return;
        }

        if (!isset(self::$data[$postId])) {
            self::hydrate($postId);
        }

        $data = self::$data[$postId] ?? [];

        wp_localize_script('adminforge-frontend', $objectName, [
            'postId' => $postId,
            'fields' => $data,
        ]);
    }

    /**
     * Inject data into PHP global
     *
     * @param int|null $postId Post ID
     * @return void
     */
    public static function injectToGlobal(?int $postId = null): void
    {
        if ($postId === null) {
            $postId = get_the_ID();
        }

        if (!$postId) {
            return;
        }

        if (!isset(self::$data[$postId])) {
            self::hydrate($postId);
        }

        $GLOBALS['adminforge_data'] = self::$data[$postId] ?? [];
    }

    /**
     * Auto-hydrate in WordPress loop
     *
     * @return void
     */
    public static function autoHydrateLoop(): void
    {
        add_action('the_post', function ($post) {
            if (isset($post->ID)) {
                self::hydrate($post->ID);
            }
        });
    }

    /**
     * Preload data for query
     *
     * @param \WP_Query $query WordPress query object
     * @return void
     */
    public static function preloadQuery(\WP_Query $query): void
    {
        if (!$query->have_posts()) {
            return;
        }

        $postIds = wp_list_pluck($query->posts, 'ID');

        // Use MetaHelper for batch loading
        if (class_exists('AdminForge\Helpers\MetaHelper')) {
            MetaHelper::preload($postIds);
        }

        // Hydrate all posts
        self::hydrateMultiple($postIds);
    }

    /**
     * Clear hydrated data
     *
     * @param int|null $postId Post ID (null = clear all)
     * @return void
     */
    public static function clear(?int $postId = null): void
    {
        if ($postId === null) {
            self::$data = [];
        } else {
            unset(self::$data[$postId]);
        }
    }

    /**
     * Get all registered fields
     *
     * @return array<string>
     */
    public static function getRegisteredFields(): array
    {
        return self::$fields;
    }

    /**
     * Get statistics
     *
     * @return array<string, int>
     */
    public static function getStats(): array
    {
        return [
            'registered_fields' => count(self::$fields),
            'hydrated_posts' => count(self::$data),
            'total_values' => array_sum(array_map('count', self::$data)),
        ];
    }
}
