<?php
/**
 * Select Field Class
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Fields;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * SelectField class
 */
class SelectField extends BaseField
{
    /**
     * Field options
     *
     * @var array<string|int, string>
     */
    protected array $options;

    /**
     * Allow multiple selection
     *
     * @var bool
     */
    protected bool $multiple;

    /**
     * Constructor
     *
     * @param string $id Field ID
     * @param array<string, mixed> $args Field arguments
     */
    public function __construct(string $id, array $args = [])
    {
        parent::__construct($id, $args);
        $this->options = $args['options'] ?? [];
        $this->multiple = $args['multiple'] ?? false;
    }

    /**
     * Render select field
     *
     * @return void
     */
    protected function renderField(): void
    {
        $name = $this->multiple ? $this->name . '[]' : $this->name;
        $multipleAttr = $this->multiple ? 'multiple' : '';

        printf(
            '<select id="%s" name="%s" %s %s>',
            esc_attr($this->id),
            esc_attr($name),
            $multipleAttr,
            $this->getAttributesString()
        );

        foreach ($this->options as $optionValue => $optionLabel) {
            $selected = $this->isSelected($optionValue);

            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr($optionValue),
                selected($selected, true, false),
                esc_html($optionLabel)
            );
        }

        echo '</select>';
    }

    /**
     * Check if option is selected
     *
     * @param string|int $optionValue Option value
     * @return bool
     */
    protected function isSelected($optionValue): bool
    {
        $value = $this->getValue();

        if ($this->multiple && is_array($value)) {
            return in_array($optionValue, $value, true);
        }

        return $value === $optionValue || (string) $value === (string) $optionValue;
    }

    /**
     * Sanitize select value
     *
     * @param mixed $value Field value
     * @return mixed
     */
    public function sanitize($value)
    {
        if ($this->multiple && is_array($value)) {
            return array_map('sanitize_text_field', $value);
        }

        return sanitize_text_field($value);
    }

    /**
     * Validate select value
     *
     * @param mixed $value Field value
     * @return bool
     */
    public function validate($value): bool
    {
        if (!parent::validate($value)) {
            return false;
        }

        // Check if value exists in options
        if ($this->multiple && is_array($value)) {
            foreach ($value as $val) {
                if (!array_key_exists($val, $this->options)) {
                    return false;
                }
            }
            return true;
        }

        return array_key_exists($value, $this->options);
    }
}
