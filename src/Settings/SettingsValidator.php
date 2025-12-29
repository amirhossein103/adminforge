<?php
/**
 * Settings Validator
 *
 * Validates setting values before storing
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
 * SettingsValidator class
 */
final class SettingsValidator
{
    use SecurityTrait;

    /**
     * Custom validators
     *
     * @var array<string, callable>
     */
    private static array $validators = [];

    /**
     * Register custom validator
     *
     * @param string $name Validator name
     * @param callable $callback Validator callback
     * @return void
     */
    public static function register(string $name, callable $callback): void
    {
        self::$validators[$name] = $callback;
    }

    /**
     * Validate value
     *
     * @param mixed $value Value to validate
     * @param string $type Validation type
     * @return bool Valid or not
     */
    public function validate($value, string $type): bool
    {
        // Check custom validators first
        if (isset(self::$validators[$type])) {
            return call_user_func(self::$validators[$type], $value);
        }

        // Use SecurityTrait's built-in validation for email/url
        switch ($type) {
            case 'email':
                return $this->isValidEmail((string) $value);

            case 'url':
                return $this->isValidUrl((string) $value);

            case 'int':
            case 'integer':
                return $this->validateInt($value);

            case 'float':
            case 'number':
                return $this->validateFloat($value);

            case 'bool':
            case 'boolean':
                return $this->validateBool($value);

            case 'string':
                return is_string($value);

            case 'array':
                return is_array($value);

            case 'color':
            case 'hex_color':
                return $this->validateHexColor($value);

            case 'ip':
                return $this->validateIp($value);

            case 'date':
                return $this->validateDate($value);

            case 'json':
                return $this->validateJson($value);

            case 'alpha':
                return $this->validateAlpha($value);

            case 'alphanumeric':
                return $this->validateAlphanumeric($value);

            case 'slug':
                return $this->validateSlug($value);

            case 'positive':
                return is_numeric($value) && $value > 0;

            case 'negative':
                return is_numeric($value) && $value < 0;

            case 'required':
                return !empty($value);

            default:
                // No validation for unknown types
                return true;
        }
    }

    /**
     * Validate integer
     *
     * @param mixed $value Value to validate
     * @return bool
     */
    private function validateInt($value): bool
    {
        return is_int($value) || (is_string($value) && ctype_digit($value));
    }

    /**
     * Validate float
     *
     * @param mixed $value Value to validate
     * @return bool
     */
    private function validateFloat($value): bool
    {
        return is_float($value) || is_int($value) || (is_string($value) && is_numeric($value));
    }

    /**
     * Validate boolean
     *
     * @param mixed $value Value to validate
     * @return bool
     */
    private function validateBool($value): bool
    {
        return is_bool($value) || in_array($value, [0, 1, '0', '1', 'true', 'false', 'yes', 'no'], true);
    }

    /**
     * Validate hex color
     *
     * @param mixed $value Value to validate
     * @return bool
     */
    private function validateHexColor($value): bool
    {
        return is_string($value) && preg_match('/^#[a-f0-9]{6}$/i', $value) === 1;
    }

    /**
     * Validate IP address
     *
     * @param mixed $value Value to validate
     * @return bool
     */
    private function validateIp($value): bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Validate date
     *
     * @param mixed $value Value to validate
     * @return bool
     */
    private function validateDate($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $timestamp = strtotime($value);
        return $timestamp !== false;
    }

    /**
     * Validate JSON
     *
     * @param mixed $value Value to validate
     * @return bool
     */
    private function validateJson($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Validate alphabetic
     *
     * @param mixed $value Value to validate
     * @return bool
     */
    private function validateAlpha($value): bool
    {
        return is_string($value) && ctype_alpha($value);
    }

    /**
     * Validate alphanumeric
     *
     * @param mixed $value Value to validate
     * @return bool
     */
    private function validateAlphanumeric($value): bool
    {
        return is_string($value) && ctype_alnum($value);
    }

    /**
     * Validate slug
     *
     * @param mixed $value Value to validate
     * @return bool
     */
    private function validateSlug($value): bool
    {
        return is_string($value) && preg_match('/^[a-z0-9-]+$/', $value) === 1;
    }

    /**
     * Validate multiple rules
     *
     * @param mixed $value Value to validate
     * @param array<string> $rules Array of validation types
     * @return bool
     */
    public function validateMultiple($value, array $rules): bool
    {
        foreach ($rules as $rule) {
            if (!$this->validate($value, $rule)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate with custom message
     *
     * @param mixed $value Value to validate
     * @param string $type Validation type
     * @param string $message Custom error message
     * @return array{valid: bool, message: string}
     */
    public function validateWithMessage($value, string $type, string $message = ''): array
    {
        $valid = $this->validate($value, $type);

        return [
            'valid' => $valid,
            'message' => $valid ? '' : ($message ?: $this->getDefaultMessage($type)),
        ];
    }

    /**
     * Get default error message for type
     *
     * @param string $type Validation type
     * @return string
     */
    private function getDefaultMessage(string $type): string
    {
        $messages = [
            'email' => __('Invalid email address', 'adminforge'),
            'url' => __('Invalid URL', 'adminforge'),
            'int' => __('Value must be an integer', 'adminforge'),
            'float' => __('Value must be a number', 'adminforge'),
            'bool' => __('Value must be boolean', 'adminforge'),
            'string' => __('Value must be a string', 'adminforge'),
            'array' => __('Value must be an array', 'adminforge'),
            'color' => __('Invalid color format', 'adminforge'),
            'ip' => __('Invalid IP address', 'adminforge'),
            'date' => __('Invalid date format', 'adminforge'),
            'json' => __('Invalid JSON format', 'adminforge'),
            'alpha' => __('Value must contain only letters', 'adminforge'),
            'alphanumeric' => __('Value must contain only letters and numbers', 'adminforge'),
            'slug' => __('Invalid slug format', 'adminforge'),
            'positive' => __('Value must be positive', 'adminforge'),
            'negative' => __('Value must be negative', 'adminforge'),
            'required' => __('This field is required', 'adminforge'),
        ];

        return $messages[$type] ?? __('Validation failed', 'adminforge');
    }
}
