<?php
/**
 * Repeater Field Class
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Fields;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

use AdminForge\Core\Constants;

/**
 * RepeaterField class - Complex field with sub-fields
 */
class RepeaterField extends BaseField
{
    /**
     * Sub-fields configuration
     *
     * @var array<array<string, mixed>>
     */
    protected array $fields;

    /**
     * Maximum rows allowed
     *
     * @var int
     */
    protected int $maxRows;

    /**
     * Minimum rows required
     *
     * @var int
     */
    protected int $minRows;

    /**
     * Button text for adding rows
     *
     * @var string
     */
    protected string $addButtonText;

    /**
     * Allow sorting/drag-drop
     *
     * @var bool
     */
    protected bool $sortable;

    /**
     * Constructor
     *
     * @param string $id Field ID
     * @param array<string, mixed> $args Field arguments
     */
    public function __construct(string $id, array $args = [])
    {
        parent::__construct($id, $args);
        $this->fields = $args['fields'] ?? [];
        $this->maxRows = $args['max_rows'] ?? Constants::MAX_REPEATER_ROWS;
        $this->minRows = $args['min_rows'] ?? Constants::MIN_REPEATER_ROWS;
        $this->addButtonText = $args['add_button_text'] ?? __('Add Row', 'adminforge');
        $this->sortable = $args['sortable'] ?? true;
    }

    /**
     * Render repeater field
     *
     * @return void
     */
    protected function renderField(): void
    {
        $value = $this->getValue();
        $value = is_array($value) ? $value : [];

        $sortableClass = $this->sortable ? 'adminforge-repeater-sortable' : '';

        echo '<div class="adminforge-repeater-wrapper" data-max-rows="' . esc_attr($this->maxRows) . '">';
        echo '<div class="adminforge-repeater-rows ' . esc_attr($sortableClass) . '" data-field-id="' . esc_attr($this->id) . '">';

        // Render existing rows
        if (!empty($value)) {
            foreach ($value as $index => $rowData) {
                $this->renderRow($index, $rowData);
            }
        } else {
            // Render minimum rows
            for ($i = 0; $i < $this->minRows; $i++) {
                $this->renderRow($i, []);
            }
        }

        echo '</div>'; // .adminforge-repeater-rows

        // Add row button
        echo '<button type="button" class="button adminforge-repeater-add" data-field-id="' . esc_attr($this->id) . '">';
        echo esc_html($this->addButtonText);
        echo '</button>';

        // Template for new rows (hidden)
        echo '<script type="text/html" id="' . esc_attr($this->id) . '-template">';
        $this->renderRow('{{INDEX}}', []);
        echo '</script>';

        echo '</div>'; // .adminforge-repeater-wrapper
    }

    /**
     * Render single repeater row
     *
     * @param int|string $index Row index
     * @param array<string, mixed> $rowData Row data
     * @return void
     */
    protected function renderRow($index, array $rowData): void
    {
        echo '<div class="adminforge-repeater-row">';

        // Drag handle (if sortable)
        if ($this->sortable) {
            echo '<div class="adminforge-repeater-handle">';
            echo '<span class="dashicons dashicons-menu"></span>';
            echo '</div>';
        }

        echo '<div class="adminforge-repeater-content">';

        // Render sub-fields
        foreach ($this->fields as $fieldConfig) {
            $fieldId = $fieldConfig['id'] ?? '';
            $fieldType = $fieldConfig['type'] ?? 'text';
            $fieldLabel = $fieldConfig['label'] ?? '';
            $fieldValue = $rowData[$fieldId] ?? ($fieldConfig['default'] ?? '');

            // Create field name with array notation
            $fieldName = $this->name . '[' . $index . '][' . $fieldId . ']';
            $uniqueFieldId = $this->id . '_' . $index . '_' . $fieldId;

            echo '<div class="adminforge-repeater-field">';

            if ($fieldLabel) {
                echo '<label for="' . esc_attr($uniqueFieldId) . '">' . esc_html($fieldLabel) . '</label>';
            }

            // Use FieldFactory if available, otherwise render basic input
            if (class_exists('AdminForge\Fields\FieldFactory')) {
                $field = FieldFactory::create($fieldType, $uniqueFieldId, array_merge($fieldConfig, [
                    'name' => $fieldName,
                    'value' => $fieldValue,
                    'label' => '', // Already rendered
                ]));

                if ($field) {
                    $field->renderField();
                }
            } else {
                $this->renderBasicInput($uniqueFieldId, $fieldName, $fieldType, $fieldValue, $fieldConfig);
            }

            echo '</div>'; // .adminforge-repeater-field
        }

        echo '</div>'; // .adminforge-repeater-content

        // Remove button
        echo '<div class="adminforge-repeater-actions">';
        echo '<button type="button" class="button adminforge-repeater-remove">';
        echo '<span class="dashicons dashicons-trash"></span>';
        echo '</button>';
        echo '</div>';

        echo '</div>'; // .adminforge-repeater-row
    }

