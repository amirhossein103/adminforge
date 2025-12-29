<?php
/**
 * Settings API - Main Entry Point
 *
 * Centralized settings management with dot notation support,
 * nested arrays, validation, and caching.
 *
 * @package AdminForge
 * @since 2.0.0
 */

declare(strict_types=1);

namespace AdminForge\Settings;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Settings class - Main API
 *
 * @example
 * // Simple get/set
 * Settings::get('general.site_name', 'Default');
 * Settings::set('general.site_name', 'My Site');
 *
 * // Nested arrays
 * Settings::set('appearance.colors', ['primary' => '#0073aa', 'secondary' => '#00a0d2']);
 * Settings::get('appearance.colors.primary'); // '#0073aa'
 *
 * // Array operations (for checkboxes)
 * Settings::pushToArray('features.enabled', 'dark_mode');
 * Settings::removeFromArray('features.enabled', 'old_feature');
 *
 * // Group-based access
 * Settings::group('appearance')->all();
 * Settings::group('features')->get('enabled');
 */
final class Settings
{
    /**
     * Manager instance
     *
     * @var SettingsManager|null
     */
    private static ?SettingsManager $manager = null;

    /**
     * Get manager instance
     *
     * @return SettingsManager
     */
    private static function manager(): SettingsManager
    {
        if (self::$manager === null) {
            self::$manager = new SettingsManager();
        }

        return self::$manager;
    }

    /**
     * Get setting value using dot notation
     *
     * @param string $key Dot notation key (e.g., 'general.site_name')
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return self::manager()->get($key, $default);
    }

    /**
     * Set setting value using dot notation
     *
     * @param string $key Dot notation key
     * @param mixed $value Value to set
     * @param array<string, mixed> $options Options (validate, sanitize, etc.)
     * @return bool Success status
     */
    public static function set(string $key, $value, array $options = []): bool
    {
        return self::manager()->set($key, $value, $options);
    }

    /**
     * Set multiple settings at once
     *
     * @param array<string, mixed> $settings Key-value pairs
     * @param array<string, mixed> $options Options for all settings
     * @return bool Success status
     */
    public static function setMultiple(array $settings, array $options = []): bool
    {
        return self::manager()->setMultiple($settings, $options);
    }

    /**
     * Check if setting exists
     *
     * @param string $key Dot notation key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return self::manager()->has($key);
    }

    /**
     * Remove setting
     *
     * @param string $key Dot notation key
     * @return bool Success status
     */
    public static function remove(string $key): bool
    {
        return self::manager()->remove($key);
    }

    /**
     * Get all settings
     *
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return self::manager()->all();
    }

    /**
     * Get settings group
     *
     * @param string $name Group name
     * @return SettingsGroup
     */
    public static function group(string $name): SettingsGroup
    {
        return new SettingsGroup($name, self::manager());
    }

    /**
     * Get multiple settings
     *
     * @param array<string> $keys Array of keys
     * @return array<string, mixed> Key-value pairs
     */
    public static function getMultiple(array $keys): array
    {
        return self::manager()->getMultiple($keys);
    }

    /**
     * Push value to array setting
     *
     * @param string $key Dot notation key
     * @param mixed $value Value to push
     * @return bool Success status
     */
    public static function pushToArray(string $key, $value): bool
    {
        $current = self::get($key, []);

        if (!is_array($current)) {
            $current = [];
        }

        if (!in_array($value, $current, true)) {
            $current[] = $value;
            return self::set($key, $current);
        }

        return true;
    }

    /**
     * Remove value from array setting
     *
     * @param string $key Dot notation key
     * @param mixed $value Value to remove
     * @return bool Success status
     */
    public static function removeFromArray(string $key, $value): bool
    {
        $current = self::get($key, []);

        if (!is_array($current)) {
            return false;
        }

        $current = array_values(array_filter($current, function ($item) use ($value) {
            return $item !== $value;
        }));

        return self::set($key, $current);
    }

    /**
     * Toggle value in array setting
     *
     * @param string $key Dot notation key
     * @param mixed $value Value to toggle
     * @return bool Success status
     */
    public static function toggleInArray(string $key, $value): bool
    {
        $current = self::get($key, []);

        if (!is_array($current)) {
            $current = [];
        }

        if (in_array($value, $current, true)) {
            return self::removeFromArray($key, $value);
        } else {
            return self::pushToArray($key, $value);
        }
    }

