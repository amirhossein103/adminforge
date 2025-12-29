<?php
/**
 * Settings Import/Export
 *
 * Handles import/export and backup/restore functionality
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
 * SettingsImportExport class
 */
class SettingsImportExport
{
    use SecurityTrait;

    /**
     * Backup option prefix
     */
    private const BACKUP_PREFIX = 'adminforge_backup_';

    /**
     * Export settings to JSON
     *
     * @param string|null $group Optional group to export
     * @return string JSON string
     */
    public static function export(?string $group = null): string
    {
        $settings = get_option('adminforge_settings', []);

        if ($group !== null) {
            $settings = $settings[$group] ?? [];
        }

        $export = [
            'version' => ADMINFORGE_VERSION ?? '2.0.0',
            'export_date' => current_time('mysql'),
            'export_time' => time(),
            'site_url' => get_site_url(),
            'group' => $group,
            'settings' => $settings,
        ];

        return wp_json_encode($export, JSON_PRETTY_PRINT);
    }

    /**
     * Export and download as file
     *
     * @param string|null $group Optional group
     * @param string $filename Filename
     * @param string $nonce Nonce for security
     * @return void
     */
    public static function exportToFile(?string $group = null, string $filename = 'adminforge-settings.json', string $nonce = ''): void
    {
        // Verify nonce
        if (empty($nonce) || wp_verify_nonce($nonce, 'adminforge_export_settings') !== 1) {
            wp_die(esc_html__('Security check failed.', 'adminforge'));
        }

        // Check capability
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permission denied.', 'adminforge'));
        }

        $json = self::export($group);

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . sanitize_file_name($filename) . '"');
        header('Content-Length: ' . strlen($json));

        echo $json;
        exit;
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
        $result = [
            'success' => false,
            'message' => '',
            'imported' => 0,
            'errors' => [],
        ];

        // Decode JSON
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $result['message'] = __('Invalid JSON format', 'adminforge');
            return $result;
        }

        // Validate structure
        if (!isset($data['settings'])) {
            $result['message'] = __('Invalid settings structure', 'adminforge');
            return $result;
        }

        $currentSettings = get_option('adminforge_settings', []);
        $importSettings = $data['settings'];

        // Handle group import
        if (isset($data['group']) && $data['group'] !== null) {
            $group = $data['group'];
            if ($merge && isset($currentSettings[$group])) {
                $currentSettings[$group] = array_merge($currentSettings[$group], $importSettings);
            } else {
                $currentSettings[$group] = $importSettings;
            }
        } else {
            // Full import
            if ($merge) {
                $currentSettings = array_replace_recursive($currentSettings, $importSettings);
            } else {
                $currentSettings = $importSettings;
            }
        }

        // Save
        $success = update_option('adminforge_settings', $currentSettings, false);

        if ($success) {
            $result['success'] = true;
            $result['imported'] = self::countSettings($importSettings);
            $result['message'] = sprintf(
                __('Successfully imported %d settings', 'adminforge'),
                $result['imported']
            );

            // Clear cache
            Settings::clearCache();
        } else {
            $result['message'] = __('Failed to save settings', 'adminforge');
        }

        return $result;
    }

    /**
     * Import from uploaded file
     *
     * @param array<string, mixed> $file $_FILES array element
     * @param bool $merge Merge with existing
     * @param string $nonce Nonce for security
     * @return array<string, mixed> Import result
     */
    public static function importFromFile(array $file, bool $merge = true, string $nonce = ''): array
    {
        $result = [
            'success' => false,
            'message' => '',
        ];

        // Verify nonce
        if (empty($nonce) || wp_verify_nonce($nonce, 'adminforge_import_settings') !== 1) {
            $result['message'] = __('Security check failed.', 'adminforge');
            return $result;
        }

        // Check capability
        if (!current_user_can('manage_options')) {
            $result['message'] = __('Permission denied.', 'adminforge');
            return $result;
        }

        // Validate upload
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $result['message'] = __('No file uploaded', 'adminforge');
            return $result;
        }

        // Validate file type
        $filetype = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
        if ($filetype['ext'] !== 'json' && $filetype['type'] !== 'application/json') {
            $result['message'] = __('Invalid file type', 'adminforge');
            return $result;
        }

        // Size limit (5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            $result['message'] = __('File too large', 'adminforge');
            return $result;
        }

        // Read file
        $json = file_get_contents($file['tmp_name']);
        if ($json === false) {
            $result['message'] = __('Failed to read file', 'adminforge');
            return $result;
        }

        return self::import($json, $merge);
    }

    /**
     * Create backup
     *
     * @param string $name Backup name
     * @return bool Success status
     */
    public static function backup(string $name): bool
    {
        $name = sanitize_key($name);
        $settings = get_option('adminforge_settings', []);

        $backup = [
            'name' => $name,
            'created' => time(),
            'settings' => $settings,
        ];

        return add_option(self::BACKUP_PREFIX . $name, $backup, '', false);
    }

    /**
     * Restore from backup
     *
     * @param string $name Backup name
     * @return bool Success status
     */
    public static function restore(string $name): bool
    {
        $name = sanitize_key($name);
        $backup = get_option(self::BACKUP_PREFIX . $name);

        if (!$backup || !isset($backup['settings'])) {
            return false;
        }

        $result = update_option('adminforge_settings', $backup['settings'], false);

        if ($result) {
            Settings::clearCache();
        }

        return $result;
    }

    /**
     * List all backups
     *
     * @return array<string, array<string, mixed>> Backup list
     */
    public static function listBackups(): array
    {
        global $wpdb;

        $prefix = self::BACKUP_PREFIX;
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like($prefix) . '%'
            ),
            ARRAY_A
        );

        $backups = [];
        foreach ($results as $row) {
            $backup = maybe_unserialize($row['option_value']);
            if (isset($backup['name'], $backup['created'])) {
                $backups[$backup['name']] = [
                    'name' => $backup['name'],
                    'created' => $backup['created'],
                    'created_date' => date('Y-m-d H:i:s', $backup['created']),
                    'size' => self::countSettings($backup['settings']),
                ];
            }
        }

        return $backups;
    }

    /**
     * Delete backup
     *
     * @param string $name Backup name
     * @return bool Success status
     */
    public static function deleteBackup(string $name): bool
    {
        $name = sanitize_key($name);
        return delete_option(self::BACKUP_PREFIX . $name);
    }

    /**
     * Count settings recursively
     *
     * @param array<mixed> $settings Settings array
     * @return int Count
     */
    private static function countSettings(array $settings): int
    {
        $count = 0;
        foreach ($settings as $value) {
            if (is_array($value)) {
                $count += self::countSettings($value);
            } else {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Validate import data
     *
     * @param string $json JSON string
     * @return array{valid: bool, message: string, metadata: array<string, mixed>}
     */
    public static function validateImport(string $json): array
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'valid' => false,
                'message' => __('Invalid JSON', 'adminforge'),
                'metadata' => [],
            ];
        }

        if (!isset($data['settings'])) {
            return [
                'valid' => false,
                'message' => __('Invalid structure', 'adminforge'),
                'metadata' => [],
            ];
        }

        $metadata = [
            'version' => $data['version'] ?? 'unknown',
            'export_date' => $data['export_date'] ?? 'unknown',
            'site_url' => $data['site_url'] ?? 'unknown',
            'group' => $data['group'] ?? null,
            'count' => self::countSettings($data['settings']),
        ];

        return [
            'valid' => true,
            'message' => __('Valid settings file', 'adminforge'),
            'metadata' => $metadata,
        ];
    }
}
