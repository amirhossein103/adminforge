<?php
/**
 * Text Field Class
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Fields;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * TextField class
 */
class TextField extends BaseField
{
    /**
     * Input type (text, email, url, number, tel, password)
     *
     * @var string
     */
    protected string $type;

    /**
     * Constructor
     *
     * @param string $id Field ID
     * @param array<string, mixed> $args Field arguments
     */
    public function __construct(string $id, array $args = [])
    {
        parent::__construct($id, $args);
        $this->type = $args['type'] ?? 'text';
    }

    /**
     * Render text field
     *
     * @return void
     */
    protected function renderField(): void
    {
        printf(
            '<input type="%s" id="%s" name="%s" value="%s" placeholder="%s" %s />',
            esc_attr($this->type),
            esc_attr($this->id),
            esc_attr($this->name),
            esc_attr($this->getValue()),
            esc_attr($this->placeholder),
            $this->getAttributesString()
        );
    }

    /**
     * Sanitize based on type
     *
     * @param mixed $value Field value
     * @return mixed
     */
    public function sanitize($value)
    {
        switch ($this->type) {
            case 'email':
                return sanitize_email($value);

            case 'url':
                return esc_url_raw($value);

            case 'number':
                return is_numeric($value) ? $value : 0;

            case 'tel':
                return sanitize_text_field($value);

            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * Validate based on type
     *
     * @param mixed $value Field value
     * @return bool
     */
    public function validate($value): bool
    {
        if (!parent::validate($value)) {
            return false;
        }

        switch ($this->type) {
            case 'email':
                return is_email($value);

            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) !== false;

            case 'number':
                return is_numeric($value);

            default:
                return true;
        }
    }
}