    /**
     * Check if value exists in array setting
     *
     * @param string $key Dot notation key
     * @param mixed $value Value to check
     * @return bool
     */
    public static function inArray(string $key, $value): bool
    {
        $current = self::get($key, []);

        if (!is_array($current)) {
            return false;
        }

        return in_array($value, $current, true);
    }

    /**
     * Merge settings with existing
     *
     * @param string $key Dot notation key
     * @param array<string, mixed> $values Values to merge
     * @return bool Success status
     */
    public static function merge(string $key, array $values): bool
    {
        $current = self::get($key, []);

        if (!is_array($current)) {
            $current = [];
        }

        $merged = array_merge($current, $values);
        return self::set($key, $merged);
    }

    /**
     * Reset setting to default
     *
     * @param string $key Dot notation key
     * @return bool Success status
     */
    public static function reset(string $key): bool
    {
        return self::manager()->reset($key);
    }

    /**
     * Reset all settings
     *
     * @return bool Success status
     */
    public static function resetAll(): bool
    {
        return self::manager()->resetAll();
    }

    /**
     * Set default values for a group
     *
     * @param string $group Group name
     * @param array<string, mixed> $defaults Default values
     * @return void
     */
    public static function setDefaults(string $group, array $defaults): void
    {
        self::manager()->setDefaults($group, $defaults);
    }

    /**
     * Get type-safe integer value
     *
     * @param string $key Dot notation key
     * @param int $default Default value
     * @return int
     */
    public static function getInt(string $key, int $default = 0): int
    {
        $value = self::get($key, $default);
        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * Get type-safe boolean value
     *
     * @param string $key Dot notation key
     * @param bool $default Default value
     * @return bool
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        // Handle common boolean representations
        if (is_string($value)) {
            return in_array(strtolower($value), ['true', '1', 'yes', 'on'], true);
        }

        return (bool) $value;
    }

    /**
     * Get type-safe string value
     *
     * @param string $key Dot notation key
     * @param string $default Default value
     * @return string
     */
    public static function getString(string $key, string $default = ''): string
    {
        $value = self::get($key, $default);
        return is_string($value) ? $value : (string) $value;
    }

    /**
     * Get type-safe array value
     *
     * @param string $key Dot notation key
     * @param array<mixed> $default Default value
     * @return array<mixed>
     */
    public static function getArray(string $key, array $default = []): array
    {
        $value = self::get($key, $default);
        return is_array($value) ? $value : $default;
    }

    /**
     * Export settings to JSON
     *
     * @param string|null $group Optional group to export (null = all)
     * @return string JSON string
     */
    public static function export(?string $group = null): string
    {
        return SettingsImportExport::export($group);
    }

    /**
     * Import settings from JSON
     *
     * @param string $json JSON string
     * @param bool $merge Merge with existing or overwrite
     * @return array<string, mixed> Import result
     */
    public static function import(string $json, bool $merge = true): array
    {
        return SettingsImportExport::import($json, $merge);
    }

    /**
     * Create backup
     *
     * @param string $name Backup name
     * @return bool Success status
     */
    public static function backup(string $name): bool
    {
        return SettingsImportExport::backup($name);
    }

    /**
     * Restore from backup
     *
     * @param string $name Backup name
     * @return bool Success status
     */
    public static function restore(string $name): bool
    {
        return SettingsImportExport::restore($name);
    }

    /**
     * List backups
     *
     * @return array<string, array<string, mixed>> Backup list
     */
    public static function listBackups(): array
    {
        return SettingsImportExport::listBackups();
    }

    /**
     * Clear all caches
     *
     * @return void
     */
    public static function clearCache(): void
    {
        self::manager()->clearCache();
    }

    /**
     * Register validator
     *
     * @param string $name Validator name
     * @param callable $callback Validator callback
     * @return void
     */
    public static function registerValidator(string $name, callable $callback): void
    {
        SettingsValidator::register($name, $callback);
    }

    /**
     * Register sanitizer
     *
     * @param string $name Sanitizer name
     * @param callable $callback Sanitizer callback
     * @return void
     */
    public static function registerSanitizer(string $name, callable $callback): void
    {
        SettingsSanitizer::register($name, $callback);
    }

}
