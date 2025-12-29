# AdminForge Settings System 2.0

**Complete centralized settings management for WordPress**

## 🎯 Overview

The new Settings system provides a powerful, developer-friendly API for managing WordPress options with:

- ✅ **Centralized Storage** - Single database option for all settings
- ✅ **Dot Notation Access** - Easy nested array navigation (`general.site_name`)
- ✅ **Type-Safe Methods** - `getInt()`, `getBool()`, `getString()`, `getArray()`
- ✅ **Validation & Sanitization** - Built-in validators and sanitizers
- ✅ **Caching Layer** - High-performance with WordPress Object Cache
- ✅ **Group-Based Organization** - Organize settings into logical groups
- ✅ **Import/Export** - Backup and restore functionality
- ✅ **Array Operations** - Perfect for checkboxes and multi-select fields

---

## 📦 Quick Start

```php
use AdminForge\Settings\Settings;

// Get setting
$siteName = Settings::get('general.site_name', 'Default Site');

// Set setting
Settings::set('general.site_name', 'My Awesome Site');

// Set with validation
Settings::set('admin_email', 'admin@example.com', [
    'type' => 'email',
    'validate' => 'email'
]);

// Array operations (for checkboxes)
Settings::pushToArray('features.enabled', 'dark_mode');
Settings::removeFromArray('features.enabled', 'old_feature');

// Check if value in array
if (Settings::inArray('features.enabled', 'dark_mode')) {
    // Dark mode is enabled
}
```

---

## 📖 Complete API Reference

### Basic Operations

#### Get Setting
```php
// Basic get
$value = Settings::get('key.path', $default);

// Type-safe getters
$count = Settings::getInt('items.count', 10);
$enabled = Settings::getBool('features.enabled', false);
$name = Settings::getString('general.name', '');
$tags = Settings::getArray('post.tags', []);
```

#### Set Setting
```php
// Simple set
Settings::set('key.path', $value);

// Set with options
Settings::set('email', 'test@example.com', [
    'type' => 'email',           // Validation type
    'validate' => 'email',       // Validator name
    'sanitize' => 'email'        // Sanitizer name
]);

// Set multiple
Settings::setMultiple([
    'general.site_name' => 'My Site',
    'general.tagline' => 'Just another site',
    'appearance.theme' => 'dark'
]);
```

#### Check & Remove
```php
// Check if exists
if (Settings::has('key.path')) {
    // Key exists
}

// Remove setting
Settings::remove('key.path');

// Get all settings
$all = Settings::all();
```

---

### Group-Based Access

Organize settings into logical groups:

```php
// Get group
$appearance = Settings::group('appearance');

// Group operations
$appearance->get('theme', 'light');
$appearance->set('theme', 'dark');
$appearance->setMultiple([
    'primary_color' => '#0073aa',
    'secondary_color' => '#00a0d2'
]);

// Get all settings in group
$allAppearance = $appearance->all();

// Check if key exists in group
if ($appearance->has('theme')) {
    // ...
}

// Remove from group
$appearance->remove('old_setting');

// Clear entire group
$appearance->clear();

// Merge with existing
$appearance->merge([
    'new_option' => 'value'
]);
```

---

### Array Operations

Perfect for checkboxes, multi-select, and feature flags:

```php
// Push value to array (add if not exists)
Settings::pushToArray('features.enabled', 'analytics');
Settings::pushToArray('features.enabled', 'notifications');

// Remove value from array
Settings::removeFromArray('features.enabled', 'old_feature');

// Toggle value in array
Settings::toggleInArray('features.enabled', 'dark_mode');

// Check if value exists in array
if (Settings::inArray('features.enabled', 'dark_mode')) {
    // Dark mode is enabled
}

// Get array
$enabledFeatures = Settings::getArray('features.enabled', []);
// Output: ['analytics', 'notifications', 'dark_mode']
```

---

### Nested Arrays

Store complex data structures:

```php
// Set nested array
Settings::set('appearance.colors', [
    'primary' => '#0073aa',
    'secondary' => '#00a0d2',
    'success' => '#46b450',
    'error' => '#dc3232'
]);

// Access nested values
$primary = Settings::get('appearance.colors.primary'); // '#0073aa'

// Update single nested value
Settings::set('appearance.colors.primary', '#ff0000');

// Get entire nested array
$colors = Settings::get('appearance.colors');
/*
[
    'primary' => '#ff0000',
    'secondary' => '#00a0d2',
    'success' => '#46b450',
    'error' => '#dc3232'
]
*/
```

---

### Validation

Built-in validators:

```php
// Email validation
Settings::set('contact_email', 'admin@example.com', [
    'type' => 'email'
]);

// URL validation
Settings::set('website', 'https://example.com', [
    'type' => 'url'
]);

// Integer validation
Settings::set('max_items', 50, [
    'type' => 'int'
]);

// Color validation
Settings::set('primary_color', '#0073aa', [
    'type' => 'hex_color'
]);

// Custom validator
Settings::registerValidator('my_validator', function($value) {
    return strlen($value) > 5;
});

Settings::set('password', 'secret123', [
    'validate' => 'my_validator'
]);
```

**Available validators:**
- `email`, `url`, `int`, `float`, `bool`, `string`, `array`
- `color`/`hex_color`, `ip`, `date`, `json`
- `alpha`, `alphanumeric`, `slug`
- `positive`, `negative`, `required`

---

### Sanitization

Automatic sanitization:

```php
// Email sanitization
Settings::set('email', 'ADMIN@EXAMPLE.COM', [
    'type' => 'email'
]);
// Stored as: 'admin@example.com'

// URL sanitization
Settings::set('link', '  https://example.com  ', [
    'type' => 'url'
]);
// Stored as: 'https://example.com'

// Text sanitization
Settings::set('name', '<script>alert("xss")</script>Name', [
    'type' => 'text'
]);
// Stored as: 'Name'

// Custom sanitizer
Settings::registerSanitizer('uppercase', function($value) {
    return strtoupper($value);
});

Settings::set('code', 'abcdef', [
    'sanitize' => 'uppercase'
]);
// Stored as: 'ABCDEF'
```

**Available sanitizers:**
- `email`, `url`, `int`, `float`, `bool`, `string`, `text`
- `textarea`, `html`, `array`
- `color`/`hex_color`, `slug`, `key`, `filename`
- `alphanumeric`, `json`

---

### Default Values

Set default values for groups:

```php
// Register defaults
Settings::setDefaults('general', [
    'site_name' => 'My Site',
    'tagline' => 'Just another WordPress site',
    'posts_per_page' => 10
]);

// Reset single setting to default
Settings::reset('general.site_name');

// Reset all settings
Settings::resetAll();
```

---

### Caching

High-performance caching:

```php
// Cache is automatic, but you can control it:

// Get cache stats
$stats = Settings::cache()->getStats();
/*
[
    'hits' => 150,
    'misses' => 25,
    'sets' => 30,
    'hit_rate' => '85.71%',
    'runtime_size' => 45,
    'memory_usage' => 12345
]
*/

// Clear cache
Settings::clearCache();

// Disable/enable cache
Settings::cache()->disable();
Settings::cache()->enable();

// Refresh cache
Settings::cache()->refresh();
```

---

### Import/Export

Backup and restore settings:

```php
// Export all settings
$json = Settings::export();

// Export specific group
$json = Settings::export('appearance');

// Import settings (merge with existing)
$result = Settings::import($json, $merge = true);

// Import (overwrite existing)
$result = Settings::import($json, $merge = false);

// Create backup
Settings::backup('my-backup-name');

// Restore from backup
Settings::restore('my-backup-name');

// List backups
$backups = Settings::listBackups();
/*
[
    'backup1' => [
        'name' => 'backup1',
        'created' => 1735728000,
        'created_date' => '2025-01-01 12:00:00',
        'size' => 45
    ]
]
*/
```

---

## 💡 Real-World Examples

### Example 1: User Settings Panel

```php
// Save user preferences
Settings::group('user_' . get_current_user_id())->setMultiple([
    'theme' => 'dark',
    'notifications' => true,
    'email_frequency' => 'daily'
]);

// Get user preference
$theme = Settings::group('user_' . get_current_user_id())->get('theme', 'light');
```

