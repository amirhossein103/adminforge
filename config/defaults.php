<?php
/**
 * Default Configuration
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

use AdminForge\Core\Constants;

return [
    /**
     * Framework general settings
     */
    'framework' => [
        'name' => 'AdminForge',
        'version' => '1.0.0',
        'text_domain' => 'adminforge',
    ],

    /**
     * Admin menu settings
     */
    'menu' => [
        'title' => 'AdminForge',
        'menu_title' => 'AdminForge',
        'capability' => 'manage_options',
        'slug' => 'adminforge',
        'icon' => 'dashicons-admin-generic',
        'position' => Constants::DEFAULT_MENU_POSITION,
    ],

    /**
     * Meta box settings
     */
    'meta_box' => [
        'enabled' => true,
        'auto_nonce' => true,
        'context' => 'normal',
        'priority' => 'high',
    ],

    /**
     * Field settings
     */
    'fields' => [
        'prefix' => Constants::DEFAULT_FIELD_PREFIX,
        'upload_size_limit' => Constants::MAX_UPLOAD_SIZE,
        'allowed_mime_types' => Constants::ALLOWED_MIME_TYPES,
    ],

    /**
     * Security settings
     */
    'security' => [
        'nonce_action' => Constants::NONCE_ACTION,
        'nonce_name' => Constants::NONCE_NAME,
        'auto_sanitize' => true,
    ],

    /**
     * Performance settings
     */
    'performance' => [
        'cache_enabled' => true,
        'cache_expiration' => Constants::DEFAULT_CACHE_DURATION,
        'lazy_load_assets' => true,
        'minify_assets' => false, // Set to true in production
    ],

    /**
     * UI/UX settings
     */
    'ui' => [
        'primary_color' => Constants::COLOR_PRIMARY,
        'secondary_color' => Constants::COLOR_SECONDARY,
        'success_color' => Constants::COLOR_SUCCESS,
        'error_color' => Constants::COLOR_ERROR,
        'warning_color' => Constants::COLOR_WARNING,
    ],

    /**
     * Custom branding
     */
    'branding' => [
        'enabled' => false,
        'logo' => '',
        'admin_footer_text' => '',
    ],
];
