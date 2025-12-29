# AdminForge

**WordPress-native admin framework for theme and plugin developers.**

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php&logoColor=white)](https://php.net)
[![WordPress](https://img.shields.io/badge/WordPress-5.8+-21759B?logo=wordpress&logoColor=white)](https://wordpress.org)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![Packagist](https://img.shields.io/packagist/v/amirhossein103/adminforge)](https://packagist.org/packages/amirhossein103/adminforge)

AdminForge is a modern, PSR-4 compliant library for building WordPress admin interfaces. It provides an intuitive API for creating settings pages, meta boxes, and custom fields—all designed exclusively for WordPress environments.

---

## Why AdminForge?

- **WordPress-First Design:** Built on WordPress APIs, not abstracted away from them
- **Modern PHP:** PHP 8.0+, strict types, union types, named parameters
- **Zero Configuration:** Sensible defaults, works out of the box
- **Type-Safe:** Full IDE autocomplete support with comprehensive type hints
- **Performance-Optimized:** O(1) meta access, two-tier caching, conditional asset loading
- **Security-Conscious:** Auto-nonce verification, comprehensive sanitization, capability checks
- **Extensible:** Designed with clear extension points for custom implementations

---

## Requirements

- **PHP:** 8.0 or higher
- **WordPress:** 5.8 or higher

---

## Installation

### Via Composer (Recommended)

```bash
composer require amirhossein103/adminforge
```

### In Your WordPress Plugin

```php
<?php
/**
 * Plugin Name: My Awesome Plugin
 */

// Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Start using AdminForge
use AdminForge\Admin\MenuPage;
use AdminForge\MetaBox\MetaBox;
use AdminForge\Settings\Settings;
```

### In Your WordPress Theme

```php
<?php
// functions.php

// Composer autoloader
require_once get_template_directory() . '/vendor/autoload.php';

// Start using AdminForge
```

---

## Quick Start Examples

### 1. Create a Settings Page

```php
use AdminForge\Admin\SettingsPage;

add_action('admin_menu', function() {
    $page = new SettingsPage('My Plugin Settings', 'my_plugin', [
        'slug' => 'my-plugin-settings',
        'icon' => 'dashicons-admin-settings',
        'position' => 30
    ]);

    // Add fields
    $page->addField('text', 'api_key', 'API Key', [
        'hint' => 'Enter your API key',
        'required' => true
    ]);

    $page->addField('checkbox', 'enable_cache', 'Enable Caching', [
        'hint' => 'Improve performance with caching'
    ]);

    $page->addField('select', 'theme_color', 'Theme Color', [
        'options' => [
            'blue' => 'Blue',
            'red' => 'Red',
            'green' => 'Green'
        ]
    ]);

    $page->register();
});

// Retrieve settings anywhere
$api_key = Settings::get('my_plugin.api_key', '');
$cache_enabled = Settings::getBool('my_plugin.enable_cache', false);
```

### 2. Create a Meta Box

```php
use AdminForge\MetaBox\MetaBox;

add_action('add_meta_boxes', function() {
    $metaBox = new MetaBox('product_info', 'Product Information', ['product', 'post']);

    $metaBox->addField([
        'id' => 'price',
        'type' => 'number',
        'label' => 'Price',
        'hint' => 'Enter product price in USD'
    ]);

    $metaBox->addField([
        'id' => 'sku',
        'type' => 'text',
        'label' => 'SKU',
        'placeholder' => 'PROD-12345'
    ]);

    $metaBox->addField([
        'id' => 'in_stock',
        'type' => 'checkbox',
        'label' => 'In Stock'
    ]);

    $metaBox->register();
});

// Retrieve meta values (performance-optimized)
use AdminForge\Helpers\MetaHelper;

// Option 1: Individual access
$price = get_post_meta($post_id, 'price', true);

// Option 2: Batch loading (single DB query)
MetaHelper::loadAll($post_id);
$price = MetaHelper::get($post_id, 'price', 0);
$sku = MetaHelper::get($post_id, 'sku', '');
$in_stock = MetaHelper::get($post_id, 'in_stock', false);
```

### 3. Create a Custom Admin Page

```php
use AdminForge\Admin\MenuPage;

class MyCustomPage extends MenuPage
{
    protected string $pageTitle = 'My Dashboard';
    protected string $menuTitle = 'Dashboard';
    protected string $slug = 'my-dashboard';
    protected string $icon = 'dashicons-dashboard';

    public function render(): void
    {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html($this->pageTitle); ?></h1>
            <p>Welcome to your custom dashboard!</p>

            <?php
            // Use WordPress functions naturally
            $user = wp_get_current_user();
            echo '<p>Hello, ' . esc_html($user->display_name) . '</p>';
            ?>
        </div>
        <?php
    }
}

// Register the page
add_action('admin_menu', function() {
    (new MyCustomPage())->register();
});
```

### 4. Create Custom Fields

```php
use AdminForge\Fields\FieldFactory;

// Text field
$field = FieldFactory::create('text', 'username', [
    'label' => 'Username',
    'placeholder' => 'Enter username',
    'required' => true
]);
echo $field->render();

// Color picker
$color = FieldFactory::create('color', 'brand_color', [
    'label' => 'Brand Color',
    'default' => '#3498db'
]);
echo $color->render();

// Media uploader
$media = FieldFactory::create('media', 'featured_image', [
    'label' => 'Featured Image',
    'button_text' => 'Select Image'
]);
echo $media->render();

// Repeater field (complex repeating sections)
$repeater = FieldFactory::create('repeater', 'team_members', [
    'label' => 'Team Members',
    'fields' => [
        ['id' => 'name', 'type' => 'text', 'label' => 'Name'],
        ['id' => 'role', 'type' => 'text', 'label' => 'Role'],
        ['id' => 'bio', 'type' => 'textarea', 'label' => 'Bio']
    ]
]);
echo $repeater->render();
```

---

## Available Field Types

AdminForge includes 12+ built-in field types:

| Field Type | Description | Usage |
|------------|-------------|-------|
| `text` | Single-line text input | Names, titles, URLs |
| `email` | Email input with validation | Email addresses |
| `url` | URL input with validation | Website links |
| `number` | Numeric input | Prices, quantities |
| `tel` | Telephone input | Phone numbers |
| `password` | Password input (masked) | Credentials |
| `textarea` | Multi-line text | Descriptions, notes |
| `select` | Dropdown selection | Categories, options |
| `checkbox` | Boolean checkbox | Enable/disable features |
| `radio` | Radio button group | Exclusive choices |
| `color` | WordPress color picker | Theme colors |
| `media` | WordPress media uploader | Images, files |
| `editor` | WordPress visual editor (TinyMCE) | Rich text content |
| `repeater` | Repeating field groups | Lists, galleries |

---

## Settings API

AdminForge provides a powerful settings management system with dot notation support:

```php
use AdminForge\Settings\Settings;

// Simple get/set
Settings::set('my_plugin.api_key', 'abc123');
$key = Settings::get('my_plugin.api_key', '');

// Nested arrays
Settings::set('appearance.colors', [
    'primary' => '#0073aa',
    'secondary' => '#00a0d2'
]);
$primary = Settings::get('appearance.colors.primary'); // '#0073aa'

// Type-safe getters
$port = Settings::getInt('server.port', 8080);
$enabled = Settings::getBool('features.cache', false);
$name = Settings::getString('site.name', 'My Site');
$options = Settings::getArray('menu.items', []);

// Array operations (useful for checkboxes)
Settings::pushToArray('features.enabled', 'dark_mode');
Settings::removeFromArray('features.enabled', 'old_feature');
Settings::toggleInArray('features.beta', 'feature_x');

// Check if setting exists
if (Settings::has('my_plugin.configured')) {
    // ...
}

// Group-based access
$appearance = Settings::group('appearance');
$appearance->set('theme', 'dark');
$theme = $appearance->get('theme');
$all = $appearance->all();

// Import/Export
$json = Settings::export('my_plugin'); // Export specific group
Settings::import($json, $merge = true); // Import and merge

// Backups
Settings::backup('pre-update-backup');
Settings::restore('pre-update-backup');
$backups = Settings::listBackups();
```

---

## Performance Optimization

### MetaHelper: O(1) Meta Access

```php
use AdminForge\Helpers\MetaHelper;

// Without MetaHelper: N database queries
foreach ($posts as $post) {
    $price = get_post_meta($post->ID, 'price', true);     // Query 1
    $sku = get_post_meta($post->ID, 'sku', true);         // Query 2
    $stock = get_post_meta($post->ID, 'in_stock', true);  // Query 3
}

// With MetaHelper: 1 database query total
$post_ids = wp_list_pluck($posts, 'ID');
MetaHelper::preload($post_ids); // Single query loads all meta

foreach ($posts as $post) {
    $price = MetaHelper::get($post->ID, 'price', 0);      // O(1) array access
    $sku = MetaHelper::get($post->ID, 'sku', '');         // O(1) array access
    $stock = MetaHelper::get($post->ID, 'in_stock', false); // O(1) array access
}
```

### Two-Tier Caching

AdminForge uses a two-tier caching strategy:

1. **Runtime cache:** In-memory cache for the current request (fastest)
2. **WordPress Object Cache:** Persistent cache across requests (Redis, Memcached compatible)

```php
use AdminForge\Core\Cache;

// Cached for 1 hour
$data = Cache::remember('expensive_operation', 3600, function() {
    return expensive_database_query();
});

// Clear specific cache
Cache::forget('expensive_operation');

// Clear all caches
Cache::flush();
```

---

## Security Best Practices

AdminForge follows WordPress security standards:

### Automatic Nonce Verification

```php
// Meta boxes automatically include nonces
$metaBox = new MetaBox('product_info', 'Product Information', 'product');
$metaBox->register(); // Nonces handled automatically

// Settings pages automatically verify nonces
$page = new SettingsPage('Settings', 'my_plugin');
// Form submissions automatically verified
```

### Comprehensive Sanitization

```php
use AdminForge\Security\SecurityTrait;

class MyPlugin
{
    use SecurityTrait;

    public function saveData($input)
    {
        $sanitized = [
            'email' => $this->sanitizeEmail($input['email']),
            'url' => $this->sanitizeUrl($input['url']),
            'text' => $this->sanitizeText($input['text']),
            'html' => $this->sanitizeHtml($input['content']),
            'int' => $this->sanitizeInt($input['count']),
        ];

        // Or use type-based sanitization
        $value = $this->sanitizeByType($input['field'], 'email');

        return $sanitized;
    }
}
```

### Capability Checks

```php
// Built into admin pages
class MyPage extends MenuPage
{
    protected string $capability = 'manage_options'; // Requires admin

    public function render(): void
    {
        // Only accessible to users with 'manage_options' capability
    }
}

// Manual capability checks
use AdminForge\Security\SecurityTrait;

if ($this->userCan('edit_posts')) {
    // User can edit posts
}
```

---

## Extending AdminForge

### Custom Field Types

```php
use AdminForge\Fields\BaseField;
use AdminForge\Fields\FieldFactory;

class SliderField extends BaseField
{
    public function render(): string
    {
        $value = $this->getValue() ?? $this->config['default'] ?? 50;
        $min = $this->config['min'] ?? 0;
        $max = $this->config['max'] ?? 100;

        return sprintf(
            '<input type="range" id="%s" name="%s" min="%d" max="%d" value="%d" />',
            esc_attr($this->id),
            esc_attr($this->id),
            $min,
            $max,
            $value
        );
    }
}

// Register custom field type
FieldFactory::register('slider', SliderField::class);

// Use it
$field = FieldFactory::create('slider', 'volume', [
    'label' => 'Volume',
    'min' => 0,
    'max' => 100,
    'default' => 50
]);
```

### Custom Page Types

```php
use AdminForge\Admin\MenuPage;

class AnalyticsDashboard extends MenuPage
{
    protected string $pageTitle = 'Analytics';
    protected string $menuTitle = 'Analytics';
    protected string $slug = 'my-analytics';
    protected string $capability = 'view_analytics'; // Custom capability

    protected function init(): void
    {
        // Called during construction
        // Add custom initialization logic
    }

    public function enqueueAssets(string $hook): void
    {
        // Only load on this specific page
        if ($hook !== $this->getHookSuffix()) {
            return;
        }

        wp_enqueue_script('chart-js', '//cdn.example.com/chart.js');
        wp_enqueue_style('my-analytics', plugin_dir_url(__FILE__) . 'analytics.css');
    }

    public function render(): void
    {
        // Use WordPress data naturally
        $stats = new WP_Query(['post_type' => 'product', 'posts_per_page' => -1]);

        ?>
        <div class="wrap">
            <h1>Analytics Dashboard</h1>
            <p>Total Products: <?php echo $stats->found_posts; ?></p>

            <div id="chart-container"></div>
        </div>
        <?php
    }
}
```

---

## WordPress Integration

AdminForge embraces WordPress conventions:

### Hooks and Filters

```php
// Register pages on admin_menu
add_action('admin_menu', function() {
    (new MySettingsPage())->register();
});

// Register meta boxes on add_meta_boxes
add_action('add_meta_boxes', function() {
    (new MyMetaBox())->register();
});

// Access WordPress globals naturally
global $wpdb;
$results = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type = 'product'");
```

### Translations

```php
// All strings are translatable
__('Settings saved successfully', 'my-plugin');
esc_html__('Welcome to AdminForge', 'my-plugin');
```

### WordPress APIs

```php
// Use WordPress functions naturally
$user = wp_get_current_user();
$post = get_post($post_id);
$meta = get_post_meta($post_id, 'key', true);

// WordPress hooks work as expected
do_action('my_plugin_before_save', $data);
$value = apply_filters('my_plugin_value', $value);
```

---

## Library vs. Plugin Mode

AdminForge can be used as a library in your plugin/theme OR as a standalone plugin.

### As a Library (Recommended)

```bash
composer require amirhossein103/adminforge
```

```php
// Your plugin/theme code
use AdminForge\Admin\MenuPage;
use AdminForge\Settings\Settings;

// Use only the components you need
// No unwanted admin menus or automatic initialization
```

### As a Standalone Plugin

1. Download/clone the repository
2. Place in `wp-content/plugins/adminforge`
3. Activate through WordPress admin

**Note:** When activated as a plugin, AdminForge creates a demo admin page. You can disable this by not initializing the core singleton:

```php
// Remove the default init hook
remove_action('plugins_loaded', 'AdminForge\adminforge_init');
```

---

## API Reference

### Core Classes

- **`AdminForge\Settings\Settings`** - Settings management with dot notation
- **`AdminForge\Fields\FieldFactory`** - Create field instances
- **`AdminForge\Helpers\MetaHelper`** - Performance-optimized meta access
- **`AdminForge\Core\Cache`** - Two-tier caching system
- **`AdminForge\Core\Config`** - Configuration management

### Page Classes

- **`AdminForge\Admin\MenuPage`** - Top-level admin menu pages
- **`AdminForge\Admin\SubMenuPage`** - Submenu pages
- **`AdminForge\Admin\SettingsPage`** - Settings pages with auto-forms
- **`AdminForge\Admin\BasePage`** - Abstract base for custom pages

### Meta Boxes

- **`AdminForge\MetaBox\MetaBox`** - Custom meta boxes with auto-nonce

### Fields

- **`AdminForge\Fields\FieldInterface`** - Field contract
- **`AdminForge\Fields\BaseField`** - Abstract base field class
- **`AdminForge\Fields\TextField`** - Text input fields
- **`AdminForge\Fields\SelectField`** - Select dropdowns
- **`AdminForge\Fields\CheckboxField`** - Checkboxes
- **`AdminForge\Fields\MediaField`** - WordPress media uploader
- **`AdminForge\Fields\RepeaterField`** - Repeating field groups
- *And more...*

### Utilities

- **`AdminForge\Helpers\Helper`** - General utility functions
- **`AdminForge\Helpers\DataHydrator`** - Data extraction and filtering
- **`AdminForge\Security\SecurityTrait`** - Security helpers (trait)

---

## Contributing

Contributions are welcome! This is a WordPress-native library, so contributions should:

- Follow WordPress coding standards
- Use WordPress APIs (not reinvent them)
- Include PHPDoc blocks
- Maintain backward compatibility in minor versions
- Add tests for new features

See [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines.

---

## Changelog

### 1.0.0 (2025-12-29)

**Initial stable release for Packagist**

- ✅ PSR-4 autoloading with PHP 8.0+ strict types
- ✅ Settings API with dot notation support
- ✅ 12+ field types including Repeater
- ✅ Meta box system with auto-nonce security
- ✅ Performance-optimized MetaHelper (O(1) access)
- ✅ Two-tier caching system
- ✅ Comprehensive security sanitization
- ✅ Admin page system with tabs support
- ✅ Full WordPress integration
- ✅ Production-ready and stable

---

## License

MIT License - see [LICENSE](LICENSE) for details.

---

## Credits

**Author:** Amirhossein ([@amirhossein103](https://github.com/amirhossein103))

**Special Thanks:**
- WordPress Core Team for the excellent APIs
- The PHP community for modern standards (PSR-4, PSR-12)

---

## Support

- **Issues:** [GitHub Issues](https://github.com/amirhossein103/adminforge/issues)
- **Documentation:** [GitHub Wiki](https://github.com/amirhossein103/adminforge#readme)
- **Source:** [GitHub Repository](https://github.com/amirhossein103/adminforge)

---

Made with ❤️ for WordPress developers
