<?php
/**
 * Color Field Class (WordPress Color Picker)
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Fields;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * ColorField class
 */
class ColorField extends BaseField
{
    /**
     * Default color
     *
     * @var string
     */
    protected string $defaultColor;

    /**
     * Constructor
     *
     * @param string $id Field ID
     * @param array<string, mixed> $args Field arguments
     */
    public function __construct(string $id, array $args = [])
    {
        parent::__construct($id, $args);
        $this->defaultColor = $args['default_color'] ?? '#000000';
    }

    /**
     * Render color field
     *
     * @return void
     */
    protected function renderField(): void
    {
        printf(
            '<input type="text" id="%s" name="%s" value="%s" class="adminforge-color-picker" data-default-color="%s" %s />',
            esc_attr($this->id),
            esc_attr($this->name),
            esc_attr($this->getValue() ?: $this->defaultColor),
            esc_attr($this->defaultColor),
            $this->getAttributesString()
        );
    }

    /**
     * Sanitize color value
     *
     * @param mixed $value Field value
     * @return string
     */
    public function sanitize($value)
    {
        // Validate hex color
        if (preg_match('/^#[a-f0-9]{6}$/i', $value)) {
            return $value;
        }

        return $this->defaultColor;
    }

    /**
     * Validate color value
     *
     * @param mixed $value Field value
     * @return bool
     */
    public function validate($value): bool
    {
        if (!parent::validate($value)) {
            return false;
        }

        if (empty($value)) {
            return true;
        }

        // Check if valid hex color
        return (bool) preg_match('/^#[a-f0-9]{6}$/i', $value);
    }
}
