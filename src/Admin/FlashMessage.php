<?php
/**
 * Flash Message Class
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Admin;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * FlashMessage class using transients
 */
class FlashMessage
{
    /**
     * Transient key prefix
     */
    private const TRANSIENT_PREFIX = 'adminforge_flash_';

    /**
     * Message types
     */
    private const TYPE_SUCCESS = 'success';
    private const TYPE_ERROR = 'error';
    private const TYPE_WARNING = 'warning';
    private const TYPE_INFO = 'info';

    /**
     * Add success message
     *
     * @param string $message Message text
     * @param int $expiration Expiration time in seconds
     * @return void
     */
    public static function success(string $message, int $expiration = 60): void
    {
        self::add($message, self::TYPE_SUCCESS, $expiration);
    }

    /**
     * Add error message
     *
     * @param string $message Message text
     * @param int $expiration Expiration time in seconds
     * @return void
     */
    public static function error(string $message, int $expiration = 60): void
    {
        self::add($message, self::TYPE_ERROR, $expiration);
    }

    /**
     * Add warning message
     *
     * @param string $message Message text
     * @param int $expiration Expiration time in seconds
     * @return void
     */
    public static function warning(string $message, int $expiration = 60): void
    {
        self::add($message, self::TYPE_WARNING, $expiration);
    }

    /**
     * Add info message
     *
     * @param string $message Message text
     * @param int $expiration Expiration time in seconds
     * @return void
     */
    public static function info(string $message, int $expiration = 60): void
    {
        self::add($message, self::TYPE_INFO, $expiration);
    }

    /**
     * Add flash message
     *
     * @param string $message Message text
     * @param string $type Message type
     * @param int $expiration Expiration time in seconds
     * @return void
     */
    private static function add(string $message, string $type, int $expiration): void
    {
        $userId = get_current_user_id();
        $key = self::TRANSIENT_PREFIX . $userId;

        // Get existing messages
        $messages = get_transient($key);

        if (!is_array($messages)) {
            $messages = [];
        }

        // Add new message
        $messages[] = [
            'message' => $message,
            'type' => $type,
            'time' => time(),
        ];

        // Save to transient
        set_transient($key, $messages, $expiration);
    }

    /**
     * Get all flash messages
     *
     * @param bool $clear Clear messages after retrieval
     * @return array<array<string, mixed>>
     */
    public static function get(bool $clear = true): array
    {
        $userId = get_current_user_id();
        $key = self::TRANSIENT_PREFIX . $userId;

        $messages = get_transient($key);

        if (!is_array($messages)) {
            return [];
        }

        // Clear messages if requested
        if ($clear) {
            delete_transient($key);
        }

        return $messages;
    }

    /**
     * Check if there are any messages
     *
     * @return bool
     */
    public static function has(): bool
    {
        $userId = get_current_user_id();
        $key = self::TRANSIENT_PREFIX . $userId;

        $messages = get_transient($key);

        return is_array($messages) && !empty($messages);
    }

    /**
     * Clear all flash messages
     *
     * @return void
     */
    public static function clear(): void
    {
        $userId = get_current_user_id();
        $key = self::TRANSIENT_PREFIX . $userId;

        delete_transient($key);
    }

    /**
     * Display flash messages
     *
     * @return void
     */
    public static function display(): void
    {
        if (!self::has()) {
            return;
        }

        $messages = self::get(true);

        foreach ($messages as $flash) {
            $type = $flash['type'] ?? 'info';
            $message = $flash['message'] ?? '';

            if (empty($message)) {
                continue;
            }

            self::renderMessage($message, $type);
        }
    }

    /**
     * Render single message
     *
     * @param string $message Message text
     * @param string $type Message type
     * @return void
     */
    private static function renderMessage(string $message, string $type): void
    {
        $class = 'notice notice-' . $type;

        if ($type === self::TYPE_SUCCESS) {
            $class .= ' is-dismissible';
        }

        printf(
            '<div class="%s"><p>%s</p></div>',
            esc_attr($class),
            esc_html($message)
        );
    }

    /**
     * Auto-display messages on admin notices
     *
     * @return void
     */
    public static function autoDisplay(): void
    {
        add_action('admin_notices', [self::class, 'display']);
    }

    /**
     * Add message with redirect
     *
     * @param string $message Message text
     * @param string $type Message type
     * @param string $redirectUrl Redirect URL
     * @return void
     */
    public static function addAndRedirect(string $message, string $type, string $redirectUrl): void
    {
        self::add($message, $type, 60);

        wp_safe_redirect($redirectUrl);
        exit;
    }
}

// Auto-register display hook
FlashMessage::autoDisplay();
