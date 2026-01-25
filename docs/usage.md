# Usage

## View Resolution

Laravel Themer simplifies view management by introducing a cascading resolution system.

### The `theme::` Namespace

Always refer to your views using the `theme::` namespace. This abstracts the actual file location and allows the Themer to resolve the most appropriate view based on the active theme.

```php
return view('theme::pages.home');
```

The resolver checks locations in this order:
1.  **Active Theme**: `themes/{active}/resources/views/...`
2.  **Parent Theme(s)**: `themes/{parent}/resources/views/...` (Cascades through multiple parent levels)
3.  **Application**: `resources/views/...` (Fallback)

### Overriding Resources

To override a view or asset from any parent theme or the main application, simply create a file with the same path in your active theme. Themer handles multi-level cascading automatically.

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

## Route-based Switching (Middleware)

You can enforce a specific theme for routes or route groups using the `theme` middleware:

```php
Route::middleware('theme:dark-theme')->group(function () {
    Route::get('/dashboard', DashboardController::class);
});
```

This is useful for multi-tenant applications or sections requiring a specific visual style (e.g., admin panels).
