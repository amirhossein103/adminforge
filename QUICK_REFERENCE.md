# AdminForge - Quick Reference Guide

Quick reference for common AdminForge tasks.

---

## Table of Contents

1. [Configuration](#configuration)
2. [Admin Pages](#admin-pages)
3. [Meta Boxes](#meta-boxes)
4. [Fields](#fields)
5. [Performance](#performance)
6. [Security](#security)
7. [Helpers](#helpers)

---

## Configuration

### Get Config Value
```php
use AdminForge\Core\Config;

$value = Config::get('menu.title', 'Default Title');
```

### Set Config Value
```php
Config::set('menu.title', 'My Admin');
Config::set('ui.primary_color', '#e74c3c');
```

### Check Config Exists
```php
if (Config::has('menu.icon')) {
    // Config exists
}
```

### Merge Config
```php
Config::merge([
    'menu' => ['title' => 'Custom Admin'],
    'ui' => ['primary_color' => '#3498db']
]);
```

---

## Admin Pages

### Simple Menu Page
```php
use AdminForge\Admin\MenuPage;

class MyPage extends MenuPage {
    protected string $pageTitle = 'My Page';
    protected string $menuTitle = 'My Page';
    protected string $slug = 'my-page';
    protected string $icon = 'dashicons-admin-settings';

    public function render(): void {
        echo '<h1>My Page Content</h1>';
    }
}

add_action('admin_menu', fn() => (new MyPage())->register());
```

### Page with Tabs
```php
class MyDashboard extends MenuPage {
    protected function init(): void {
        $this->addTabs([
            'general' => [
                'title' => 'General',
                'callback' => [$this, 'renderGeneral']
            ],
            'advanced' => [
                'title' => 'Advanced',
                'callback' => [$this, 'renderAdvanced']
            ]
        ]);
    }

    public function renderGeneral(): void {
        echo '<h2>General Settings</h2>';
    }

    public function renderAdvanced(): void {
        echo '<h2>Advanced Settings</h2>';
    }
}
```

### SubMenu Page
```php
use AdminForge\Admin\SubMenuPage;

class MySubPage extends SubMenuPage {
    public function __construct() {
        parent::__construct('options-general.php');
        $this->pageTitle = 'My Sub Page';
        $this->menuTitle = 'My Sub Page';
        $this->slug = 'my-sub-page';
    }
}
```

---

## Meta Boxes

### Basic Meta Box
```php
use AdminForge\MetaBox\MetaBox;

$box = new MetaBox('my_meta', 'My Fields', 'post');

$box->addField([
    'id' => 'my_field',
    'type' => 'text',
    'label' => 'My Field',
    'hint' => 'Enter value here'
]);

$box->register();
```

### Multiple Fields
```php
$box->addField([
    'id' => 'title',
    'type' => 'text',
    'label' => 'Title'
])
->addField([
    'id' => 'description',
    'type' => 'textarea',
    'label' => 'Description'
])
->addField([
    'id' => 'featured_image',
    'type' => 'media',
    'label' => 'Image'
])
->register();
```

### Custom Context & Priority
```php
$box->setContext('side')     // normal, side, advanced
    ->setPriority('high')    // high, default, low
    ->register();
```

---

## Fields

### Field Factory Usage
```php
use AdminForge\Fields\FieldFactory;

// Text field
$field = FieldFactory::create('text', 'username', [
    'label' => 'Username',
    'placeholder' => 'Enter username',
    'required' => true
]);
$field->render();

// Email field
$email = FieldFactory::create('email', 'email', [
    'label' => 'Email Address'
]);

// Number field
$number = FieldFactory::create('number', 'age', [
    'label' => 'Age',
    'min' => 0,
    'max' => 120
]);

// Textarea
$textarea = FieldFactory::create('textarea', 'bio', [
    'label' => 'Biography',
    'rows' => 5
]);

// Select dropdown
$select = FieldFactory::create('select', 'country', [
    'label' => 'Country',
    'options' => [
        'us' => 'United States',
        'uk' => 'United Kingdom',
        'ca' => 'Canada'
    ]
]);

// Multiple select
$multi = FieldFactory::create('select', 'skills', [
    'label' => 'Skills',
    'options' => ['php' => 'PHP', 'js' => 'JavaScript'],
    'multiple' => true
]);

// Checkbox
$checkbox = FieldFactory::create('checkbox', 'subscribe', [
    'label' => 'Subscribe to newsletter',
    'checkbox_label' => 'Yes, subscribe me'
]);

// Radio buttons
$radio = FieldFactory::create('radio', 'gender', [
    'label' => 'Gender',
    'options' => [
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other'
    ]
]);

// Color picker
$color = FieldFactory::create('color', 'brand_color', [
    'label' => 'Brand Color',
    'default' => '#3498db'
]);

// Media uploader
$media = FieldFactory::create('media', 'logo', [
    'label' => 'Logo',
    'button_text' => 'Upload Logo',
    'media_type' => 'image'
]);

// WordPress editor
$editor = FieldFactory::create('editor', 'content', [
    'label' => 'Content',
    'editor_height' => 300
]);
```

### Repeater Field
```php
use AdminForge\Fields\RepeaterField;

$repeater = new RepeaterField('team_members', [
    'label' => 'Team Members',
    'add_button_text' => 'Add Member',
    'max_rows' => 10,
    'sortable' => true,
    'fields' => [
        [
            'id' => 'name',
            'type' => 'text',
            'label' => 'Name'
        ],
        [
            'id' => 'role',
            'type' => 'text',
            'label' => 'Role'
        ],
        [
            'id' => 'photo',
            'type' => 'media',
            'label' => 'Photo'
        ]
    ]
]);

$repeater->render();
```

### Conditional Fields
```php
// Show field only when checkbox is checked
$enable = FieldFactory::create('checkbox', 'enable_feature', [
    'label' => 'Enable Feature'
]);

$advanced = FieldFactory::create('textarea', 'config', [
    'label' => 'Configuration',
    'condition' => [
        'field' => 'enable_feature',
        'value' => '1'
    ]
]);

$enable->render();
$advanced->render();
```

---

## Performance

### MetaHelper - O(1) Access
```php
use AdminForge\Helpers\MetaHelper;

// Load all meta at once (1 DB query)
MetaHelper::loadAll($post_id);

// Get values (O(1) from cache)
$price = MetaHelper::get($post_id, 'price', 0);
$sku = MetaHelper::get($post_id, 'sku', '');

// Get multiple values
$data = MetaHelper::getMultiple($post_id, [
    'price', 'sku', 'stock'
]);

// Preload for loop
$post_ids = [1, 2, 3, 4, 5];
MetaHelper::preload($post_ids);

foreach ($post_ids as $id) {
    $price = MetaHelper::get($id, 'price');
}
```

### DataHydrator - Frontend Injection
```php
use AdminForge\Helpers\DataHydrator;

// Register fields
DataHydrator::registerFields([
    'price', 'sku', 'stock'
]);

// Hydrate for current post
DataHydrator::hydrate();

// Get value
$price = DataHydrator::get(null, 'price');

// Inject to JavaScript
DataHydrator::injectToJS('myData');

// Preload for WP_Query
$query = new WP_Query(['post_type' => 'product']);
DataHydrator::preloadQuery($query);
```

---

## Security

### SecurityTrait Usage
```php
use AdminForge\Security\SecurityTrait;

class MyClass {
    use SecurityTrait;

    public function save($data) {
        // Verify nonce
        if (!$this->verifyNonce($_POST['nonce'], 'my_action')) {
            return false;
        }

        // Check capability
        if (!$this->userCan('manage_options')) {
            return false;
        }

        // Sanitize data
        $clean = $this->sanitizeArray($data);

        // Type-specific sanitization
        $email = $this->sanitizeByType($data['email'], 'email');
        $url = $this->sanitizeByType($data['url'], 'url');
        $int = $this->sanitizeByType($data['count'], 'int');

        // Save...
    }
}
```

### Flash Messages
```php
use AdminForge\Admin\FlashMessage;

// Success message
FlashMessage::success('Settings saved!');

// Error message
FlashMessage::error('Something went wrong');

// Warning message
FlashMessage::warning('Please review your settings');

// Info message
FlashMessage::info('New feature available');

// With redirect
FlashMessage::addAndRedirect(
    'Settings updated!',
    'success',
    admin_url('admin.php?page=my-settings')
);
```

---

## Helpers

### Helper Functions
```php
use AdminForge\Helpers\Helper;

// Get pages
$pages = Helper::getPages();

// Get menus
$menus = Helper::getMenus();

// Get categories
$categories = Helper::getCategories('category', false);

// Get post types
$postTypes = Helper::getPostTypes(true);

// Get sidebars
$sidebars = Helper::getSidebars();

// Get users
$users = Helper::getUsers();

// Get terms
$terms = Helper::getTerms('category');

// Sanitize array
$clean = Helper::sanitizeArray($_POST);

// Debug (only in WP_DEBUG mode)
Helper::debug($data, 'Debug Label');
```

### Settings Import/Export
```php
use AdminForge\Admin\SettingsTool;

$tool = new SettingsTool([
    'my_option_1',
    'my_option_2'
]);

// Export to JSON
$json = $tool->exportToJson();
file_put_contents('backup.json', $json);

// Import from JSON
$json = file_get_contents('backup.json');
$result = $tool->importFromJson($json, true);

// Export as download
$tool->exportToFile('settings-backup.json');

// Import from file upload
$result = $tool->importFromFile($_FILES['import_file'], true);

// Reset settings
$deleted = $tool->reset();
```

---

## Advanced Features

### Sidebar Registration
```php
use AdminForge\Admin\SidebarManager;

SidebarManager::register('my-sidebar', [
    'name' => 'My Sidebar',
    'description' => 'Custom sidebar area'
]);

// Display sidebar
SidebarManager::display('my-sidebar');
```

### Admin Branding
```php
use AdminForge\Admin\Branding;

$branding = new Branding();

$branding->setColors([
    'primary_color' => '#e74c3c',
    'secondary_color' => '#3498db'
])
->setLoginLogo('/path/to/logo.png')
->setFooterText('Powered by My Company')
->register();

// Hide WordPress logo
Branding::hideWordPressLogo();
```

### Editor Support
```php
use AdminForge\Admin\EditorSupport;

// Check if Gutenberg is active
if (EditorSupport::isGutenbergActive()) {
    // Do something
}

// Disable Gutenberg for post types
EditorSupport::disableGutenberg(['post', 'page']);

// Enable for specific types
EditorSupport::enableGutenberg(['product']);

// Disable completely
EditorSupport::disableGutenbergCompletely();

// Add block category
EditorSupport::addBlockCategory('my-blocks', 'My Blocks');

// Get current editor
$editor = EditorSupport::getCurrentEditor($post_id);
```

### Asset Management
```php
use AdminForge\Core\AssetManager;

// Register conditional script
AssetManager::registerScript(
    'my-script',
    'my-script.js',
    ['jquery'],
    ['post_type' => 'product'] // Load only on product pages
);

// Register conditional style
AssetManager::registerStyle(
    'my-style',
    'my-style.css',
    [],
    ['screen' => 'edit-product'] // Load only on specific screen
);

// Defer script
AssetManager::deferScript('my-script');

// Async script
AssetManager::asyncScript('my-script');

// Minify all assets
$stats = AssetManager::minifyAll();
```

---

## Common Patterns

### Admin Page + Meta Box
```php
// Admin page
class ProductSettings extends MenuPage {
    protected string $pageTitle = 'Product Settings';

    protected function init(): void {
        $this->registerMetaBox();
    }

    private function registerMetaBox(): void {
        $box = new MetaBox('product_meta', 'Details', 'product');
        $box->addField([
            'id' => 'price',
            'type' => 'number',
            'label' => 'Price'
        ])->register();
    }

    public function render(): void {
        // Render page
    }
}
```

### Form with Security
```php
class SecureForm {
    use SecurityTrait;

    public function process() {
        if (!$this->verifyNonce($_POST['nonce'], 'save')) {
            FlashMessage::error('Security check failed');
            return;
        }

        $data = $this->sanitizeArray($_POST);

        // Process data
        FlashMessage::success('Saved successfully!');
    }
}
```

---

For detailed examples, see [EXAMPLES.md](EXAMPLES.md)
