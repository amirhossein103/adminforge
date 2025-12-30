<?php
/**
 * Framework Constants
 *
 * Centralized location for all magic numbers and configuration values
 *
 * @package AdminForge
 * @since 2.0.0
 */

declare(strict_types=1);

namespace AdminForge\Core;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Constants class
 *
 * @since 2.0.0
 */
final class Constants
{
    /**
     * Limits
     */
    public const MAX_REPEATER_ROWS = 50;
    public const MIN_REPEATER_ROWS = 0;
    public const MAX_UPLOAD_SIZE = 5242880; // 5MB in bytes
    public const DEFAULT_MENU_POSITION = 99;

    /**
     * Cache durations
     */
    public const CACHE_HOUR = 3600;         // 1 hour in seconds
    public const CACHE_DAY = 86400;         // 1 day in seconds
    public const CACHE_WEEK = 604800;       // 1 week in seconds
    public const DEFAULT_CACHE_DURATION = self::CACHE_HOUR;

    /**
     * UI/Animation speeds (milliseconds)
     */
    public const ANIMATION_FAST = 150;
    public const ANIMATION_NORMAL = 300;
    public const ANIMATION_SLOW = 600;
    public const DEFAULT_ANIMATION_SPEED = self::ANIMATION_NORMAL;

    /**
     * Field defaults
     */
    public const DEFAULT_FIELD_PREFIX = 'adminforge_';

    /**
     * Allowed MIME types for media uploads
     */
    public const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /**
     * Security
     */
    public const NONCE_ACTION = 'adminforge_nonce_action';
    public const NONCE_NAME = 'adminforge_nonce';

    /**
     * Colors
     */
    public const COLOR_PRIMARY = '#0073aa';
    public const COLOR_SECONDARY = '#00a0d2';
    public const COLOR_SUCCESS = '#46b450';
    public const COLOR_ERROR = '#dc3232';
    public const COLOR_WARNING = '#ffb900';
}