    /**
     * Render basic input (fallback if FieldFactory not available)
     *
     * @param string $id Field ID
     * @param string $name Field name
     * @param string $type Field type
     * @param mixed $value Field value
     * @param array<string, mixed> $config Field config
     * @return void
     */
    protected function renderBasicInput(string $id, string $name, string $type, $value, array $config): void
    {
        $placeholder = $config['placeholder'] ?? '';

        switch ($type) {
            case 'textarea':
                printf(
                    '<textarea id="%s" name="%s" placeholder="%s">%s</textarea>',
                    esc_attr($id),
                    esc_attr($name),
                    esc_attr($placeholder),
                    esc_textarea($value)
                );
                break;

            case 'select':
                $options = $config['options'] ?? [];
                echo '<select id="' . esc_attr($id) . '" name="' . esc_attr($name) . '">';
                foreach ($options as $optValue => $optLabel) {
                    printf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr($optValue),
                        selected($value, $optValue, false),
                        esc_html($optLabel)
                    );
                }
                echo '</select>';
                break;

            default:
                printf(
                    '<input type="%s" id="%s" name="%s" value="%s" placeholder="%s" />',
                    esc_attr($type),
                    esc_attr($id),
                    esc_attr($name),
                    esc_attr($value),
                    esc_attr($placeholder)
                );
                break;
        }
    }

    /**
     * Sanitize repeater value
     *
     * @param mixed $value Field value
     * @return array<array<string, mixed>>
     */
    public function sanitize($value)
    {
        if (!is_array($value)) {
            return [];
        }

        $sanitized = [];

        foreach ($value as $row) {
            if (!is_array($row)) {
                continue;
            }

            $sanitizedRow = [];

            foreach ($this->fields as $fieldConfig) {
                $fieldId = $fieldConfig['id'] ?? '';
                $fieldType = $fieldConfig['type'] ?? 'text';

                if (!isset($row[$fieldId])) {
                    continue;
                }

                $fieldValue = $row[$fieldId];

                // Sanitize based on field type
                $sanitizedRow[$fieldId] = $this->sanitizeFieldValue($fieldValue, $fieldType);
            }

            $sanitized[] = $sanitizedRow;
        }

        return $sanitized;
    }

    /**
     * Sanitize individual field value
     *
     * @param mixed $value Field value
     * @param string $type Field type
     * @return mixed
     */
    protected function sanitizeFieldValue($value, string $type)
    {
        switch ($type) {
            case 'email':
                return sanitize_email($value);

            case 'url':
                return esc_url_raw($value);

            case 'number':
                return is_numeric($value) ? $value : 0;

            case 'textarea':
                return sanitize_textarea_field($value);

            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * Validate repeater value
     *
     * @param mixed $value Field value
     * @return bool
     */
    public function validate($value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        // Check min/max rows
        $rowCount = count($value);

        if ($rowCount < $this->minRows) {
            return false;
        }

        if ($rowCount > $this->maxRows) {
            return false;
        }

        return true;
    }
}
