<?php
/**
 * Settings Group Handler
 *
 * Provides group-based access to settings
 *
 * @package AdminForge
 * @since 2.0.0
 */

declare(strict_types=1);

namespace AdminForge\Settings;

// Security: Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * SettingsGroup class
 */
final class SettingsGroup
{
    /**
     * Group name
     *
     * @var string
     */
    private string $name;

    /**
     * Settings manager
     *
     * @var SettingsManager
     */
    private SettingsManager $manager;

    /**
     * Constructor
     *
     * @param string $name Group name
     * @param SettingsManager $manager Manager instance
     */
    public function __construct(string $name, SettingsManager $manager)
    {
        $this->name = $name;
        $this->manager = $manager;
    }

    /**
     * Get value from group
     *
     * @param string $key Key within group
     * @param mixed $default Default value
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->manager->get($this->name . '.' . $key, $default);
    }

    /**
     * Set value in group
     *
     * @param string $key Key within group
     * @param mixed $value Value to set
     * @param array<string, mixed> $options Options
     * @return bool Success status
     */
    public function set(string $key, $value, array $options = []): bool
    {
        return $this->manager->set($this->name . '.' . $key, $value, $options);
    }

    /**
     * Set multiple values in group
     *
     * @param array<string, mixed> $settings Key-value pairs
     * @param array<string, mixed> $options Options
     * @return bool Success status
     */
    public function setMultiple(array $settings, array $options = []): bool
    {
        $prefixedSettings = [];
        foreach ($settings as $key => $value) {
            $prefixedSettings[$this->name . '.' . $key] = $value;
        }

        return $this->manager->setMultiple($prefixedSettings, $options);
    }

    /**
     * Check if key exists in group
     *
     * @param string $key Key within group
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->manager->has($this->name . '.' . $key);
    }

    /**
     * Remove key from group
     *
     * @param string $key Key within group
     * @return bool Success status
     */
    public function remove(string $key): bool
    {
        return $this->manager->remove($this->name . '.' . $key);
    }

    /**
     * Get all settings in group
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->manager->get($this->name, []);
    }

    /**
     * Clear entire group
     *
     * @return bool Success status
     */
    public function clear(): bool
    {
        return $this->manager->remove($this->name);
    }

    /**
     * Merge with existing group data
     *
     * @param array<string, mixed> $values Values to merge
     * @return bool Success status
     */
    public function merge(array $values): bool
    {
        $current = $this->all();
        $merged = array_merge($current, $values);
        return $this->manager->set($this->name, $merged);
    }

    /**
     * Get group name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
