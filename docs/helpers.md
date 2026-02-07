# Helper Functions

Laravel Themer provides convenient helper functions for common theme operations.

## Available Helpers

### `get_active_theme()`

Get the currently active theme instance.

```php
$theme = get_active_theme();

if ($theme) {
    echo $theme->name;      // "mytheme"
    echo $theme->version;   // "1.0.0"
    echo $theme->parent;    // "basetheme" or null
}
```

**Returns:** `Theme|null`

**Example Usage:**

```php
// Check if theme is active before using
if ($theme = get_active_theme()) {
    $logo = theme_asset('images/logo.png');
} else {
    $logo = asset('images/logo.png');
}
```

---

### `theme_asset()`

Generate a URL for a theme asset.

```php
<img src="{{ theme_asset('images/logo.png') }}" alt="Logo">

<link rel="stylesheet" href="{{ theme_asset('css/custom.css') }}">
```

**Parameters:**
- `string $path` - Relative path to asset within theme

**Returns:** `string` - Public URL to the asset

**Example:**

```php
// Active theme asset
theme_asset('images/logo.png')
// → /themes/mytheme/images/logo.png

// Falls back to regular asset() if no theme active
theme_asset('images/logo.png')
// → /images/logo.png (if no active theme)
```

---

### `is_theme_active()`

Check if a specific theme is currently active.

```php
if (is_theme_active('mytheme')) {
    // Theme-specific logic
}
```

**Parameters:**
- `string $themeName` - Theme name, slug, or directory name

**Returns:** `bool`

**Example:**

```php
if (is_theme_active('dark-theme')) {
    $logoPath = theme_asset('images/logo-light.png');
} else {
    $logoPath = theme_asset('images/logo-dark.png');
}
```

---

## Blade Directives

### `@vite`

Theme-aware Vite directive (automatically overridden by Laravel Themer).

```blade
@vite(['resources/assets/css/app.css', 'resources/assets/js/app.js'])
```

The package automatically detects if assets exist in the active theme and loads them accordingly.

---

### `@theme_include`

Include a view from the active theme.

```blade
@theme_include('partials.header')
```

**Equivalent to:**

```blade
@include('theme::partials.header')
```

---

### `@theme_asset`

Generate a public URL for a theme asset.

```blade
<link rel="stylesheet" href="@theme_asset('css/app.css')">
```

### `@theme_vite`

Load theme-specific assets using Vite with proper hot-reload support.

```blade
@theme_vite('resources/assets/js/app.js')
```

---

## ThemeManager Methods

Access the ThemeManager via the service container:

```php
$manager = app('themer');
// or
$manager = app(\AlizHarb\Themer\ThemeManager::class);
```

### `all()`

Get all discovered themes.

```php
$themes = app('themer')->all();

foreach ($themes as $theme) {
    echo $theme->name;
}
```

**Returns:** `Collection<string, Theme>`

---

### `find()`

Find a theme by name, slug, or directory name.

```php
$theme = app('themer')->find('mytheme');

if ($theme) {
    echo $theme->version;
}
```

**Parameters:**
- `string $themeName` - Theme identifier

**Returns:** `Theme|null`

---

### `getActiveTheme()`

Get the active theme instance.

```php
$theme = app('themer')->getActiveTheme();
```

**Returns:** `Theme|null`

---

### `set()`

Set the active theme programmatically.

```php
app('themer')->set('mytheme');
```

**Parameters:**
- `string $themeName` - Theme name or slug

**Throws:** `ThemeNotFoundException` if theme doesn't exist

---

### `isActive()`

Check if a specific theme is active.

```php
$isActive = app('themer')->isActive('mytheme');
```

**Parameters:**
- `string $themeName` - Theme identifier

**Returns:** `bool`

---

### `getInheritanceChain()`

Get the parent chain of a theme.

```php
$theme = app('themer')->find('mytheme');
$parents = app('themer')->getInheritanceChain($theme);

foreach ($parents as $parent) {
    echo $parent->name;
}
```

**Parameters:**
- `Theme|string $theme` - Theme instance or slug

**Returns:** `Collection<int, Theme>`

---

### `publishAssets()`

Publish or symlink theme assets to public directory.

```php
$theme = app('themer')->find('mytheme');
app('themer')->publishAssets($theme);
```

**Parameters:**
- `Theme $theme` - Theme instance

---

## Theme Object Properties

The `Theme` class is a readonly data object with public properties:

```php
$theme = get_active_theme();

// Identifiers
$theme->name;           // string: Display name
$theme->slug;           // string: URL-safe identifier
$theme->path;           // string: Absolute filesystem path

// Metadata
$theme->version;        // string: Semantic version
$theme->author;         // string|null: Primary author
$theme->authors;        // array: Detailed author info
$theme->tags;           // array: Searchable tags
$theme->screenshots;    // array: Preview image paths

// Hierarchy
$theme->parent;         // string|null: Parent theme slug

// Configuration
$theme->config;         // array: Full theme.json config
$theme->assetPath;      // string: Public asset path

// Capabilities
$theme->hasViews;       // bool: Has views directory
$theme->hasTranslations; // bool: Has translations
$theme->hasProvider;    // bool: Has service provider
$theme->hasLivewire;    // bool: Has Livewire components

// Permissions
$theme->removable;      // bool: Can be deleted
$theme->disableable;    // bool: Can be deactivated
```

## Usage Examples

### Conditional Theme Logic

```php
if (is_theme_active('dark-theme')) {
    $logoPath = theme_asset('images/logo-light.png');
} else {
    $logoPath = theme_asset('images/logo-dark.png');
}
```

### Dynamic Asset Loading

```blade
@if(get_active_theme()?->parent === 'corporate')
    <link rel="stylesheet" href="{{ theme_asset('css/corporate-overrides.css') }}">
@endif
```

### Theme Information Display

```blade
@if($theme = get_active_theme())
    <footer>
        <p>Theme: {{ $theme->name }} v{{ $theme->version }}</p>
        @if($theme->author)
            <p>By {{ $theme->author }}</p>
        @endif
    </footer>
@endif
```

### Programmatic Theme Switching

```php
// In a controller
public function switchTheme(Request $request, string $themeName)
{
    $manager = app('themer');
    
    if (!$manager->find($themeName)) {
        abort(404, 'Theme not found');
    }
    
    try {
        $manager->set($themeName);
        session(['theme' => $themeName]);
        
        return redirect()->back()->with('success', 'Theme changed!');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', $e->getMessage());
    }
}
```

## Best Practices

### 1. Always Check for Null

```php
✅ if ($theme = get_active_theme()) { }
❌ $theme = get_active_theme(); $theme->name; // May be null
```

### 2. Use Helpers for Portability

```php
✅ theme_asset('logo.png')
❌ asset('themes/mytheme/logo.png')
```

### 3. Leverage Theme Properties

```php
// Instead of hardcoding
if (config('app.theme') === 'dark') { }

// Use theme properties
if (get_active_theme()?->slug === 'dark') { }
```

### 4. Cache Theme Checks in Service Providers

```php
// In a service provider
public function boot()
{
    $this->app->singleton('theme.is_dark', function () {
        return is_theme_active('dark-theme');
    });
}

// In views/controllers
if (app('theme.is_dark')) { }
```

## Next Steps

- [Advanced Features](advanced.md)
- [Production Deployment](deployment.md)
