<?php
/**
 * Textarea Field Class
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Fields;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * TextareaField class
 */
class TextareaField extends BaseField
{
    /**
     * Number of rows
     *
     * @var int
     */
    protected int $rows;

    /**
     * Number of columns
     *
     * @var int
     */
    protected int $cols;

    /**
     * Constructor
     *
     * @param string $id Field ID
     * @param array<string, mixed> $args Field arguments
     */
    public function __construct(string $id, array $args = [])
    {
        parent::__construct($id, $args);
        $this->rows = $args['rows'] ?? 5;
        $this->cols = $args['cols'] ?? 50;
    }

    /**
     * Render textarea field
     *
     * @return void
     */
    protected function renderField(): void
    {
        printf(
            '<textarea id="%s" name="%s" rows="%d" cols="%d" placeholder="%s" %s>%s</textarea>',
            esc_attr($this->id),
            esc_attr($this->name),
            (int) $this->rows,
            (int) $this->cols,
            esc_attr($this->placeholder),
            $this->getAttributesString(),
            esc_textarea($this->getValue())
        );
    }

    /**
     * Sanitize textarea value
     *
     * @param mixed $value Field value
     * @return string
     */
    public function sanitize($value)
    {
        return sanitize_textarea_field($value);
    }
}
