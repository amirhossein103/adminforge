<?php
/**
 * SubMenu Page Class
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Admin;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * SubMenuPage class for submenu admin pages
 */
class SubMenuPage extends BasePage
{
    /**
     * Parent menu slug
     *
     * @var string
     */
    protected string $parentSlug;

    /**
     * Constructor
     *
     * @param string $parentSlug Parent menu slug
     */
    public function __construct(string $parentSlug = '')
    {
        $this->parentSlug = $parentSlug;
        parent::__construct();
    }

    /**
     * Register submenu page
     *
     * @return void
     */
    public function register(): void
    {
        $this->hookSuffix = add_submenu_page(
            $this->parentSlug,
            $this->pageTitle,
            $this->menuTitle,
            $this->capability,
            $this->slug,
            [$this, 'renderPage']
        );

        // Add hook for assets
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Render page wrapper
     *
     * @return void
     */
    public function renderPage(): void
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

    /**
     * Set parent slug
     *
     * @param string $parentSlug Parent menu slug
     * @return self
     */
    public function setParentSlug(string $parentSlug): self
    {
        $this->parentSlug = $parentSlug;
        return $this;
    }

    /**
     * Get parent slug
     *
     * @return string
     */
    public function getParentSlug(): string
    {
        return $this->parentSlug;
    }
}
