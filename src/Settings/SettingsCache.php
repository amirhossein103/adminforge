<?php
/**
 * Settings Cache Layer
 *
 * Wrapper around unified Cache service for settings-specific caching
 *
 * @package AdminForge
 * @since 2.0.0
 */

declare(strict_types=1);

namespace AdminForge\Settings;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

use AdminForge\Core\Cache as CoreCache;
use AdminForge\Core\Constants;

/**
 * SettingsCache class
 */
class SettingsCache
{
    /**
     * Cache group name
     */
    private const CACHE_GROUP = 'adminforge_settings';

    /**
     * Cache enabled
     *
     * @var bool
     */
    private bool $enabled = true;

    /**
     * Get value from cache
     *
     * @param string $key Cache key
     * @return mixed|null Value or null if not found
     */
    public function get(string $key)
    {
        if (!$this->enabled) {
            return null;
        }

        return CoreCache::get($key, self::CACHE_GROUP);
    }

    /**
     * Set value in cache
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $expiration Expiration time in seconds
     * @return bool Success status
     */
    public function set(string $key, $value, int $expiration = Constants::DEFAULT_CACHE_DURATION): bool
    {
        if (!$this->enabled) {
            return false;
        }

        return CoreCache::set($key, $value, $expiration, self::CACHE_GROUP);
    }

    /**
     * Remove value from cache
     *
     * @param string $key Cache key
     * @return bool Success status
     */
    public function remove(string $key): bool
    {
        return CoreCache::delete($key, self::CACHE_GROUP);
    }

    /**
     * Clear all caches
     *
     * @return void
     */
    public function clear(): void
    {
        CoreCache::flush(self::CACHE_GROUP);
    }

    /**
     * Enable cache
     *
     * @return void
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * Disable cache
     *
     * @return void
     */
    public function disable(): void
    {
        $this->enabled = false;
        $this->clear();
    }

    /**
     * Check if cache is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Refresh cache (clear and reload)
     *
     * @return void
     */
    public function refresh(): void
    {
        $this->clear();
    }

    /**
     * Get cache statistics
     *
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        return CoreCache::getStats();
    }

    /**
     * Increment counter
     *
     * @param string $key Counter key
     * @param int $offset Increment amount
     * @return int|false New value or false on failure
     */
    public function increment(string $key, int $offset = 1)
    {
        $value = $this->get($key);
        $newValue = is_numeric($value) ? (int) $value + $offset : $offset;
        $this->set($key, $newValue);
        return $newValue;
    }

    /**
     * Decrement counter
     *
     * @param string $key Counter key
     * @param int $offset Decrement amount
     * @return int|false New value or false on failure
     */
    public function decrement(string $key, int $offset = 1)
    {
        return $this->increment($key, -$offset);
    }
}
