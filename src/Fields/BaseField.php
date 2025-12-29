<?php
/**
 * Base Field Abstract Class
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Fields;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Abstract BaseField class
 */
abstract class BaseField implements FieldInterface
{
    /**
     * Field ID
     *
     * @var string
     */
    protected string $id;

    /**
     * Field name attribute
     *
     * @var string
     */
    protected string $name;

    /**
     * Field label
     *
     * @var string
     */
    protected string $label;

    /**
     * Field value
     *
     * @var mixed
     */
    protected $value;

    /**
     * Field placeholder
     *
     * @var string
     */
    protected string $placeholder;

    /**
     * Field description/hint
     *
     * @var string
     */
    protected string $description;

    /**
     * Default value
     *
     * @var mixed
     */
    protected $default;

    /**
     * CSS classes
     *
     * @var array<string>
     */
    protected array $classes;

    /**
     * HTML attributes
     *
     * @var array<string, string>
     */
    protected array $attributes;

    /**
     * Is field required
     *
     * @var bool
     */
    protected bool $required;

    /**
     * Conditional logic
     *
     * @var array<string, mixed>
     */
    protected array $condition;

    /**
     * Constructor
     *
     * @param string $id Field ID
     * @param array<string, mixed> $args Field arguments
     */
    public function __construct(string $id, array $args = [])
    {
        $this->id = $id;
        $this->name = $args['name'] ?? $id;
        $this->label = $args['label'] ?? '';
        $this->value = $args['value'] ?? ($args['default'] ?? '');
        $this->placeholder = $args['placeholder'] ?? '';
        $this->description = $args['description'] ?? '';
        $this->default = $args['default'] ?? '';
        $this->classes = $args['classes'] ?? [];
        $this->attributes = $args['attributes'] ?? [];
        $this->required = $args['required'] ?? false;
        $this->condition = $args['condition'] ?? [];
    }

    /**
     * Render field (only input without wrapper)
     *
     * @return void
     */
    public function render(): void
    {
        $this->renderField();

        // Render description if available
        if (!empty($this->description)) {
            $this->renderDescription();
        }
    }

    /**
     * Render field with full wrapper (label + input + description)
     *
     * @return void
     */
    public function renderWithWrapper(): void
    {
        $wrapperClasses = array_merge(['adminforge-field'], $this->classes);
        $conditionalAttr = $this->getConditionalAttribute();

        echo '<div class="' . esc_attr(implode(' ', $wrapperClasses)) . '" ' . $conditionalAttr . '>';

        // Render label
        if (!empty($this->label)) {
            $this->renderLabel();
        }

        // Render field input
        $this->renderField();

        // Render description
        if (!empty($this->description)) {
            $this->renderDescription();
        }

        echo '</div>';
    }

    /**
     * Render label
     *
     * @return void
     */
    public function renderLabel(): void
    {
        echo '<label for="' . esc_attr($this->id) . '">';
        echo esc_html($this->label);

        if ($this->required) {
            echo ' <span class="required" aria-label="' . esc_attr__('required', 'adminforge') . '">*</span>';
        }

        echo '</label>';
    }

    /**
     * Render field input (must be implemented by child classes)
     *
     * @return void
     */
    abstract protected function renderField(): void;

    /**
     * Render description
     *
     * @return void
     */
    protected function renderDescription(): void
    {
        echo '<span class="adminforge-field-hint">' . esc_html($this->description) . '</span>';
    }

    /**
     * Get conditional logic attribute
     *
     * @return string
     */
    protected function getConditionalAttribute(): string
    {
        if (empty($this->condition)) {
            return '';
        }

        $json = wp_json_encode($this->condition);
        if ($json === false) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('AdminForge: Failed to encode conditional logic for field ' . $this->id);
            }
            return '';
        }

        return 'data-condition=\'' . esc_attr($json) . '\'';
    }

    /**
     * Get HTML attributes as string
     *
     * @return string
     */
    protected function getAttributesString(): string
    {
        $attrs = [];

        foreach ($this->attributes as $key => $value) {
            $attrs[] = esc_attr($key) . '="' . esc_attr($value) . '"';
        }

        if ($this->required) {
            $attrs[] = 'required';
            $attrs[] = 'aria-required="true"';
        }

        return implode(' ', $attrs);
    }

    /**
     * Default sanitization
     *
     * @param mixed $value Field value
     * @return mixed
     */
    public function sanitize($value)
    {
        return sanitize_text_field($value);
    }

    /**
     * Default validation
     *
     * @param mixed $value Field value
     * @return bool
     */
    public function validate($value): bool
    {
        // If required, check if value is not empty
        if ($this->required && empty($value)) {
            return false;
        }

        return true;
    }

    /**
     * Get field ID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get field name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get field value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value ?? $this->default;
    }

    /**
     * Set field value
     *
     * @param mixed $value Field value
     * @return self
     */
    public function setValue($value): FieldInterface
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get field label
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Set required
     *
     * @param bool $required Is required
     * @return self
     */
    public function setRequired(bool $required): self
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Set condition
     *
     * @param array<string, mixed> $condition Conditional logic
     * @return self
     */
    public function setCondition(array $condition): self
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * Add CSS class
     *
     * @param string $class CSS class
     * @return self
     */
    public function addClass(string $class): self
    {
        $this->classes[] = $class;
        return $this;
    }

    /**
     * Add HTML attribute
     *
     * @param string $key Attribute key
     * @param string $value Attribute value
     * @return self
     */
    public function addAttribute(string $key, string $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }
}
