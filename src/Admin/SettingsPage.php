<?php
/**
 * Settings Page Class
 *
 * Integrates Settings API with Fields system for automatic form rendering
 *
 * @package AdminForge
 * @since 2.0.0
 */

declare(strict_types=1);

namespace AdminForge\Admin;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

use AdminForge\Settings\Settings;
use AdminForge\Fields\FieldFactory;
use AdminForge\Fields\FieldInterface;
use AdminForge\Core\ErrorHandler;

/**
 * SettingsPage class - Extends MenuPage with Settings integration
 *
 * @example
 * $page = new SettingsPage('My Settings', 'my_settings');
 * $page->addField('text', 'api_key', 'API Key');
 * $page->addField('checkbox', 'enable_cache', 'Enable Caching');
 */
class SettingsPage extends MenuPage
{
    /**
     * Settings group (used as prefix for Settings API)
     *
     * @var string
     */
    private string $settingsGroup;

    /**
     * Fields in this settings page
     *
     * @var array<FieldInterface>
     */
    private array $fields = [];

    /**
     * Constructor
     *
     * @param string $title Page title
     * @param string $settingsGroup Settings group identifier
     * @param array<string, mixed> $config Page configuration
     */
    public function __construct(string $title, string $settingsGroup, array $config = [])
    {
        $this->settingsGroup = $settingsGroup;

        // Set default slug if not provided
        if (!isset($config['slug'])) {
            $config['slug'] = sanitize_title($settingsGroup);
        }

        parent::__construct($title, $config);

        // Hook into save action
        add_action('admin_post_' . $this->getSlug() . '_save', [$this, 'handleSave']);
    }

    /**
     * Add field to settings page
     *
     * @param string $type Field type
     * @param string $key Setting key (without group prefix)
     * @param string $label Field label
     * @param array<string, mixed> $config Field configuration
     * @return self
     */
    public function addField(string $type, string $key, string $label, array $config = []): self
    {
        // Merge config with defaults
        $config = array_merge([
            'id' => $key,
            'label' => $label,
        ], $config);

        // Create field
        $field = FieldFactory::create($type, $config);

        if ($field === null) {
            ErrorHandler::warning(
                sprintf('Failed to create field of type "%s" for key "%s"', $type, $key),
                ['settings_group' => $this->settingsGroup]
            );
            return $this;
        }

        // Set current value from Settings
        $currentValue = Settings::group($this->settingsGroup)->get($key);
        if ($currentValue !== null) {
            $field->setValue($currentValue);
        }

        $this->fields[] = $field;

        return $this;
    }

    /**
     * Get settings group
     *
     * @return string
     */
    public function getSettingsGroup(): string
    {
        return $this->settingsGroup;
    }

    /**
     * Get all fields
     *
     * @return array<FieldInterface>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Render page content
     *
     * @return void
     */
    public function render(): void
    {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html($this->getTitle()); ?></h1>

            <?php if (!empty($this->getDescription())): ?>
                <p class="description"><?php echo esc_html($this->getDescription()); ?></p>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field($this->getSlug() . '_save', $this->getSlug() . '_nonce'); ?>
                <input type="hidden" name="action" value="<?php echo esc_attr($this->getSlug() . '_save'); ?>">

                <table class="form-table" role="presentation">
                    <?php foreach ($this->fields as $field): ?>
                        <tr>
                            <th scope="row">
                                <?php echo $field->renderLabel(); ?>
                            </th>
                            <td>
                                <?php echo $field->render(); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <?php submit_button(__('Save Settings', 'adminforge')); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Handle form submission
     *
     * @return void
     */
    public function handleSave(): void
    {
        // Verify nonce
        if (!isset($_POST[$this->getSlug() . '_nonce']) ||
            !wp_verify_nonce($_POST[$this->getSlug() . '_nonce'], $this->getSlug() . '_save')) {
            ErrorHandler::error('Nonce verification failed for settings page: ' . $this->getSlug());
            wp_die(__('Security check failed', 'adminforge'));
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to save settings', 'adminforge'));
        }

        $group = Settings::group($this->settingsGroup);
        $errors = [];
        $saved = 0;

        // Process each field
        foreach ($this->fields as $field) {
            $key = $field->getId();
            $value = $field->getValue();

            // Get field config for validation
            $config = $field->getConfig();

            // Save to Settings API
            $options = [];
            if (isset($config['validate'])) {
                $options['validate'] = $config['validate'];
            }
            if (isset($config['sanitize'])) {
                $options['sanitize'] = $config['sanitize'];
            }
            if (isset($config['type'])) {
                $options['type'] = $config['type'];
            }

            if ($group->set($key, $value, $options)) {
                $saved++;
            } else {
                $errors[] = $field->getLabel();
                ErrorHandler::validationError(
                    $key,
                    sprintf('Failed to save setting: %s', $field->getLabel())
                );
            }
        }

        // Redirect with status
        $redirect = add_query_arg([
            'page' => $this->getSlug(),
            'saved' => $saved,
            'errors' => count($errors),
        ], admin_url('admin.php'));

        wp_redirect($redirect);
        exit;
    }

    /**
     * Get page slug
     *
     * @return string
     */
    private function getSlug(): string
    {
        return $this->config['slug'] ?? sanitize_title($this->title);
    }

    /**
     * Get page description
     *
     * @return string
     */
    private function getDescription(): string
    {
        return $this->config['description'] ?? '';
    }
}
