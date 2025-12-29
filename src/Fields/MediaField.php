<?php
/**
 * Media Field Class (WordPress Media Uploader)
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Fields;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * MediaField class
 */
class MediaField extends BaseField
{
    /**
     * Media type (image, video, audio, file)
     *
     * @var string
     */
    protected string $mediaType;

    /**
     * Button text
     *
     * @var string
     */
    protected string $buttonText;

    /**
     * Show preview
     *
     * @var bool
     */
    protected bool $showPreview;

    /**
     * Constructor
     *
     * @param string $id Field ID
     * @param array<string, mixed> $args Field arguments
     */
    public function __construct(string $id, array $args = [])
    {
        parent::__construct($id, $args);
        $this->mediaType = $args['media_type'] ?? 'image';
        $this->buttonText = $args['button_text'] ?? __('Select Media', 'adminforge');
        $this->showPreview = $args['show_preview'] ?? true;
    }

    /**
     * Render media field
     *
     * @return void
     */
    protected function renderField(): void
    {
        $value = $this->getValue();

        echo '<div class="adminforge-media-field">';

        // Hidden input for URL
        printf(
            '<input type="hidden" id="%s" name="%s" class="adminforge-upload-input" value="%s" />',
            esc_attr($this->id),
            esc_attr($this->name),
            esc_url($value)
        );

        // Upload button
        printf(
            '<button type="button" class="button adminforge-upload-btn" data-media-type="%s">%s</button>',
            esc_attr($this->mediaType),
            esc_html($this->buttonText)
        );

        // Remove button
        if (!empty($value)) {
            echo ' <button type="button" class="button adminforge-remove-upload">' . esc_html__('Remove', 'adminforge') . '</button>';
        }

        // Preview
        if ($this->showPreview && !empty($value)) {
            echo '<div class="adminforge-upload-preview">';

            if ($this->mediaType === 'image') {
                printf('<img src="%s" style="max-width: 200px; margin-top: 10px; display: block;" />', esc_url($value));
            } else {
                printf('<a href="%s" target="_blank">%s</a>', esc_url($value), esc_html__('View File', 'adminforge'));
            }

            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Sanitize media URL
     *
     * @param mixed $value Field value
     * @return string
     */
    public function sanitize($value)
    {
        return esc_url_raw($value);
    }

    /**
     * Validate media URL
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

        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
}
