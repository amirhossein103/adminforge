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
use AdminForge\Security\SecurityTrait;

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
    use SecurityTrait;

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
     * Page configuration
     *
     * @var array<string, mixed>
     */
    private array $config = [];

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
        $this->config = $config;

        // Set properties from config
        $this->pageTitle = $title;
        $this->menuTitle = $config['menu_title'] ?? $title;
        $this->capability = $config['capability'] ?? 'manage_options';
        $this->slug = $config['slug'] ?? sanitize_title($settingsGroup);
        $this->icon = $config['icon'] ?? 'dashicons-admin-generic';
        $this->position = $config['position'] ?? null;

        parent::__construct();

        // Hook into save action
        add_action('admin_post_' . $this->slug . '_save', [$this, 'handleSave']);
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
     * Get page title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->pageTitle;
    }

    /**
     * Get page description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->config['description'] ?? '';
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
            <h1><?php echo esc_html($this->pageTitle); ?></h1>

            <?php if (!empty($this->getDescription())): ?>
                <p class="description"><?php echo esc_html($this->getDescription()); ?></p>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field($this->slug . '_save', $this->slug . '_nonce'); ?>
                <input type="hidden" name="action" value="<?php echo esc_attr($this->slug . '_save'); ?>">

                <table class="form-table" role="presentation">
                    <?php foreach ($this->fields as $field): ?>
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr($field->getId()); ?>">
                                    <?php echo esc_html($field->getLabel()); ?>
                                </label>
                            </th>
                            <td>
                                <?php $field->render(); ?>
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
        if (!isset($_POST[$this->slug . '_nonce'])) {
            ErrorHandler::error('Nonce not set for settings page: ' . $this->slug);
            wp_die(__('Security check failed', 'adminforge'));
        }

        $nonce = $this->sanitizeText(wp_unslash($_POST[$this->slug . '_nonce']));
        if (!$this->verifyNonce($nonce, $this->slug . '_save')) {
            ErrorHandler::error('Nonce verification failed for settings page: ' . $this->slug);
            wp_die(__('Security check failed', 'adminforge'));
        }

        // Check user capabilities
        if (!$this->userCan('manage_options')) {
            wp_die(__('You do not have permission to save settings', 'adminforge'));
        }

        $group = Settings::group($this->settingsGroup);
        $errors = [];
        $saved = 0;

        // Process each field
        foreach ($this->fields as $field) {
            $key = $field->getId();

            // Get value from POST (for checkboxes, null means unchecked)
            $rawValue = $_POST[$key] ?? null;

            // Sanitize using field's sanitize method
            $value = $field->sanitize($rawValue);

            // Validate using field's validate method
            if (!$field->validate($value)) {
                $errors[] = $field->getLabel();
                ErrorHandler::validationError(
                    $key,
                    sprintf('Validation failed for setting: %s', $field->getLabel())
                );
                continue;
            }

            // Save to Settings API
            if ($group->set($key, $value)) {
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
            'page' => $this->slug,
            'saved' => $saved,
            'errors' => count($errors),
        ], admin_url('admin.php'));

        wp_redirect($redirect);
        exit;
    }
}
