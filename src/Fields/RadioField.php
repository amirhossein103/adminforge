<?php
/**
 * Radio Field Class
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Fields;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * RadioField class
 */
class RadioField extends BaseField
{
    /**
     * Radio options
     *
     * @var array<string|int, string>
     */
    protected array $options;

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
    }

    /**
     * Render radio field
     *
     * @return void
     */
    protected function renderField(): void
    {
        echo '<div class="adminforge-radio-group">';

        foreach ($this->options as $optionValue => $optionLabel) {
            $radioId = $this->id . '_' . sanitize_key($optionValue);

            printf(
                '<label class="adminforge-radio-label"><input type="radio" id="%s" name="%s" value="%s" %s %s /> %s</label>',
                esc_attr($radioId),
                esc_attr($this->name),
                esc_attr($optionValue),
                checked($this->getValue(), $optionValue, false),
                $this->getAttributesString(),
                esc_html($optionLabel)
            );
        }

        echo '</div>';
    }

    /**
     * Validate radio value
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
        return array_key_exists($value, $this->options);
    }
}
