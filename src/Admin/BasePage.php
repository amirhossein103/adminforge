<?php
/**
 * Abstract Base Page Class
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
 * Abstract BasePage class for admin pages
 */
abstract class BasePage
{
    /**
     * Page title
     *
     * @var string
     */
    protected string $pageTitle;

    /**
     * Menu title
     *
     * @var string
     */
    protected string $menuTitle;

    /**
     * Required capability
     *
     * @var string
     */
    protected string $capability;

    /**
     * Page slug
     *
     * @var string
     */
    protected string $slug;

    /**
     * Page hook suffix
     *
     * @var string|false
     */
    protected $hookSuffix;

    /**
     * Tab manager instance
     *
     * @var TabManager|null
     */
    protected ?TabManager $tabManager = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setDefaults();
        $this->init();
    }

    /**
     * Set default values from config
     *
     * @return void
     */
    protected function setDefaults(): void
    {
        if (empty($this->pageTitle)) {
            $this->pageTitle = Config::get('menu.title', 'AdminForge');
        }
        if (empty($this->menuTitle)) {
            $this->menuTitle = Config::get('menu.menu_title', 'AdminForge');
        }
        if (empty($this->capability)) {
            $this->capability = Config::get('menu.capability', 'manage_options');
        }
        if (empty($this->slug)) {
            $this->slug = Config::get('menu.slug', 'adminforge');
        }
    }

    /**
     * Initialize page
     * Override this method in child classes
     *
     * @return void
     */
    protected function init(): void
    {
        // Override in child class
    }

    /**
     * Register the page
     * Must be implemented by child classes
     *
     * @return void
     */
    abstract public function register(): void;

    /**
     * Render the page content
     * Must be implemented by child classes
     *
     * @return void
     */
    abstract public function render(): void;

    /**
     * Enqueue page-specific assets
     *
     * @param string $hook Current page hook
     * @return void
     */
    public function enqueueAssets(string $hook): void
    {
        // Only enqueue on this page
        if ($hook !== $this->hookSuffix) {
            return;
        }

        // Override in child class to add specific assets
    }

    /**
     * Add tab manager
     *
     * @param array<string, array<string, mixed>> $tabs Array of tabs configuration
     * @return TabManager
     */
    public function addTabs(array $tabs): TabManager
    {
        if ($this->tabManager === null) {
            $this->tabManager = new TabManager($tabs);
        }

        return $this->tabManager;
    }

    /**
     * Get tab manager
     *
     * @return TabManager|null
     */
    public function getTabManager(): ?TabManager
    {
        return $this->tabManager;
    }

    /**
     * Check if user can access this page
     *
     * @return bool
     */
    protected function canAccess(): bool
    {
        return current_user_can($this->capability);
    }

    /**
     * Render header
     *
     * @return void
     */
    protected function renderHeader(): void
    {
        echo '<div class="adminforge-wrap">';
        echo '<div class="adminforge-header">';
        echo '<h1>' . esc_html($this->pageTitle) . '</h1>';
        echo '</div>';
    }

    /**
     * Render footer
     *
     * @return void
     */
    protected function renderFooter(): void
    {
        echo '</div>'; // .adminforge-wrap
    }

    /**
     * Get page slug
     *
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * Get page title
     *
     * @return string
     */
    public function getPageTitle(): string
    {
        return $this->pageTitle;
    }

    /**
     * Get hook suffix
     *
     * @return string|false
     */
    public function getHookSuffix(): string|false
    {
        return $this->hookSuffix;
    }
}
