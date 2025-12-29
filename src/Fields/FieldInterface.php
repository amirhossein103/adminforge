<?php
/**
 * Field Interface
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Fields;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Interface for all field types
 */
interface FieldInterface
{
    /**
     * Render field HTML
     *
     * @return void
     */
    public function render(): void;

    /**
     * Sanitize field value
     *
     * @param mixed $value Field value
     * @return mixed Sanitized value
     */
    public function sanitize($value);

    /**
     * Get field ID
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Get field name attribute
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get field value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set field value
     *
     * @param mixed $value Field value
     * @return self
     */
    public function setValue($value): self;

    /**
     * Get field label
     *
     * @return string
     */
    public function getLabel(): string;

    /**
     * Validate field value
     *
     * @param mixed $value Field value
     * @return bool
     */
    public function validate($value): bool;

    /**
     * Render label
     *
     * @return void
     */
    public function renderLabel(): void;
}
