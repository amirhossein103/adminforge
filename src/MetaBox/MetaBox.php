<?php
/**
 * MetaBox Class
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\MetaBox;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

use AdminForge\Core\Config;
use AdminForge\Core\ErrorHandler;
use AdminForge\Helpers\DataHydrator;
use AdminForge\Security\SecurityTrait;

/**
 * MetaBox class for custom meta boxes
 */
class MetaBox
{
    use SecurityTrait;
    /**
     * Meta box ID
     *
     * @var string
     */
    private string $id;

    /**
     * Meta box title
     *
     * @var string
     */
    private string $title;

    /**
     * Post types to show meta box on
     *
     * @var array<string>|string
     */
    private $postTypes;

    /**
     * Meta box context (normal, side, advanced)
     *
     * @var string
     */
    private string $context;

    /**
     * Meta box priority (high, low, default)
     *
     * @var string
     */
    private string $priority;

    /**
     * Fields configuration
     *
     * @var array<array<string, mixed>>
     */
    private array $fields = [];

    /**
     * Auto add nonce field
     *
     * @var bool
     */
    private bool $autoNonce;

    /**
     * Nonce action name
     *
     * @var string
     */
    private string $nonceAction;

    /**
     * Nonce field name
     *
     * @var string
     */
    private string $nonceName;

    /**
     * Constructor
     *
     * @param string $id Meta box ID
     * @param string $title Meta box title
     * @param array<string>|string $postTypes Post type(s)
     */
    public function __construct(string $id, string $title, $postTypes = 'post')
    {
        $this->id = $id;
        $this->title = $title;
        $this->postTypes = $postTypes;

        // Set defaults from config
        $this->context = Config::get('meta_box.context', 'normal');
        $this->priority = Config::get('meta_box.priority', 'high');
        $this->autoNonce = Config::get('meta_box.auto_nonce', true);

        // Nonce settings
        $this->nonceAction = $this->id . '_nonce_action';
        $this->nonceName = $this->id . '_nonce';
    }

    /**
     * Register meta box
     *
     * @return self
     */
    public function register(): self
    {
        add_action('add_meta_boxes', [$this, 'addMetaBox']);
        add_action('save_post', [$this, 'save'], 10, 2);

        return $this;
    }

    /**
     * Add meta box callback (WordPress callback)
     *
     * @return void
     */
    private function addMetaBox(): void
    {
        add_meta_box(
            $this->id,
            $this->title,
            [$this, 'render'],
            $this->postTypes,
            $this->context,
            $this->priority
        );
    }

    /**
     * Render meta box content (WordPress callback)
     *
     * @param \WP_Post $post Current post object
     * @return void
     */
    private function render(\WP_Post $post): void
    {
        // Add nonce field for security
        if ($this->autoNonce) {
            wp_nonce_field($this->nonceAction, $this->nonceName);
        }

        echo '<div class="adminforge-metabox">';

        // Render fields
        foreach ($this->fields as $field) {
            $this->renderField($field, $post->ID);
        }

        echo '</div>';
    }

    /**
     * Render single field
     *
     * @param array<string, mixed> $field Field configuration
     * @param int $postId Post ID
     * @return void
     */
    private function renderField(array $field, int $postId): void
    {
        $fieldId = $field['id'] ?? '';
        $fieldType = $field['type'] ?? 'text';
        $fieldLabel = $field['label'] ?? '';
        $fieldHint = $field['hint'] ?? '';
        $fieldDefault = $field['default'] ?? '';

        // Validate field ID
        if (empty($fieldId) || !preg_match('/^[a-zA-Z0-9_-]+$/', $fieldId)) {
            ErrorHandler::warning('Invalid field ID in meta box: ' . $this->id, ['field_id' => $fieldId]);
            return;
        }

        // Get saved value
        $value = get_post_meta($postId, $fieldId, true);
        if (empty($value) && !empty($fieldDefault)) {
            $value = $fieldDefault;
        }

        echo '<div class="adminforge-field adminforge-field-' . esc_attr($fieldType) . '">';

        if ($fieldLabel) {
            echo '<label for="' . esc_attr($fieldId) . '">';
            echo esc_html($fieldLabel);
            echo '</label>';
        }

        // Render field based on type
        $this->renderFieldInput($field, $value);

        if ($fieldHint) {
            echo '<span class="adminforge-field-hint">' . esc_html($fieldHint) . '</span>';
        }

        echo '</div>';
    }

