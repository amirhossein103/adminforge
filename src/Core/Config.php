<?php
/**
 * Configuration Management with Dot Notation
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Core;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Config class with dot notation access
 */
class Config
{
    /**
     * Configuration data
     *
     * @var array<string, mixed>
     */
    private static array $config = [];

    /**
     * Constructor - Load default config
     */
    public function __construct()
    {
        if (empty(self::$config)) {
            $this->loadDefaults();
        }
    }

    /**
     * Load default configuration
     *
     * @return void
     */
    private function loadDefaults(): void
    {
        $defaultConfigFile = ADMINFORGE_PATH . 'config/defaults.php';

        if (!file_exists($defaultConfigFile)) {
            // Log error if debug mode is enabled
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('AdminForge: Default configuration file not found at ' . $defaultConfigFile);
            }
            self::$config = [];
            return;
        }

        try {
            $defaults = require $defaultConfigFile;
            if (!is_array($defaults)) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('AdminForge: Configuration file did not return an array');
                }
                self::$config = [];
                return;
            }
            self::$config = $defaults;
        } catch (\Throwable $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('AdminForge: Error loading configuration file: ' . $e->getMessage());
            }
            self::$config = [];
        }
    }

    /**
     * Get configuration value using dot notation
     *
     * Example: Config::get('menu.title') or $config->get('menu.title')
     *
     * @param string $key Dot notation key (e.g., 'menu.title')
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Set configuration value using dot notation
     *
     * Example: Config::set('menu.title', 'My Admin')
     *
     * @param string $key Dot notation key
     * @param mixed $value Value to set
     * @return void
     */
    public static function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &self::$config;

        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
    }

    /**
     * Check if configuration key exists
     *
     * @param string $key Dot notation key
     * @return bool
     */
    public static function has(string $key): bool
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return false;
            }
            $value = $value[$k];
        }

        return true;
    }

    /**
     * Merge user configuration with defaults
     *
     * @param array<string, mixed> $userConfig User-defined config
     * @return void
     */
    public static function merge(array $userConfig): void
    {
        self::$config = array_replace_recursive(self::$config, $userConfig);
    }

    /**
     * Get all configuration
     *
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return self::$config;
    }

    /**
     * Reset configuration to defaults
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$config = [];
        $instance = new self();
        $instance->loadDefaults();
    }

    /**
     * Remove a configuration key
     *
     * @param string $key Dot notation key
     * @return void
     */
    public static function remove(string $key): void
    {
        $keys = explode('.', $key);
        $config = &self::$config;

        $lastKey = array_pop($keys);

        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                return;
            }
            $config = &$config[$k];
        }

        if ($lastKey !== null) {
            unset($config[$lastKey]);
        }
    }
}