### Example 2: Feature Flags

```php
// Enable features
Settings::set('features.enabled', ['analytics', 'cache', 'cdn']);

// Check if feature is enabled
if (Settings::inArray('features.enabled', 'analytics')) {
    // Initialize analytics
}

// Toggle feature
Settings::toggleInArray('features.enabled', 'dark_mode');
```

### Example 3: Multi-Checkbox Form

```php
// In your form handler
$selected_features = $_POST['features']; // ['f1', 'f2', 'f3']
Settings::set('my_plugin.features', $selected_features);

// In your theme/plugin
$enabled = Settings::getArray('my_plugin.features', []);
foreach ($enabled as $feature) {
    // Initialize each feature
}
```

### Example 4: API Configuration

```php
// Store API credentials
Settings::group('api')->setMultiple([
    'key' => 'your-api-key',
    'secret' => 'your-secret',
    'endpoint' => 'https://api.example.com',
    'timeout' => 30,
    'enabled' => true
]);

// Use in your API class
class MyAPI {
    private $config;

    public function __construct() {
        $this->config = Settings::group('api')->all();
    }

    public function request() {
        if (!$this->config['enabled']) {
            return false;
        }

        // Make API request with config
    }
}
```

---

## 🎯 Usage Patterns

### Simple Settings Storage

```php
// Store individual settings
Settings::set('my_plugin.option_1', 'value1');
Settings::set('my_plugin.option_2', 'value2');

// Retrieve settings
$value1 = Settings::get('my_plugin.option_1');
$value2 = Settings::get('my_plugin.option_2');

// Use groups for better organization
$config = Settings::group('my_plugin');
$value1 = $config->get('option_1');
$config->set('option_1', 'new value');
```

---

## 📊 Performance Comparison

| Method | Database Queries | Memory Usage | Speed |
|--------|------------------|--------------|-------|
| Old `get_option()` (10 settings) | 10 queries | Low | Slow |
| Old with transient cache | 10 queries (first time) | Medium | Medium |
| **New Settings (no cache)** | **1 query** | **Medium** | **Fast** ⚡ |
| **New Settings (cached)** | **0 queries** | **High** | **Very Fast** ⚡⚡ |

---

## 🔒 Security

All settings operations include:
- ✅ Automatic sanitization based on type
- ✅ Validation before storage
- ✅ XSS prevention
- ✅ SQL injection prevention (prepared queries)
- ✅ CSRF protection for import/export

---

## 🐛 Debugging

Enable debug mode:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Settings will log errors to debug.log
Settings::set('invalid_email', 'not-an-email', ['type' => 'email']);
// Error logged: "AdminForge: Type validation failed for key: invalid_email"
```

---

## 📝 Best Practices

1. **Use Groups** - Organize related settings
   ```php
   Settings::group('my_plugin')->set('option', 'value');
   ```

2. **Always Set Defaults** - Prevent null/false returns
   ```php
   $value = Settings::get('key', 'safe-default');
   ```

3. **Use Type-Safe Methods** - Avoid type juggling bugs
   ```php
   $count = Settings::getInt('count', 0); // Always returns int
   ```

4. **Validate & Sanitize** - Never trust user input
   ```php
   Settings::set('email', $user_input, ['type' => 'email']);
   ```

5. **Use Arrays for Multi-Values** - Perfect for checkboxes
   ```php
   Settings::set('features', ['f1', 'f2']);
   Settings::inArray('features', 'f1'); // true
   ```

---

## 🚀 Advanced Usage

### Conditional Settings

```php
// Set different values based on environment
if (wp_get_environment_type() === 'production') {
    Settings::set('api.debug', false);
} else {
    Settings::set('api.debug', true);
}
```

### Cached Expensive Operations

```php
// Cache expensive computation result
$result = Settings::get('expensive.result');
if ($result === null) {
    $result = perform_expensive_operation();
    Settings::set('expensive.result', $result);
}
```

---

## 📞 Support

For issues or questions:
- GitHub: https://github.com/anthropics/adminforge
- Documentation: https://adminforge.dev/docs

---

**Built with ❤️ by AdminForge Team**
