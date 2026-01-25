# Helpers

Laravel Themer provides a clean, strictly typed API for interacting with themes.

## `themer()`

The main entry point is the `themer()` helper, which returns the `ThemeManager` instance.

```php
// Get active theme name
$active = themer()->getActiveTheme()->name;

// Register a new theme manually
themer()->register($theme);
```

## `theme_asset()`

Generate a URL to a theme asset.

```blade
<link rel="stylesheet" href="{{ theme_asset('css/app.css') }}">
```

This automatically resolves to:
- `http://yoursite.com/themes/current-theme/css/app.css`

## `theme_path()`

Get the absolute path to a file within the active theme.

```php
$path = theme_path('resources/views/home.blade.php');
```
