<?php
/**
 * WordPress Editor Field Class
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Fields;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WPEditorField class
 */
class WPEditorField extends BaseField
{
    /**
     * Editor settings
     *
     * @var array<string, mixed>
     */
    protected array $settings;

    /**
     * Constructor
     *
     * @param string $id Field ID
     * @param array<string, mixed> $args Field arguments
     */
    public function __construct(string $id, array $args = [])
    {
        parent::__construct($id, $args);

        // Default editor settings
        $this->settings = array_merge([
            'textarea_name' => $this->name,
            'textarea_rows' => 10,
            'teeny' => false,
            'media_buttons' => true,
            'tinymce' => true,
            'quicktags' => true,
        ], $args['settings'] ?? []);
    }

    /**
     * Render WordPress editor
     *
     * @return void
     */
    protected function renderField(): void
    {
        // WordPress editor doesn't use standard label, so we render it separately
        wp_editor(
            $this->getValue(),
            $this->id,
            $this->settings
        );
    }

    /**
     * Override render to handle editor differently
     *
     * @return void
     */
    public function render(): void
    {
        $wrapperClasses = array_merge(['adminforge-field', 'adminforge-field-editor'], $this->classes);
        $conditionalAttr = $this->getConditionalAttribute();

        echo '<div class="' . esc_attr(implode(' ', $wrapperClasses)) . '" ' . $conditionalAttr . '>';

        // Render label
        if (!empty($this->label)) {
            echo '<label>' . esc_html($this->label) . '</label>';
        }

        // Render field
        $this->renderField();

        // Render description
        if (!empty($this->description)) {
            $this->renderDescription();
        }

        echo '</div>';
    }

    /**
     * Sanitize editor content
     *
     * @param mixed $value Field value
     * @return string
     */
    public function sanitize($value)
    {
        // Use wp_kses_post to allow safe HTML
        return wp_kses_post($value);
    }
}
