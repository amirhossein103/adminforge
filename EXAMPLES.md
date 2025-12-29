# AdminForge - Code Examples

Comprehensive examples for using AdminForge framework.

## Table of Contents

1. [Basic Setup](#basic-setup)
2. [Creating Admin Pages](#creating-admin-pages)
3. [Working with Meta Boxes](#working-with-meta-boxes)
4. [Field Types](#field-types)
5. [Performance Optimization](#performance-optimization)
6. [Security](#security)

---

## Basic Setup

### Simple Configuration

```php
use AdminForge\Core\Config;

// Update config values
Config::set('menu.title', 'My Custom Admin');
Config::set('menu.icon', 'dashicons-admin-tools');
Config::set('ui.primary_color', '#e74c3c');
```

---

## Creating Admin Pages

### Example 1: Simple Menu Page

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
    $page = new MySettingsPage();
    $page->register();
});
```

### Example 2: Page with Tabs

```php
use AdminForge\Admin\MenuPage;

class MyDashboard extends MenuPage
{
    protected function init(): void
    {
        $this->addTabs([
            'general' => [
                'title' => 'General Settings',
                'icon' => 'dashicons-admin-generic',
                'callback' => [$this, 'renderGeneral'],
            ],
            'advanced' => [
                'title' => 'Advanced',
                'icon' => 'dashicons-admin-tools',
                'callback' => [$this, 'renderAdvanced'],
            ],
        ]);
    }

    public function render(): void
    {
        // Tabs will be rendered automatically
    }

    public function renderGeneral(): void
    {
        echo '<h3>General Settings</h3>';
    }

    public function renderAdvanced(): void
    {
        echo '<h3>Advanced Settings</h3>';
    }
}
```

### Example 3: SubMenu Page

```php
use AdminForge\Admin\SubMenuPage;

class MySubPage extends SubMenuPage
{
    public function __construct()
    {
        parent::__construct('options-general.php'); // Settings submenu
        $this->pageTitle = 'My Sub Page';
        $this->menuTitle = 'My Sub Page';
        $this->slug = 'my-sub-page';
    }

    public function render(): void
    {
        echo '<h2>SubMenu Page Content</h2>';
    }
}
```

---

## Working with Meta Boxes

### Example 1: Simple Meta Box

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

### Example 2: Advanced Meta Box with Multiple Fields

```php
$metaBox = new MetaBox('event_info', 'Event Information', ['event', 'post']);

$metaBox
    ->addField([
        'id' => 'event_date',
        'type' => 'text',
        'label' => 'Event Date',
        'placeholder' => 'YYYY-MM-DD',
    ])
    ->addField([
        'id' => 'event_location',
        'type' => 'text',
        'label' => 'Location',
    ])
    ->addField([
        'id' => 'event_type',
        'type' => 'select',
        'label' => 'Event Type',
        'options' => [
            'conference' => 'Conference',
            'workshop' => 'Workshop',
            'webinar' => 'Webinar',
        ],
    ])
    ->setContext('side')
    ->setPriority('high')
    ->register();
```

---

## Field Types

### Using Field Factory

```php
use AdminForge\Fields\FieldFactory;

// Text field
$field = FieldFactory::create('text', 'username', [
    'label' => 'Username',
    'placeholder' => 'Enter username',
    'required' => true,
]);
$field->render();

// Email field
$email = FieldFactory::create('email', 'user_email', [
    'label' => 'Email Address',
    'value' => 'user@example.com',
]);
$email->render();

// Select field
$select = FieldFactory::create('select', 'country', [
    'label' => 'Country',
    'options' => [
        'us' => 'United States',
        'uk' => 'United Kingdom',
        'ca' => 'Canada',
    ],
]);
$select->render();

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
    'media_type' => 'image',
]);
$media->render();
```

### Repeater Field Example

```php
use AdminForge\Fields\RepeaterField;

$repeater = new RepeaterField('team_members', [
    'label' => 'Team Members',
    'add_button_text' => 'Add Member',
    'max_rows' => 10,
    'min_rows' => 1,
    'sortable' => true,
    'fields' => [
        [
            'id' => 'name',
            'type' => 'text',
            'label' => 'Name',
        ],
        [
            'id' => 'role',
            'type' => 'text',
            'label' => 'Role',
        ],
        [
            'id' => 'email',
            'type' => 'email',
            'label' => 'Email',
        ],
        [
            'id' => 'photo',
            'type' => 'media',
            'label' => 'Photo',
        ],
    ],
]);

$repeater->render();
```

### Conditional Fields

```php
// Show field only when checkbox is checked
$checkbox = FieldFactory::create('checkbox', 'enable_feature', [
    'label' => 'Enable Advanced Feature',
    'checkbox_label' => 'Yes, enable it',
]);

$advanced = FieldFactory::create('textarea', 'advanced_config', [
    'label' => 'Advanced Configuration',
    'condition' => [
        'field' => 'enable_feature',
        'value' => '1',
    ],
]);

$checkbox->render();
$advanced->render();
```

---

## Performance Optimization

### Using MetaHelper

```php
use AdminForge\Helpers\MetaHelper;

// Load all meta at once (single DB query)
MetaHelper::loadAll($post_id);

// Get values (O(1) access from cache)
$price = MetaHelper::get($post_id, 'price', 0);
$sku = MetaHelper::get($post_id, 'sku', '');

// Get multiple values
$data = MetaHelper::getMultiple($post_id, ['price', 'sku', 'stock']);

// Preload for loop
$post_ids = [1, 2, 3, 4, 5];
MetaHelper::preload($post_ids);

foreach ($post_ids as $id) {
    $price = MetaHelper::get($id, 'price'); // No DB query!
}
```

### Using DataHydrator

```php
use AdminForge\Helpers\DataHydrator;

// Register fields to hydrate
DataHydrator::registerFields(['price', 'sku', 'stock', 'description']);

// Hydrate for current post
DataHydrator::hydrate();

// Get hydrated data
$price = DataHydrator::get(null, 'price');

// Auto-hydrate in loop
DataHydrator::autoHydrateLoop();

// Preload for WP_Query
$query = new WP_Query(['post_type' => 'product']);
DataHydrator::preloadQuery($query);
```

---

## Security

### Using SecurityTrait

```php
use AdminForge\Security\SecurityTrait;

class MySecureClass
{
    use SecurityTrait;

    public function saveData($data)
    {
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

        // Or sanitize by type
        $email = $this->sanitizeByType($data['email'], 'email');
        $url = $this->sanitizeByType($data['url'], 'url');
        $number = $this->sanitizeByType($data['count'], 'int');

        // Save...
    }
}
```

### Flash Messages

```php
use AdminForge\Admin\FlashMessage;

// Add success message
FlashMessage::success('Settings saved successfully!');

// Add error message
FlashMessage::error('Something went wrong');

// Add with redirect
FlashMessage::addAndRedirect(
    'Settings updated!',
    'success',
    admin_url('admin.php?page=my-settings')
);
```

### Import/Export Settings

```php
use AdminForge\Admin\SettingsTool;

$tool = new SettingsTool(['my_option_1', 'my_option_2']);

// Export
$json = $tool->exportToJson();
file_put_contents('backup.json', $json);

// Import
$json = file_get_contents('backup.json');
$result = $tool->importFromJson($json, true);

if ($result['success']) {
    echo "Imported {$result['imported']} settings";
}
```

---

## Helper Functions

```php
use AdminForge\Helpers\Helper;

// Get all pages
$pages = Helper::getPages();

// Get all menus
$menus = Helper::getMenus();

// Get categories
$categories = Helper::getCategories();

// Get post types
$postTypes = Helper::getPostTypes();

// Sanitize array
$clean = Helper::sanitizeArray($_POST);

// Debug (only in WP_DEBUG mode)
Helper::debug($data, 'My Debug Label');
```

---

## Complete Example: Product Settings Page

```php
use AdminForge\Admin\MenuPage;
use AdminForge\MetaBox\MetaBox;
use AdminForge\Fields\FieldFactory;
use AdminForge\Helpers\MetaHelper;

class ProductSettings extends MenuPage
{
    protected string $pageTitle = 'Product Settings';
    protected string $slug = 'product-settings';

    protected function init(): void
    {
        // Add tabs
        $this->addTabs([
            'general' => [
                'title' => 'General',
                'callback' => [$this, 'renderGeneral'],
            ],
            'pricing' => [
                'title' => 'Pricing',
                'callback' => [$this, 'renderPricing'],
            ],
        ]);

        // Add meta box
        $this->registerMetaBox();
    }

    private function registerMetaBox(): void
    {
        $metaBox = new MetaBox('product_meta', 'Product Details', 'product');

        $metaBox
            ->addField([
                'id' => 'price',
                'type' => 'number',
                'label' => 'Price',
            ])
            ->addField([
                'id' => 'sale_price',
                'type' => 'number',
                'label' => 'Sale Price',
            ])
            ->register();
    }

    public function renderGeneral(): void
    {
        // Render form
    }

    public function renderPricing(): void
    {
        // Render pricing settings
    }
}

// Register
add_action('admin_menu', function() {
    (new ProductSettings())->register();
});
```
