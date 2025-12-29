<?php
/**
 * Security Trait
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Security;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * SecurityTrait for nonce verification and sanitization
 */
trait SecurityTrait
{
    /**
     * Verify nonce
     *
     * @param string $nonce Nonce value
     * @param string $action Nonce action
     * @return bool
     */
    protected function verifyNonce(string $nonce, string $action): bool
    {
        return wp_verify_nonce($nonce, $action) !== false;
    }

    /**
     * Create nonce
     *
     * @param string $action Nonce action
     * @return string
     */
    protected function createNonce(string $action): string
    {
        return wp_create_nonce($action);
    }

    /**
     * Check admin referer with nonce
     *
     * @param string $action Nonce action
     * @param string $queryArg Query argument name
     * @return bool
     */
    protected function checkAdminReferer(string $action, string $queryArg = '_wpnonce'): bool
    {
        $result = check_admin_referer($action, $queryArg);
        return $result !== false;
    }

    /**
     * Sanitize text field
     *
     * @param mixed $value Value to sanitize
     * @return string
     */
    protected function sanitizeText($value): string
    {
        return sanitize_text_field($value);
    }

    /**
     * Sanitize textarea
     *
     * @param mixed $value Value to sanitize
     * @return string
     */
    protected function sanitizeTextarea($value): string
    {
        return sanitize_textarea_field($value);
    }

    /**
     * Sanitize email
     *
     * @param mixed $value Value to sanitize
     * @return string
     */
    protected function sanitizeEmail($value): string
    {
        return sanitize_email($value);
    }

    /**
     * Sanitize URL
     *
     * @param mixed $value Value to sanitize
     * @return string
     */
    protected function sanitizeUrl($value): string
    {
        return esc_url_raw($value);
    }

    /**
     * Sanitize integer
     *
     * @param mixed $value Value to sanitize
     * @return int
     */
    protected function sanitizeInt($value): int
    {
        return (int) $value;
    }

    /**
     * Sanitize float
     *
     * @param mixed $value Value to sanitize
     * @return float
     */
    protected function sanitizeFloat($value): float
    {
        return (float) $value;
    }

    /**
     * Sanitize boolean
     *
     * @param mixed $value Value to sanitize
     * @return bool
     */
    protected function sanitizeBool($value): bool
    {
        return (bool) $value;
    }

    /**
     * Sanitize array recursively
     *
     * @param array<mixed> $data Array to sanitize
     * @param callable|null $callback Custom sanitization callback
     * @return array<mixed>
     */
    protected function sanitizeArray(array $data, ?callable $callback = null): array
    {
        return map_deep($data, $callback ?? 'sanitize_text_field');
    }

    /**
     * Sanitize HTML (allow safe tags)
     *
     * @param mixed $value Value to sanitize
     * @return string
     */
    protected function sanitizeHtml($value): string
    {
        return wp_kses_post($value);
    }

    /**
     * Sanitize key (alphanumeric + dashes/underscores)
     *
     * @param mixed $value Value to sanitize
     * @return string
     */
    protected function sanitizeKey($value): string
    {
        return sanitize_key($value);
    }

    /**
     * Sanitize file name
     *
     * @param mixed $value Value to sanitize
     * @return string
     */
    protected function sanitizeFileName($value): string
    {
        return sanitize_file_name($value);
    }

    /**
     * Validate email
     *
     * @param string $email Email to validate
     * @return bool
     */
    protected function isValidEmail(string $email): bool
    {
        return is_email($email) !== false;
    }

    /**
     * Validate URL
     *
     * @param string $url URL to validate
     * @return bool
     */
    protected function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Check user capability
     *
     * @param string $capability Capability to check
     * @param int|null $userId User ID (null = current user)
     * @return bool
     */
    protected function userCan(string $capability, ?int $userId = null): bool
    {
        if ($userId === null) {
            return current_user_can($capability);
        }

        $user = get_userdata($userId);
        return $user && $user->has_cap($capability);
    }

    /**
     * Verify AJAX referer
     *
     * @param string $action Action name
     * @param string $queryArg Query argument
     * @param bool $die Die on failure
     * @return bool
     */
    protected function checkAjaxReferer(string $action, string $queryArg = '_wpnonce', bool $die = true): bool
    {
        $result = check_ajax_referer($action, $queryArg, $die);
        return $result !== false;
    }

    /**
     * Escape HTML attribute
     *
     * @param string $text Text to escape
     * @return string
     */
    protected function escAttr(string $text): string
    {
        return esc_attr($text);
    }

    /**
     * Escape HTML
     *
     * @param string $text Text to escape
     * @return string
     */
    protected function escHtml(string $text): string
    {
        return esc_html($text);
    }

    /**
     * Escape URL
     *
     * @param string $url URL to escape
     * @return string
     */
    protected function escUrl(string $url): string
    {
        return esc_url($url);
    }

    /**
     * Escape JavaScript
     *
     * @param string $text Text to escape
     * @return string
     */
    protected function escJs(string $text): string
    {
        return esc_js($text);
    }

    /**
     * Escape textarea
     *
     * @param string $text Text to escape
     * @return string
     */
    protected function escTextarea(string $text): string
    {
        return esc_textarea($text);
    }

    /**
     * Validate and sanitize input based on type
     *
     * @param mixed $value Value to process
     * @param string $type Data type (text, email, url, int, float, bool, html, array)
     * @return mixed
     */
    protected function sanitizeByType($value, string $type)
    {
        switch ($type) {
            case 'email':
                return $this->sanitizeEmail($value);

            case 'url':
                return $this->sanitizeUrl($value);

            case 'int':
            case 'integer':
                return $this->sanitizeInt($value);

            case 'float':
            case 'number':
                return $this->sanitizeFloat($value);

            case 'bool':
            case 'boolean':
                return $this->sanitizeBool($value);

            case 'html':
                return $this->sanitizeHtml($value);

            case 'textarea':
                return $this->sanitizeTextarea($value);

            case 'key':
                return $this->sanitizeKey($value);

            case 'array':
                return is_array($value) ? $this->sanitizeArray($value) : [];

            case 'text':
            default:
                return $this->sanitizeText($value);
        }
    }

    /**
     * Prepare SQL query with placeholders
     *
     * This is the recommended method for preventing SQL injection.
     * Use placeholders (%s for strings, %d for integers, %f for floats).
     *
     * @param string $query SQL query with placeholders
     * @param mixed ...$args Values to replace placeholders
     * @return string|null Prepared query or null on failure
     *
     * @example
     * $sql = $this->prepareSql(
     *     "SELECT * FROM {$wpdb->posts} WHERE ID = %d AND post_status = %s",
     *     $post_id,
     *     'publish'
     * );
     */
    protected function prepareSql(string $query, ...$args): ?string
    {
        global $wpdb;
        return $wpdb->prepare($query, ...$args);
    }
}
