# Installation

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or 12.0
- Composer

## Installation Steps

### 1. Install via Composer

```bash
composer require alizharb/laravel-themer
```

The package will automatically register its service provider via Laravel's package discovery.

### 2. Run the Install Command

```bash
php artisan themer:install
```

This command will:
- Publish the configuration file to `config/themer.php`
- Create the `themes/` directory in your project root
- Configure NPM Workspaces in your root `package.json` for optimized theme asset management
- Set up Vite integration for theme asset compilation

### 3. Configure Your Environment

Add the active theme to your `.env` file:

```env
THEME=default
```

### 4. Create Your First Theme

```bash
php artisan theme:make MyTheme --description="My awesome theme" --author="Your Name"
```

This generates a complete theme structure with:
- `theme.json` metadata file
- View directories (`resources/views`)
- Asset directories (`resources/assets`)
- Language files (`lang`)
- Livewire component directories (`app/Livewire`)
- `package.json` and `vite.config.js` for asset compilation

### 5. Activate Your Theme

```bash
php artisan theme:activate MyTheme
```

This will:
- Update your `.env` file with `THEME=mytheme`
- Publish theme assets to `public/themes/mytheme`
- Clear theme caches

## Verification

To verify your installation:

```bash
php artisan theme:list
```

You should see your newly created theme listed with its metadata.

## Optional: Generate Service Provider

If you need custom theme logic (bindings, events, middleware), generate a namespaced service provider:

```bash
php artisan theme:make MyTheme --provider
```

This creates `themes/mytheme/ThemeServiceProvider.php` with namespace `Theme\MyTheme\ThemeServiceProvider`.

## Next Steps

- [Configuration Options](configuration.md)
- [Theme Structure](structure.md)
- [Commands Reference](commands.md)
