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
                return sanitize_email($value);

            case 'url':
                return esc_url_raw($value);

            case 'int':
            case 'integer':
                return (int) $value;

            case 'float':
            case 'number':
                return (float) $value;

            case 'bool':
            case 'boolean':
                return (bool) $value;

            case 'html':
                return wp_kses_post($value);

            case 'textarea':
                return sanitize_textarea_field($value);

            case 'key':
                return sanitize_key($value);

            case 'array':
                return is_array($value) ? $this->sanitizeArray($value) : [];

            case 'text':
            default:
                return sanitize_text_field($value);
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
