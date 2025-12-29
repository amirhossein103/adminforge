<?php
/**
 * Meta Helper Class with Static Cache
 *
 * High-performance meta retrieval with O(1) access complexity
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Helpers;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * MetaHelper class - Singleton pattern with static cache
 */
final class MetaHelper
{
    /**
     * Singleton instance
     *
     * @var MetaHelper|null
     */
    private static ?MetaHelper $instance = null;

    /**
     * Cache storage for all meta data
     * Key: post_id, Value: array of all meta
     *
     * @var array<int, array<string, mixed>>
     */
    private static array $cache = [];

    /**
     * Track which posts have been loaded
     *
     * @var array<int, bool>
     */
    private static array $loaded = [];

    /**
     * Private constructor (Singleton)
     */
    private function __construct()
    {
    }

    /**
     * Get singleton instance
     *
     * @return MetaHelper
     */
    public static function getInstance(): MetaHelper
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Load ALL meta for a post at once
     * This is the KEY to performance - single DB query instead of multiple
     *
     * @param int $postId Post ID
     * @return bool Success status
     */
    public static function loadAll(int $postId): bool
    {
        // Already loaded? Return early
        if (isset(self::$loaded[$postId])) {
            return true;
        }

        // Get ALL meta in one query
        $allMeta = get_post_meta($postId);

        if (empty($allMeta) || !is_array($allMeta)) {
            self::$loaded[$postId] = true;
            return false;
        }

        // Store in cache - unserialize single values
        self::$cache[$postId] = [];

        foreach ($allMeta as $key => $values) {
            // WordPress stores meta as arrays, get first value
            self::$cache[$postId][$key] = isset($values[0]) ? maybe_unserialize($values[0]) : null;
        }

        // Mark as loaded
        self::$loaded[$postId] = true;

        return true;
    }

    /**
     * Get meta value from cache (O(1) complexity)
     *
     * @param int $postId Post ID
     * @param string $key Meta key
     * @param mixed $default Default value
     * @return mixed Meta value or default
     */
    public static function get(int $postId, string $key, $default = null)
    {
        // Load all meta if not loaded yet
        if (!isset(self::$loaded[$postId])) {
            self::loadAll($postId);
        }

        // Return from cache (O(1) access)
        return self::$cache[$postId][$key] ?? $default;
    }

    /**
     * Check if meta exists
     *
     * @param int $postId Post ID
     * @param string $key Meta key
     * @return bool
     */
    public static function has(int $postId, string $key): bool
    {
        if (!isset(self::$loaded[$postId])) {
            self::loadAll($postId);
        }

        return isset(self::$cache[$postId][$key]);
    }

    /**
     * Get multiple meta values at once
     *
     * @param int $postId Post ID
     * @param array<string> $keys Meta keys
     * @return array<string, mixed> Key-value pairs
     */
    public static function getMultiple(int $postId, array $keys): array
    {
        if (!isset(self::$loaded[$postId])) {
            self::loadAll($postId);
        }

        $result = [];

        foreach ($keys as $key) {
            $result[$key] = self::$cache[$postId][$key] ?? null;
        }

        return $result;
    }

    /**
     * Get all meta for a post
     *
     * @param int $postId Post ID
     * @return array<string, mixed> All meta data
     */
    public static function getAll(int $postId): array
    {
        if (!isset(self::$loaded[$postId])) {
            self::loadAll($postId);
        }

        return self::$cache[$postId] ?? [];
    }

    /**
     * Update cache when meta is updated
     *
     * @param int $postId Post ID
     * @param string $key Meta key
     * @param mixed $value Meta value
     * @return void
     */
    public static function updateCache(int $postId, string $key, $value): void
    {
        if (!isset(self::$cache[$postId])) {
            self::$cache[$postId] = [];
        }

        self::$cache[$postId][$key] = $value;
        self::$loaded[$postId] = true;
    }

    /**
     * Remove from cache
     *
     * @param int $postId Post ID
     * @param string|null $key Meta key (null = remove all)
     * @return void
     */
    public static function removeCache(int $postId, ?string $key = null): void
    {
        if ($key === null) {
            // Remove all cache for this post
            unset(self::$cache[$postId], self::$loaded[$postId]);
        } else {
            // Remove specific key
            unset(self::$cache[$postId][$key]);
        }
    }

    /**
     * Clear all cache
     *
     * @return void
     */
    public static function clearCache(): void
    {
        self::$cache = [];
        self::$loaded = [];
    }

    /**
     * Get cache statistics
     *
     * @return array<string, int>
     */
    public static function getStats(): array
    {
        // Calculate memory usage more efficiently without serializing entire cache
        $memoryUsage = 0;
        foreach (self::$cache as $postCache) {
            foreach ($postCache as $value) {
                if (is_string($value)) {
                    $memoryUsage += strlen($value);
                } elseif (is_array($value)) {
                    $memoryUsage += strlen(wp_json_encode($value));
                } else {
                    $memoryUsage += 8; // Approximate size for scalar values
                }
            }
        }

        return [
            'posts_cached' => count(self::$loaded),
            'total_meta_keys' => array_sum(array_map('count', self::$cache)),
            'memory_usage' => $memoryUsage,
        ];
    }

    /**
     * Preload meta for multiple posts
     * Useful for loops
     *
     * @param array<int> $postIds Array of post IDs
     * @return void
     */
    public static function preload(array $postIds): void
    {
        foreach ($postIds as $postId) {
            if (!isset(self::$loaded[$postId])) {
                self::loadAll($postId);
            }
        }
    }

    /**
     * Hook into WordPress to auto-update cache
     *
     * @return void
     */
    public static function registerHooks(): void
    {
        // Update cache when meta is updated
        add_action('updated_post_meta', function ($metaId, $postId, $metaKey, $metaValue) {
            self::updateCache((int) $postId, $metaKey, $metaValue);
        }, 10, 4);

        // Remove from cache when meta is deleted
        add_action('deleted_post_meta', function ($metaIds, $postId, $metaKey) {
            if (is_array($metaIds)) {
                foreach ($metaIds as $metaId) {
                    self::removeCache((int) $postId, $metaKey);
                }
            }
        }, 10, 3);

        // Clear cache when post is deleted
        add_action('delete_post', function ($postId) {
            self::removeCache((int) $postId);
        });
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

// Auto-register hooks
add_action('init', [MetaHelper::class, 'registerHooks']);
