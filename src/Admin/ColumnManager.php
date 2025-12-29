<?php
/**
 * Column Manager Class
 *
 * @package AdminForge
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AdminForge\Admin;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * ColumnManager class for managing admin list table columns
 */
final class ColumnManager
{
    /**
     * Post type
     *
     * @var string
     */
    private string $postType;

    /**
     * Custom columns
     *
     * @var array<string, array<string, mixed>>
     */
    private array $columns = [];

    /**
     * Sortable columns
     *
     * @var array<string, string>
     */
    private array $sortableColumns = [];

    /**
     * Constructor
     *
     * @param string $postType Post type
     */
    public function __construct(string $postType)
    {
        $this->postType = $postType;
    }

    /**
     * Register column manager
     *
     * @return self
     */
    public function register(): self
    {
        // Add columns
        add_filter("manage_edit-{$this->postType}_columns", [$this, 'addColumns']);

        // Populate column content
        add_action("manage_{$this->postType}_posts_custom_column", [$this, 'renderColumn'], 10, 2);

        // Make columns sortable
        add_filter("manage_edit-{$this->postType}_sortable_columns", [$this, 'addSortableColumns']);

        return $this;
    }

    /**
     * Add columns to list table
     *
     * @param array<string, string> $columns Existing columns
     * @return array<string, string>
     */
    public function addColumns(array $columns): array
    {
        $newColumns = [];

        foreach ($this->columns as $columnId => $column) {
            $position = $column['position'] ?? 'after_title';
            $title = $column['title'] ?? ucfirst($columnId);

            // Insert column at specified position
            if ($position === 'before_title') {
                $newColumns[$columnId] = $title;
            } elseif ($position === 'after_title' && isset($columns['title'])) {
                // Add after title
                foreach ($columns as $key => $value) {
                    $newColumns[$key] = $value;
                    if ($key === 'title') {
                        $newColumns[$columnId] = $title;
                    }
                }
                continue;
            } elseif ($position === 'before_date' && isset($columns['date'])) {
                // Will be added before date below
            } else {
                $newColumns[$columnId] = $title;
            }
        }

        // Merge with existing columns
        foreach ($columns as $key => $value) {
            if (!isset($newColumns[$key])) {
                // Add custom columns before date
                if ($key === 'date') {
                    foreach ($this->columns as $columnId => $column) {
                        if (($column['position'] ?? '') === 'before_date') {
                            $newColumns[$columnId] = $column['title'] ?? ucfirst($columnId);
                        }
                    }
                }

                $newColumns[$key] = $value;
            }
        }

        return $newColumns;
    }

    /**
     * Render column content
     *
     * @param string $columnName Column name
     * @param int $postId Post ID
     * @return void
     */
    public function renderColumn(string $columnName, int $postId): void
    {
        if (!isset($this->columns[$columnName])) {
            return;
        }

        $column = $this->columns[$columnName];

        // Render using callback if provided
        if (isset($column['callback']) && is_callable($column['callback'])) {
            call_user_func($column['callback'], $postId, $columnName);
            return;
        }

        // Render meta value if meta_key provided
        if (isset($column['meta_key'])) {
            $metaKey = $column['meta_key'];
            $value = get_post_meta($postId, $metaKey, true);

            // Format value if formatter provided
            if (isset($column['formatter']) && is_callable($column['formatter'])) {
                $value = call_user_func($column['formatter'], $value, $postId);
            }

            echo wp_kses_post($value);
            return;
        }

        // Default: show column name
        echo esc_html__('N/A', 'adminforge');
    }

    /**
     * Add sortable columns
     *
     * @param array<string, string> $columns Existing sortable columns
     * @return array<string, string>
     */
    public function addSortableColumns(array $columns): array
    {
        return array_merge($columns, $this->sortableColumns);
    }

    /**
     * Add a custom column
     *
     * @param string $id Column ID
     * @param string $title Column title
     * @param array<string, mixed> $args Additional arguments
     * @return self
     */
    public function addColumn(string $id, string $title, array $args = []): self
    {
        $this->columns[$id] = array_merge([
            'title' => $title,
            'position' => 'before_date',
        ], $args);

        return $this;
    }

    /**
     * Remove a column
     *
     * @param string $id Column ID
     * @return self
     */
    public function removeColumn(string $id): self
    {
        unset($this->columns[$id]);
        return $this;
    }

    /**
     * Make column sortable
     *
     * @param string $columnId Column ID
     * @param string $orderby Orderby key (usually meta_key)
     * @return self
     */
    public function makeSortable(string $columnId, string $orderby = ''): self
    {
        if (empty($orderby)) {
            $orderby = $columnId;
        }

        // Validate and sanitize orderby key
        $orderby = sanitize_key($orderby);
        if (empty($orderby)) {
            return $this;
        }

        $this->sortableColumns[$columnId] = $orderby;

        // Add query modification for sorting
        add_action('pre_get_posts', function ($query) use ($columnId, $orderby) {
            if (!is_admin() || !$query->is_main_query()) {
                return;
            }

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $currentOrderby = $query->get('orderby');

            if ($currentOrderby === $orderby) {
                // Validate meta key exists in our allowed columns
                if (isset($this->columns[$columnId]['meta_key']) && $this->columns[$columnId]['meta_key'] === $orderby) {
                    $query->set('meta_key', $orderby);
                    $query->set('orderby', 'meta_value');
                }
            }
        });

        return $this;
    }

    /**
     * Get all columns
     *
     * @return array<string, array<string, mixed>>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Get post type
     *
     * @return string
     */
    public function getPostType(): string
    {
        return $this->postType;
    }
}
