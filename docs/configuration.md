# Configuration

The configuration file is located at `config/themer.php` after running `php artisan themer:install`.

## Configuration Options

### Themes Path

```php
'themes_path' => base_path('themes'),
```

The root directory where themes are stored. Laravel Themer will scan this directory for `theme.json` files.

### Active Theme

```php
'active' => (string) env('THEME', 'default'),
```

The currently active theme. This is typically set via the `THEME` environment variable in `.env`.

**Example:**
```env
THEME=mytheme
```

### Asset Configuration

```php
'assets' => [
    'path' => 'themes',
    'publish_on_activate' => true,
    'symlink' => (bool) env('THEMER_SYMLINK', true),
],
```

#### `path`
The public directory suffix where theme assets will be published (e.g., `public/themes`).

#### `publish_on_activate`
Whether to automatically publish assets when a theme is activated. Set to `false` if you prefer manual asset publishing.

#### `symlink`
Whether to use symlinks instead of copying files. Symlinks are faster and save disk space, but require proper server permissions.

**Production Tip:** Set `THEMER_SYMLINK=false` in production if your deployment process doesn't support symlinks.

### Discovery Configuration

```php
'discovery' => [
    'filename' => 'theme.json',
    'scan_modules' => true,
],
```

#### `filename`
The metadata filename to look for when scanning for themes.

#### `scan_modules`
Whether to scan Laravel Modular modules for themes. Requires `alizharb/laravel-modular`.

### Auto-Namespaces

```php
'auto_namespaces' => [
    'layouts' => 'resources/views/layouts',
    'pages' => 'resources/views/livewire/pages',
],
```

Automatically register view and Livewire namespaces for common theme directories. These work alongside Livewire 4's native `component_namespaces` configuration.

**Usage:**
```blade
{{-- Resolves to active theme's layouts directory --}}
<x-layouts::app>
    {{-- Content --}}
</x-layouts::app>

{{-- Resolves to active theme's pages directory --}}
<livewire:pages::home />
```

**Customization:**
Add your own auto-namespaces:

```php
'auto_namespaces' => [
    'layouts' => 'resources/views/layouts',
    'pages' => 'resources/views/livewire/pages',
    'components' => 'resources/views/components',
    'partials' => 'resources/views/partials',
],
```

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `THEME` | `default` | Active theme slug |
| `THEMER_SYMLINK` | `true` | Use symlinks for assets |

## Advanced Configuration

### Custom Themes Path

If you want to store themes in a different location:

```php
'themes_path' => storage_path('themes'),
```

### Multiple Theme Paths

For scanning multiple directories (requires custom implementation):

```php
// In a service provider
$manager = app('themer');
$manager->scan(base_path('themes'));
$manager->scan(base_path('vendor-themes'));
```

### Disable Auto-Publishing

For manual control over asset publishing:

```php
'assets' => [
    'publish_on_activate' => false,
],
```

Then manually publish when needed:

```bash
php artisan theme:publish mytheme
```

## Next Steps

- [Theme Structure](structure.md)
- [Commands Reference](commands.md)
- [Asset Management](assets.md)
