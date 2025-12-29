<?php
/**
 * Checkbox Field Class
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Fields;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * CheckboxField class
 */
class CheckboxField extends BaseField
{
    /**
     * Checkbox label (inline label next to checkbox)
     *
     * @var string
     */
    protected string $checkboxLabel;

    /**
     * Constructor
     *
     * @param string $id Field ID
     * @param array<string, mixed> $args Field arguments
     */
    public function __construct(string $id, array $args = [])
    {
        parent::__construct($id, $args);
        $this->checkboxLabel = $args['checkbox_label'] ?? '';
    }

    /**
     * Render checkbox field
     *
     * @return void
     */
    protected function renderField(): void
    {
        printf(
            '<label class="adminforge-checkbox-label"><input type="checkbox" id="%s" name="%s" value="1" %s %s /> %s</label>',
            esc_attr($this->id),
            esc_attr($this->name),
            checked($this->getValue(), 1, false),
            $this->getAttributesString(),
            esc_html($this->checkboxLabel)
        );
    }

    /**
     * Sanitize checkbox value
     *
     * @param mixed $value Field value
     * @return int
     */
    public function sanitize($value)
    {
        return $value ? 1 : 0;
    }
}