    /**
     * Render field input
     *
     * @param array<string, mixed> $field Field configuration
     * @param mixed $value Field value
     * @return void
     */
    private function renderFieldInput(array $field, $value): void
    {
        $fieldId = $field['id'] ?? '';
        $fieldType = $field['type'] ?? 'text';
        $fieldPlaceholder = $field['placeholder'] ?? '';

        switch ($fieldType) {
            case 'text':
            case 'email':
            case 'url':
            case 'number':
                printf(
                    '<input type="%s" id="%s" name="%s" value="%s" placeholder="%s" />',
                    esc_attr($fieldType),
                    esc_attr($fieldId),
                    esc_attr($fieldId),
                    esc_attr($value),
                    esc_attr($fieldPlaceholder)
                );
                break;

            case 'textarea':
                $rows = $field['rows'] ?? 5;
                printf(
                    '<textarea id="%s" name="%s" rows="%d" placeholder="%s">%s</textarea>',
                    esc_attr($fieldId),
                    esc_attr($fieldId),
                    (int) $rows,
                    esc_attr($fieldPlaceholder),
                    esc_textarea($value)
                );
                break;

            case 'checkbox':
                printf(
                    '<input type="checkbox" id="%s" name="%s" value="1" %s />',
                    esc_attr($fieldId),
                    esc_attr($fieldId),
                    checked($value, 1, false)
                );
                break;

            case 'select':
                $options = $field['options'] ?? [];
                echo '<select id="' . esc_attr($fieldId) . '" name="' . esc_attr($fieldId) . '">';

                foreach ($options as $optionValue => $optionLabel) {
                    printf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr($optionValue),
                        selected($value, $optionValue, false),
                        esc_html($optionLabel)
                    );
                }

                echo '</select>';
                break;

            default:
                // Hook for custom field types
                do_action('adminforge_render_field_' . $fieldType, $field, $value);
                break;
        }
    }

    /**
     * Save meta box data (WordPress callback)
     *
     * @param int $postId Post ID
     * @param \WP_Post $post Post object
     * @return void
     */
    private function save(int $postId, \WP_Post $post): void
    {
        // Check if nonce is set and valid
        if ($this->autoNonce) {
            if (!isset($_POST[$this->nonceName])) {
                return;
            }

            $nonce = $this->sanitizeText(wp_unslash($_POST[$this->nonceName]));
            if (!$this->verifyNonce($nonce, $this->nonceAction)) {
                ErrorHandler::error('Nonce verification failed for meta box: ' . $this->id);
                return;
            }
        }

        // Check if autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        // Check post type
        if (is_array($this->postTypes)) {
            if (!in_array($post->post_type, $this->postTypes, true)) {
                return;
            }
        } elseif ($post->post_type !== $this->postTypes) {
            return;
        }

        // Get field keys for DataHydrator
        $fieldKeys = array_map(function ($field) {
            return $field['id'] ?? '';
        }, $this->fields);
        $fieldKeys = array_filter($fieldKeys);

        // Use DataHydrator to extract and filter POST data
        $data = DataHydrator::filterByFields($_POST, $fieldKeys);

        // Save fields
        foreach ($this->fields as $field) {
            $fieldId = $field['id'] ?? '';

            if (empty($fieldId) || !isset($data[$fieldId])) {
                continue;
            }

            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $value = wp_unslash($data[$fieldId]);

            // Sanitize based on field type
            $value = $this->sanitizeFieldValue($value, $field);

            // Update or delete meta
            if ($value !== '') {
                update_post_meta($postId, $fieldId, $value);
            } else {
                delete_post_meta($postId, $fieldId);
            }
        }
    }

    /**
     * Sanitize field value based on type
     *
     * @param mixed $value Field value
     * @param array<string, mixed> $field Field configuration
     * @return mixed
     */
    private function sanitizeFieldValue($value, array $field)
    {
        $fieldType = $field['type'] ?? 'text';

        // Use SecurityTrait for sanitization
        return $this->sanitizeByType($value, $fieldType);
    }

    /**
     * Add field to meta box
     *
     * @param array<string, mixed> $field Field configuration
     * @return self
     */
    public function addField(array $field): self
    {
        $this->fields[] = $field;
        return $this;
    }

    /**
     * Set context
     *
     * @param string $context Context (normal, side, advanced)
     * @return self
     */
    public function setContext(string $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Set priority
     *
     * @param string $priority Priority (high, low, default)
     * @return self
     */
    public function setPriority(string $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Get ID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
