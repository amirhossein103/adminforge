# AdminForge - WordPress Admin Framework

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0+-blue.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

A lightweight, object-oriented, PSR-4 compliant WordPress admin framework optimized for developer experience and frontend performance.

## 🚀 Features

- ✅ **PSR-4 Autoloading** - Modern PHP standards with strict types
- ✅ **Configuration System** - Dot notation config access (Config::get)
- ✅ **Admin Pages** - MenuPage and SubMenuPage with tabs support
- ✅ **Meta Box System** - Easy custom fields with auto-nonce security
- ✅ **Field Engine** - 12+ field types including Repeater
- ✅ **Performance Optimized** - O(1) meta access with static caching
- ✅ **Security First** - Built-in nonce verification and 20+ sanitization methods
- ✅ **Conditional Logic** - Show/hide fields based on conditions
- ✅ **Asset Management** - Auto-minification and conditional loading
- ✅ **Import/Export** - Settings backup and restore functionality
- ✅ **Admin Branding** - Customize colors, logos, and footer text
- ✅ **Editor Support** - Gutenberg and Classic Editor utilities

## 📦 Installation

### As a Plugin

1. Download or clone this repository
2. Place in `wp-content/plugins/adminforge`
3. Run `composer install` (optional, for dev tools)
4. Activate through WordPress admin

### As a Framework (Composer)

```bash
composer require amirhossein103/adminforge
```

Then in your theme's `functions.php`:

```php
require_once get_template_directory() . '/vendor/autoload.php';
```

## 🎯 Quick Start

### Creating an Admin Page

```php
use AdminForge\Admin\MenuPage;

class MySettingsPage extends MenuPage
{
    protected string $pageTitle = 'My Settings';
    protected string $menuTitle = 'My Settings';
    protected string $slug = 'my-settings';
    protected string $icon = 'dashicons-admin-settings';

    public function render(): void
    {
        echo '<h2>Welcome to My Settings Page</h2>';
    }
}

// Register the page
add_action('admin_menu', function() {
    (new MySettingsPage())->register();
});
```

### Creating a Meta Box

```php
use AdminForge\MetaBox\MetaBox;

$metaBox = new MetaBox('product_details', 'Product Details', 'product');

$metaBox->addField([
    'id' => 'price',
    'type' => 'number',
    'label' => 'Price',
    'hint' => 'Enter product price',
]);

$metaBox->addField([
    'id' => 'sku',
    'type' => 'text',
    'label' => 'SKU',
]);

$metaBox->register();
```

### Using Fields

```php
use AdminForge\Fields\FieldFactory;

// Text field
$field = FieldFactory::create('text', 'username', [
    'label' => 'Username',
    'placeholder' => 'Enter username',
    'required' => true,
]);
$field->render();

// Color picker
$color = FieldFactory::create('color', 'brand_color', [
    'label' => 'Brand Color',
    'default' => '#3498db',
]);
$color->render();

// Media field
$media = FieldFactory::create('media', 'featured_image', [
    'label' => 'Featured Image',
    'button_text' => 'Select Image',
]);
$media->render();
```

### Performance Optimization

```php
use AdminForge\Helpers\MetaHelper;

// Load all meta at once (single DB query)
MetaHelper::loadAll($post_id);

// Get values (O(1) access from cache)
$price = MetaHelper::get($post_id, 'price', 0);
$sku = MetaHelper::get($post_id, 'sku', '');

// Preload for loop
$post_ids = [1, 2, 3, 4, 5];
MetaHelper::preload($post_ids);

foreach ($post_ids as $id) {
    $price = MetaHelper::get($id, 'price'); // No DB query!
}
```

## 🏗️ Project Structure

```
adminforge/
├── src/
│   ├── Core/           # Core classes (Cache, ErrorHandler, Constants)
│   ├── Admin/          # Admin pages (MenuPage, SettingsPage, SubMenuPage)
│   ├── MetaBox/        # Meta box system
│   ├── Fields/         # Field types (12+ fields)
│   ├── Settings/       # Settings API (centralized storage)
│   ├── Helpers/        # Helper utilities
│   └── Security/       # Security traits
├── assets/
│   ├── css/            # Stylesheets
│   └── js/             # JavaScript
└── config/             # Configuration files
```

## 📖 Documentation

### Available Field Types

AdminForge includes 12+ built-in field types:

- **Text Fields**: text, email, url, number, tel, password
- **Textarea**: Multi-line text input
- **Select**: Single or multiple select dropdown
- **Checkbox**: Boolean checkbox field
- **Radio**: Radio button group
- **Color**: WordPress color picker
- **Media**: WordPress media uploader
- **WPEditor**: WordPress visual editor (TinyMCE)
- **Repeater**: Complex repeater with drag-drop

See [EXAMPLES.md](EXAMPLES.md) for comprehensive code examples.

### Development Progress

- ✅ Phase 1: Core Architecture (100%)
- ✅ Phase 2: Backend Systems (100%)
- ✅ Phase 3: Field Engine (100%)
- ✅ Phase 4: Performance Layer (100%)
- ✅ Phase 5: Security & Polish (100%)
- ✅ Extra Features (100%)
- ✅ Documentation & Assets (100%)

**Project Status**: Production Ready v1.0.0

## 🤝 Contributing

Contributions are welcome! Please read our [Contributing Guidelines](CONTRIBUTING.md) before submitting a Pull Request.

### Quick Contribution Steps

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Follow our coding standards (PSR-4, PSR-12, strict types)
4. Write tests for your changes
5. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
6. Push to the branch (`git push origin feature/AmazingFeature`)
7. Open a Pull Request

See [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines.

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Author

**Amirhossein**
- GitHub: [@amirhossein103](https://github.com/amirhossein103)

## 🙏 Acknowledgments

- WordPress Core Team
- PSR Standards
- Open Source Community

---

Made with ❤️ for WordPress developers
