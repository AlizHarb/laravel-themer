# Usage

## View Resolution

Laravel Themer simplifies view management by introducing a cascading resolution system.

### The `theme::` Namespace

Always refer to your views using the `theme::` namespace. This abstracts the actual file location and allows the Themer to resolve the most appropriate view based on the active theme.

```php
return view('theme::pages.home');
```

The resolver checks locations in this order:
1.  **Active Theme**: `themes/{active}/resources/views/pages/home.blade.php`
2.  **Parent Theme**: `themes/{parent}/resources/views/pages/home.blade.php`
3.  **Application**: `resources/views/pages/home.blade.php` (Fallback)

### Overriding Views

To override a view from a parent theme or the main application, simply create a file with the same path in your active theme.

**Example:**
To customize the `layouts.app` view:
1.  Create `themes/my-theme/resources/views/layouts/app.blade.php`.
2.  Themer will now serve this file instead of the default one.

## Livewire Integration

Themer provides first-class support for Livewire 4.

### Theme-Specific Components

Components created with `--theme` are automatically namespaced and registered.
- Class: `Theme\{ThemeName}\Livewire\ComponentName`
- Tag: `<livewire:theme-name::component-name />`

### Component Inheritance

You can extend Livewire components from a parent theme. If a component is missing in the child theme, Themer attempts to resolve it from the parent theme automatically.
