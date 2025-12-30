<?php
/**
 * Plugin Name: AdminForge - WordPress Admin Framework
 * Plugin URI: https://github.com/amirhossein103/adminforge
 * Description: WordPress-native admin framework library for building admin panels, settings pages, meta boxes, and custom fields. Designed for theme and plugin developers. Install via Composer: composer require amirhossein103/adminforge
 * Version: 1.1.0
 * Requires at least: 5.8
 * Requires PHP: 8.0
 * Author: Amirhossein
 * Author URI: https://github.com/amirhossein103
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: adminforge
 * Domain Path: /languages
 *
 * @package AdminForge
 */

declare(strict_types=1);

namespace AdminForge;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('ADMINFORGE_VERSION', '1.1.0');
define('ADMINFORGE_FILE', __FILE__);
define('ADMINFORGE_PATH', plugin_dir_path(__FILE__));
define('ADMINFORGE_URL', plugin_dir_url(__FILE__));
define('ADMINFORGE_BASENAME', plugin_basename(__FILE__));

// Composer autoloader
if (file_exists(ADMINFORGE_PATH . 'vendor/autoload.php')) {
    require_once ADMINFORGE_PATH . 'vendor/autoload.php';
}

// Manual autoloader fallback (if composer not used)
spl_autoload_register(function ($class) {
    $prefix = 'AdminForge\\';
    $base_dir = ADMINFORGE_PATH . 'src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Initialize AdminForge
 *
 * @return void
 */
function adminforge_init(): void
{
    if (class_exists('AdminForge\Core\AdminForge')) {
        Core\AdminForge::getInstance();
    }
}

// Initialize on plugins_loaded
add_action('plugins_loaded', __NAMESPACE__ . '\adminforge_init', 10);

/**
 * Activation hook
 *
 * @return void
 */
function adminforge_activate(): void
{
    // Set default options
    if (!get_option('adminforge_version')) {
        add_option('adminforge_version', ADMINFORGE_VERSION);
    }
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\adminforge_activate');

/**
 * Deactivation hook
 *
 * @return void
 */
function adminforge_deactivate(): void
{
    // Cleanup if needed
}
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\adminforge_deactivate');
