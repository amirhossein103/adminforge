<?php
/**
 * Tab Manager Class
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Admin;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * TabManager class for tabbed interfaces
 */
class TabManager
{
    /**
     * Tabs configuration
     *
     * @var array<string, array<string, mixed>>
     */
    private array $tabs = [];

    /**
     * Active tab ID
     *
     * @var string
     */
    private string $activeTab;

    /**
     * Constructor
     *
     * @param array<string, array<string, mixed>> $tabs Tabs configuration
     */
    public function __construct(array $tabs)
    {
        $this->tabs = $tabs;
        $this->activeTab = $this->getActiveTab();
    }

    /**
     * Get active tab from URL parameter or first tab
     *
     * @return string
     */
    private function getActiveTab(): string
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : '';

        // If tab exists in configuration, use it
        if ($tab && isset($this->tabs[$tab])) {
            return $tab;
        }

        // Otherwise return first tab
        $keys = array_keys($this->tabs);
        return $keys[0] ?? '';
    }

    /**
     * Render tabs navigation and content
     *
     * @return void
     */
    public function render(): void
    {
        if (empty($this->tabs)) {
            return;
        }

        $this->renderNavigation();
        $this->renderContent();
    }

    /**
     * Render tabs navigation
     *
     * @return void
     */
    private function renderNavigation(): void
    {
        echo '<div class="adminforge-tabs">';

        foreach ($this->tabs as $tabId => $tab) {
            $title = $tab['title'] ?? ucfirst($tabId);
            $icon = $tab['icon'] ?? '';
            $active = $tabId === $this->activeTab ? 'active' : '';

            $url = add_query_arg('tab', $tabId);

            echo '<button class="adminforge-tab ' . esc_attr($active) . '" ';
            echo 'data-tab="' . esc_attr($tabId) . '" ';
            echo 'data-url="' . esc_url($url) . '">';

            if ($icon) {
                echo '<span class="dashicons ' . esc_attr($icon) . '"></span> ';
            }

            echo esc_html($title);
            echo '</button>';
        }

        echo '</div>';
    }

    /**
     * Render tabs content
     *
     * @return void
     */
    private function renderContent(): void
    {
        echo '<div class="adminforge-content">';

        foreach ($this->tabs as $tabId => $tab) {
            $active = $tabId === $this->activeTab ? 'active' : '';

            echo '<div id="' . esc_attr($tabId) . '" class="adminforge-tab-content ' . esc_attr($active) . '">';

            // Render tab content
            if (isset($tab['callback']) && is_callable($tab['callback'])) {
                call_user_func($tab['callback']);
            } elseif (isset($tab['content'])) {
                echo wp_kses_post($tab['content']);
            } else {
                echo '<p>' . esc_html__('No content defined for this tab.', 'adminforge') . '</p>';
            }

            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Add a new tab
     *
     * @param string $id Tab ID
     * @param array<string, mixed> $config Tab configuration
     * @return self
     */
    public function addTab(string $id, array $config): self
    {
        $this->tabs[$id] = $config;
        return $this;
    }

    /**
     * Remove a tab
     *
     * @param string $id Tab ID
     * @return self
     */
    public function removeTab(string $id): self
    {
        unset($this->tabs[$id]);
        return $this;
    }

    /**
     * Get all tabs
     *
     * @return array<string, array<string, mixed>>
     */
    public function getTabs(): array
    {
        return $this->tabs;
    }

    /**
     * Get active tab ID
     *
     * @return string
     */
    public function getActiveTabId(): string
    {
        return $this->activeTab;
    }

    /**
     * Check if tab exists
     *
     * @param string $id Tab ID
     * @return bool
     */
    public function hasTab(string $id): bool
    {
        return isset($this->tabs[$id]);
    }
}
