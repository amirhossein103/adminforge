<?php
/**
 * Settings Manager - Core Logic
 *
 * Handles all settings operations with centralized storage,
 * caching, validation, and sanitization.
 *
 * @package AdminForge
 * @since 2.0.0
 */

declare(strict_types=1);

namespace AdminForge\Settings;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * SettingsManager class
 */
final class SettingsManager
{
    /**
     * Option name in database
     */
    private const OPTION_NAME = 'adminforge_settings';

    /**
     * Defaults option name
     */
    private const DEFAULTS_NAME = 'adminforge_settings_defaults';

    /**
     * Cache instance
     *
     * @var SettingsCache
     */
    private SettingsCache $cache;

    /**
     * Validator instance
     *
     * @var SettingsValidator
     */
    private SettingsValidator $validator;

    /**
     * Sanitizer instance
     *
     * @var SettingsSanitizer
     */
    private SettingsSanitizer $sanitizer;

    /**
     * Default values registry
     *
     * @var array<string, mixed>
     */
    private array $defaults = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cache = new SettingsCache();
        $this->validator = new SettingsValidator();
        $this->sanitizer = new SettingsSanitizer();
        $this->loadDefaults();
    }

    /**
     * Load default values
     *
     * @return void
     */
    private function loadDefaults(): void
    {
        $defaults = get_option(self::DEFAULTS_NAME, []);
        $this->defaults = is_array($defaults) ? $defaults : [];
    }

    /**
     * Get all settings from storage
     *
     * @return array<string, mixed>
     */
    private function loadSettings(): array
    {
        // Check cache first
        $cached = $this->cache->get('all_settings');
        if ($cached !== null) {
            return $cached;
        }

        // Load from database
        $settings = get_option(self::OPTION_NAME, []);
        if (!is_array($settings)) {
            $settings = [];
        }

        // Cache it
        $this->cache->set('all_settings', $settings);

        return $settings;
    }

    /**
     * Save settings to storage
     *
     * @param array<string, mixed> $settings Settings array
     * @return bool Success status
     */
    private function saveSettings(array $settings): bool
    {
        $result = update_option(self::OPTION_NAME, $settings, false);

        if ($result) {
            // Update cache
            $this->cache->set('all_settings', $settings);
            $this->cache->increment('version');
        }

        return $result;
    }

    /**
     * Get value using dot notation
     *
     * @param string $key Dot notation key
     * @param mixed $default Default value
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        // Check cache first
        $cacheKey = 'key_' . $key;
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $settings = $this->loadSettings();
        $value = $this->getNestedValue($settings, $key, $default);

        // Cache individual key
        $this->cache->set($cacheKey, $value);

        return $value;
    }

    /**
     * Set value using dot notation
     *
     * @param string $key Dot notation key
     * @param mixed $value Value to set
     * @param array<string, mixed> $options Options
     * @return bool Success status
     */
    public function set(string $key, $value, array $options = []): bool
    {
        // Sanitize if sanitizer specified
        if (isset($options['sanitize']) && is_callable($options['sanitize'])) {
            $value = call_user_func($options['sanitize'], $value);
        } elseif (isset($options['type'])) {
            $value = $this->sanitizer->sanitize($value, $options['type']);
        }

        // Validate if validator specified
        if (isset($options['validate']) && is_callable($options['validate'])) {
            if (!call_user_func($options['validate'], $value)) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("AdminForge: Validation failed for key: {$key}");
                }
                return false;
            }
        } elseif (isset($options['type'])) {
            if (!$this->validator->validate($value, $options['type'])) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("AdminForge: Type validation failed for key: {$key}");
                }
                return false;
            }
        }

        $settings = $this->loadSettings();
        $this->setNestedValue($settings, $key, $value);

        $result = $this->saveSettings($settings);

        if ($result) {
            // Clear specific key cache
            $this->cache->remove('key_' . $key);
        }

        return $result;
    }

    /**
     * Set multiple values
     *
     * @param array<string, mixed> $settings Key-value pairs
     * @param array<string, mixed> $options Options
     * @return bool Success status
     */
    public function setMultiple(array $settings, array $options = []): bool
    {
        $allSettings = $this->loadSettings();
        $hasChanges = false;

        foreach ($settings as $key => $value) {
            // Sanitize
            if (isset($options['sanitize']) && is_callable($options['sanitize'])) {
                $value = call_user_func($options['sanitize'], $value);
            } elseif (isset($options['type'])) {
                $value = $this->sanitizer->sanitize($value, $options['type']);
            }

            // Validate
            if (isset($options['validate']) && is_callable($options['validate'])) {
                if (!call_user_func($options['validate'], $value)) {
                    continue;
                }
            } elseif (isset($options['type'])) {
                if (!$this->validator->validate($value, $options['type'])) {
                    continue;
                }
            }

            $this->setNestedValue($allSettings, $key, $value);
            $this->cache->remove('key_' . $key);
            $hasChanges = true;
        }

        if ($hasChanges) {
            return $this->saveSettings($allSettings);
        }

        return true;
    }

    /**
     * Check if key exists
     *
     * @param string $key Dot notation key
     * @return bool
     */
    public function has(string $key): bool
    {
        $settings = $this->loadSettings();
        $keys = explode('.', $key);
        $current = $settings;

        foreach ($keys as $k) {
            if (!is_array($current) || !array_key_exists($k, $current)) {
                return false;
            }
            $current = $current[$k];
        }

        return true;
    }

    /**
     * Remove key
     *
     * @param string $key Dot notation key
     * @return bool Success status
     */
    public function remove(string $key): bool
    {
        $settings = $this->loadSettings();
        $keys = explode('.', $key);
        $lastKey = array_pop($keys);

        // Navigate to parent
        $current = &$settings;
        foreach ($keys as $k) {
            if (!isset($current[$k]) || !is_array($current[$k])) {
                return false;
            }
            $current = &$current[$k];
        }

        // Remove the key
        if (isset($current[$lastKey])) {
            unset($current[$lastKey]);
            $this->cache->remove('key_' . $key);
            return $this->saveSettings($settings);
        }

        return false;
    }

    /**
     * Get all settings
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->loadSettings();
    }

    /**
     * Get multiple keys
     *
     * @param array<string> $keys Array of keys
     * @return array<string, mixed> Key-value pairs
     */
    public function getMultiple(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        return $result;
    }

    /**
     * Reset to default value
     *
     * @param string $key Dot notation key
     * @return bool Success status
     */
    public function reset(string $key): bool
    {
        $default = $this->getNestedValue($this->defaults, $key, null);

        if ($default !== null) {
            return $this->set($key, $default);
        }

        return $this->remove($key);
    }

    /**
     * Reset all settings
     *
     * @return bool Success status
     */
    public function resetAll(): bool
    {
        $this->cache->clear();
        return delete_option(self::OPTION_NAME);
    }

    /**
     * Set defaults for a group
     *
     * @param string $group Group name
     * @param array<string, mixed> $defaults Default values
     * @return void
     */
    public function setDefaults(string $group, array $defaults): void
    {
        $this->defaults[$group] = $defaults;
        update_option(self::DEFAULTS_NAME, $this->defaults, false);
    }

    /**
     * Get nested value from array using dot notation
     *
     * @param array<string, mixed> $array Array to search
     * @param string $key Dot notation key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getNestedValue(array $array, string $key, $default = null)
    {
        $keys = explode('.', $key);
        $current = $array;

        foreach ($keys as $k) {
            if (!is_array($current) || !array_key_exists($k, $current)) {
                return $default;
            }
            $current = $current[$k];
        }

        return $current;
    }

    /**
     * Set nested value in array using dot notation
     *
     * @param array<string, mixed> $array Array to modify (by reference)
     * @param string $key Dot notation key
     * @param mixed $value Value to set
     * @return void
     */
    private function setNestedValue(array &$array, string $key, $value): void
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $k) {
            if (!isset($current[$k]) || !is_array($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }

        $current = $value;
    }

    /**
     * Get cache instance
     *
     * @return SettingsCache
     */
    public function cache(): SettingsCache
    {
        return $this->cache;
    }
}
