<?php
/**
 * Field Factory Class
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Fields;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * FieldFactory class for creating field instances
 */
final class FieldFactory
{
    /**
     * Registered field types
     *
     * @var array<string, string>
     */
    private static array $fieldTypes = [
        'text' => TextField::class,
        'email' => TextField::class,
        'url' => TextField::class,
        'number' => TextField::class,
        'tel' => TextField::class,
        'password' => TextField::class,
        'textarea' => TextareaField::class,
        'select' => SelectField::class,
        'checkbox' => CheckboxField::class,
        'radio' => RadioField::class,
        'media' => MediaField::class,
        'image' => MediaField::class,
        'color' => ColorField::class,
        'editor' => WPEditorField::class,
        'wysiwyg' => WPEditorField::class,
        'repeater' => RepeaterField::class,
    ];

    /**
     * Create field instance
     *
     * Supports two calling conventions:
     * 1. create('text', 'field_id', ['label' => 'Label'])
     * 2. create('text', ['id' => 'field_id', 'label' => 'Label'])
     *
     * @param string $type Field type
     * @param string|array<string, mixed> $idOrConfig Field ID or full config array
     * @param array<string, mixed> $args Field arguments (only used with Convention 1)
     * @return FieldInterface|null
     */
    public static function create(string $type, string|array $idOrConfig, array $args = []): ?FieldInterface
    {
        // Check if field type is registered
        if (!isset(self::$fieldTypes[$type])) {
            return null;
        }

        // Handle both calling conventions
        if (is_array($idOrConfig)) {
            // Convention 2: Config array with 'id' key
            $config = $idOrConfig;
            $id = $config['id'] ?? '';
            unset($config['id']);
            $args = $config;
        } else {
            // Convention 1: Separate ID and args
            $id = $idOrConfig;
        }

        // Validate ID
        if (empty($id)) {
            return null;
        }

        $className = self::$fieldTypes[$type];

        // Set type in args for fields that need it (like TextField)
        if (!isset($args['type'])) {
            $args['type'] = $type;
        }

        // Create and return field instance
        return new $className($id, $args);
    }

    /**
     * Register custom field type
     *
     * @param string $type Field type identifier
     * @param string $className Full class name implementing FieldInterface
     * @return bool
     */
    public static function register(string $type, string $className): bool
    {
        // Check if class exists and implements FieldInterface
        if (!class_exists($className)) {
            return false;
        }

        $reflection = new \ReflectionClass($className);
        if (!$reflection->implementsInterface(FieldInterface::class)) {
            return false;
        }

        self::$fieldTypes[$type] = $className;
        return true;
    }

    /**
     * Unregister field type
     *
     * @param string $type Field type identifier
     * @return void
     */
    public static function unregister(string $type): void
    {
        unset(self::$fieldTypes[$type]);
    }

    /**
     * Check if field type is registered
     *
     * @param string $type Field type identifier
     * @return bool
     */
    public static function has(string $type): bool
    {
        return isset(self::$fieldTypes[$type]);
    }

    /**
     * Get all registered field types
     *
     * @return array<string, string>
     */
    public static function getRegisteredTypes(): array
    {
        return self::$fieldTypes;
    }
}
