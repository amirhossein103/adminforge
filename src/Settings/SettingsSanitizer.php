<?php
/**
 * Settings Sanitizer
 *
 * Sanitizes setting values before storing
 * Uses SecurityTrait to avoid code duplication
 *
 * @package AdminForge
 * @since 2.0.0
 */

declare(strict_types=1);

namespace AdminForge\Settings;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

use AdminForge\Security\SecurityTrait;

/**
 * SettingsSanitizer class
 */
final class SettingsSanitizer
{
    use SecurityTrait;

    /**
     * Custom sanitizers
     *
     * @var array<string, callable>
     */
    private static array $sanitizers = [];

    /**
     * Register custom sanitizer
     *
     * @param string $name Sanitizer name
     * @param callable $callback Sanitizer callback
     * @return void
     */
    public static function register(string $name, callable $callback): void
    {
        self::$sanitizers[$name] = $callback;
    }

    /**
     * Sanitize value
     *
     * @param mixed $value Value to sanitize
     * @param string $type Sanitization type
     * @return mixed Sanitized value
     */
    public function sanitize($value, string $type)
    {
        // Check custom sanitizers first
        if (isset(self::$sanitizers[$type])) {
            return call_user_func(self::$sanitizers[$type], $value);
        }

        // Use SecurityTrait's built-in sanitization
        $result = $this->sanitizeByType($value, $type);

        // Handle additional types not in SecurityTrait
        if ($result === $value && !in_array($type, ['text', 'email', 'url', 'int', 'float', 'bool', 'html', 'textarea', 'key', 'array'], true)) {
            $result = $this->sanitizeAdditionalTypes($value, $type);
        }

        return $result;
    }

    /**
     * Sanitize additional types not in SecurityTrait
     *
     * @param mixed $value Value to sanitize
     * @param string $type Sanitization type
     * @return mixed
     */
    private function sanitizeAdditionalTypes($value, string $type)
    {
        switch ($type) {
            case 'color':
            case 'hex_color':
                $color = sanitize_hex_color($value);
                return $color !== null ? $color : '';

            case 'slug':
                return sanitize_title($value);

            case 'filename':
                return sanitize_file_name($value);

            case 'alphanumeric':
                return preg_replace('/[^a-zA-Z0-9]/', '', $value);

            case 'json':
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return wp_json_encode($decoded);
                    }
                } elseif (is_array($value)) {
                    return wp_json_encode($value);
                }
                return '';

            default:
                // Fallback to text sanitization
                return is_string($value) ? sanitize_text_field($value) : $value;
        }
    }

    /**
     * Sanitize multiple values
     *
     * @param array<string, mixed> $values Values to sanitize
     * @param string $type Sanitization type for all
     * @return array<string, mixed>
     */
    public function sanitizeMultiple(array $values, string $type): array
    {
        $sanitized = [];

        foreach ($values as $key => $value) {
            $sanitized[$key] = $this->sanitize($value, $type);
        }

        return $sanitized;
    }

    /**
     * Deep sanitize array (recursive)
     *
     * @param array<mixed> $array Array to sanitize
     * @param string $type Sanitization type
     * @return array<mixed>
     */
    public function deepSanitize(array $array, string $type = 'text'): array
    {
        return array_map(function ($item) use ($type) {
            if (is_array($item)) {
                return $this->deepSanitize($item, $type);
            }
            return $this->sanitize($item, $type);
        }, $array);
    }
}
