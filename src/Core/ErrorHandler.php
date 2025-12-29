<?php
/**
 * Unified Error Handler
 *
 * Centralized error handling with logging and user notifications
 *
 * @package AdminForge
 * @since 2.0.0
 */

declare(strict_types=1);

namespace AdminForge\Core;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * ErrorHandler class
 *
 * @since 2.0.0
 */
class ErrorHandler
{
    /**
     * Log levels
     */
    public const LEVEL_INFO = 'info';
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_ERROR = 'error';
    public const LEVEL_CRITICAL = 'critical';

    /**
     * Pending admin notices
     *
     * @var array<array<string, string>>
     */
    private static array $notices = [];

    /**
     * Log a message
     *
     * @param string $message Message to log
     * @param string $level Log level
     * @param array<string, mixed> $context Additional context
     * @return void
     */
    public static function log(string $message, string $level = self::LEVEL_ERROR, array $context = []): void
    {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        $contextStr = !empty($context) ? ' | Context: ' . wp_json_encode($context) : '';
        $logMessage = sprintf(
            'AdminForge [%s]: %s%s',
            strtoupper($level),
            $message,
            $contextStr
        );

        error_log($logMessage);
    }

    /**
     * Add admin notice for display
     *
     * @param string $message Message to display
     * @param string $type Notice type (error, warning, success, info)
     * @return void
     */
    public static function notify(string $message, string $type = 'error'): void
    {
        self::$notices[] = [
            'message' => $message,
            'type' => $type,
        ];

        // Hook to display notices
        add_action('admin_notices', [self::class, 'displayNotices']);
    }

    /**
     * Display admin notices
     *
     * @return void
     */
    public static function displayNotices(): void
    {
        foreach (self::$notices as $notice) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr($notice['type']),
                esc_html($notice['message'])
            );
        }

        // Clear notices after display
        self::$notices = [];
    }

    /**
     * Handle exception
     *
     * @param \Throwable $e Exception to handle
     * @param bool $notify Whether to show admin notice
     * @param string $userMessage Custom message for users
     * @return void
     */
    public static function handle(\Throwable $e, bool $notify = false, string $userMessage = ''): void
    {
        // Log the exception
        self::log(
            $e->getMessage(),
            self::LEVEL_ERROR,
            [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]
        );

        // Notify user if requested
        if ($notify) {
            $message = $userMessage ?: __('An error occurred. Please check the logs for details.', 'adminforge');
            self::notify($message, 'error');
        }
    }

    /**
     * Handle file operation error
     *
     * @param string $operation Operation name (read, write, delete, etc.)
     * @param string $file File path
     * @param string|null $error Error message
     * @return void
     */
    public static function fileError(string $operation, string $file, ?string $error = null): void
    {
        $message = sprintf(
            'File %s failed: %s',
            $operation,
            basename($file)
        );

        self::log(
            $message,
            self::LEVEL_ERROR,
            [
                'file' => $file,
                'operation' => $operation,
                'error' => $error,
            ]
        );
    }

    /**
     * Handle validation error
     *
     * @param string $field Field name
     * @param string $error Error message
     * @param bool $notify Whether to show admin notice
     * @return void
     */
    public static function validationError(string $field, string $error, bool $notify = false): void
    {
        $message = sprintf(
            'Validation failed for field "%s": %s',
            $field,
            $error
        );

        self::log($message, self::LEVEL_WARNING);

        if ($notify) {
            self::notify($error, 'warning');
        }
    }

    /**
     * Log info message
     *
     * @param string $message Message to log
     * @param array<string, mixed> $context Additional context
     * @return void
     */
    public static function info(string $message, array $context = []): void
    {
        self::log($message, self::LEVEL_INFO, $context);
    }

    /**
     * Log warning message
     *
     * @param string $message Message to log
     * @param array<string, mixed> $context Additional context
     * @return void
     */
    public static function warning(string $message, array $context = []): void
    {
        self::log($message, self::LEVEL_WARNING, $context);
    }

    /**
     * Log error message
     *
     * @param string $message Message to log
     * @param array<string, mixed> $context Additional context
     * @return void
     */
    public static function error(string $message, array $context = []): void
    {
        self::log($message, self::LEVEL_ERROR, $context);
    }

    /**
     * Log critical message
     *
     * @param string $message Message to log
     * @param array<string, mixed> $context Additional context
     * @return void
     */
    public static function critical(string $message, array $context = []): void
    {
        self::log($message, self::LEVEL_CRITICAL, $context);
    }
}
