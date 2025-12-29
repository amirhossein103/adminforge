<?php
/**
 * Unified Cache Service
 *
 * Two-tier caching system (runtime + WordPress Object Cache)
 *
 * @package AdminForge
 * @since 2.0.0
 */

declare(strict_types=1);

namespace AdminForge\Core;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Cache class
 *
 * @since 2.0.0
 */
final class Cache
{
    /**
     * Runtime cache storage
     *
     * @var array<string, mixed>
     */
    private static array $runtime = [];

    /**
     * Cache group for WordPress Object Cache
     *
     * @var string
     */
    private static string $group = 'adminforge';

    /**
     * Cache statistics
     *
     * @var array<string, int>
     */
    private static array $stats = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'deletes' => 0,
    ];

    /**
     * Get value from cache
     *
     * @param string $key Cache key
     * @param string|null $group Custom cache group
     * @return mixed|null Cached value or null
     */
    public static function get(string $key, ?string $group = null)
    {
        $cacheKey = self::makeCacheKey($key, $group);

        // Check runtime cache first
        if (isset(self::$runtime[$cacheKey])) {
            self::$stats['hits']++;
            return self::$runtime[$cacheKey];
        }

        // Check WordPress Object Cache
        $value = wp_cache_get($key, $group ?? self::$group);

        if ($value !== false) {
            // Store in runtime cache for subsequent requests
            self::$runtime[$cacheKey] = $value;
            self::$stats['hits']++;
            return $value;
        }

        self::$stats['misses']++;
        return null;
    }

    /**
     * Set value in cache
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $expiration Expiration in seconds (default: 1 hour)
     * @param string|null $group Custom cache group
     * @return bool Success status
     */
    public static function set(string $key, $value, int $expiration = Constants::DEFAULT_CACHE_DURATION, ?string $group = null): bool
    {
        $cacheKey = self::makeCacheKey($key, $group);

        // Store in runtime cache
        self::$runtime[$cacheKey] = $value;

        // Store in WordPress Object Cache
        $result = wp_cache_set($key, $value, $group ?? self::$group, $expiration);

        if ($result) {
            self::$stats['sets']++;
        }

        return $result;
    }

    /**
     * Delete value from cache
     *
     * @param string $key Cache key
     * @param string|null $group Custom cache group
     * @return bool Success status
     */
    public static function delete(string $key, ?string $group = null): bool
    {
        $cacheKey = self::makeCacheKey($key, $group);

        // Remove from runtime cache
        unset(self::$runtime[$cacheKey]);

        // Remove from WordPress Object Cache
        $result = wp_cache_delete($key, $group ?? self::$group);

        if ($result) {
            self::$stats['deletes']++;
        }

        return $result;
    }

    /**
     * Remember value in cache (get or compute and cache)
     *
     * @param string $key Cache key
     * @param callable $callback Callback to compute value if not cached
     * @param int $expiration Expiration in seconds
     * @param string|null $group Custom cache group
     * @return mixed Cached or computed value
     */
    public static function remember(string $key, callable $callback, int $expiration = Constants::DEFAULT_CACHE_DURATION, ?string $group = null)
    {
        $value = self::get($key, $group);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        self::set($key, $value, $expiration, $group);

        return $value;
    }

    /**
     * Flush cache for specific group
     *
     * @param string|null $group Cache group (null = all runtime cache)
     * @return bool Success status
     */
    public static function flush(?string $group = null): bool
    {
        if ($group === null) {
            // Clear all runtime cache
            self::$runtime = [];
            return true;
        }

        // Clear runtime cache for specific group
        $prefix = ($group ?? self::$group) . ':';
        foreach (self::$runtime as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                unset(self::$runtime[$key]);
            }
        }

        // Note: WordPress doesn't have group flush, so we can't clear WP Object Cache by group
        return true;
    }

    /**
     * Get cache statistics
     *
     * @return array<string, mixed> Statistics
     */
    public static function getStats(): array
    {
        $total = self::$stats['hits'] + self::$stats['misses'];
        $hitRate = $total > 0 ? round((self::$stats['hits'] / $total) * 100, 2) : 0;

        return [
            'hits' => self::$stats['hits'],
            'misses' => self::$stats['misses'],
            'sets' => self::$stats['sets'],
            'deletes' => self::$stats['deletes'],
            'hit_rate' => $hitRate . '%',
            'runtime_size' => count(self::$runtime),
            'memory_usage' => self::getMemoryUsage(),
        ];
    }

    /**
     * Make cache key with group prefix
     *
     * @param string $key Cache key
     * @param string|null $group Cache group
     * @return string Full cache key
     */
    private static function makeCacheKey(string $key, ?string $group = null): string
    {
        return ($group ?? self::$group) . ':' . $key;
    }

    /**
     * Get memory usage of runtime cache
     *
     * @return int Memory usage in bytes
     */
    private static function getMemoryUsage(): int
    {
        return strlen(serialize(self::$runtime));
    }

    /**
     * Reset cache statistics
     *
     * @return void
     */
    public static function resetStats(): void
    {
        self::$stats = [
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
            'deletes' => 0,
        ];
    }
}
