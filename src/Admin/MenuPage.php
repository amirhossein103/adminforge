<?php
/**
 * Menu Page Class
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Admin;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

use AdminForge\Core\Config;

/**
 * MenuPage class for top-level admin menu pages
 */
class MenuPage extends BasePage
{
    /**
     * Menu icon (dashicon class or image URL)
     *
     * @var string
     */
    protected string $icon;

    /**
     * Menu position
     *
     * @var int|null
     */
    protected ?int $position;

    /**
     * Set defaults
     *
     * @return void
     */
    protected function setDefaults(): void
    {
        parent::setDefaults();

        if (empty($this->icon)) {
            $this->icon = Config::get('menu.icon', 'dashicons-admin-generic');
        }
        if ($this->position === null) {
            $this->position = Config::get('menu.position', 99);
        }
    }

    /**
     * Register menu page
     *
     * @return void
     */
    public function register(): void
    {
        $this->hookSuffix = add_menu_page(
            $this->pageTitle,
            $this->menuTitle,
            $this->capability,
            $this->slug,
            [$this, 'renderPage'],
            $this->icon,
            $this->position
        );

        // Add hook for assets
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Render page wrapper (WordPress callback)
     *
     * @return void
     */
    private function renderPage(): void
    {
        if (!$this->canAccess()) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'adminforge'));
        }

        $this->renderHeader();

        // Render tabs if available
        if ($this->tabManager !== null) {
            $this->tabManager->render();
        } else {
            $this->render();
        }

        $this->renderFooter();
    }

    /**
     * Render method (must be implemented by child or use tabs)
     *
     * @return void
     */
    public function render(): void
    {
        echo '<div class="adminforge-content">';
        echo '<p>' . esc_html__('Please override the render() method or add tabs.', 'adminforge') . '</p>';
        echo '</div>';
    }
}
