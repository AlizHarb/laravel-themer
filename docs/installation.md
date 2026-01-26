# Installation

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or higher

## Composer

Install the package via Composer:

```bash
composer require alizharb/laravel-themer
```

## Quick Start

The easiest way to install and configure Laravel Themer is using the `themer:install` command:

```bash
php artisan themer:install
```

This command will:

- Publish the configuration file.
- Create the themes directory.
- Optionally configure `vite.config.js` with the `themerLoader`.

## Manual Configuration

If you prefer to configure Laravel Themer manually or need more control over the setup process:

### 1. Publish Configuration

Publish the configuration file to customize the package behavior:

```bash
php artisan vendor:publish --tag="themer-config"
```

This will create a `config/themer.php` file where you can configure:

- **themes_path**: Directory where themes are stored (default: `base_path('themes')`)
- **active**: Default active theme name
- **assets**: Asset publishing and symlinking settings
- **discovery**: Discovery rules for themes and automatic registration

### 2. Create Themes Directory

Create the themes directory manually:

```bash
mkdir -p themes
```

### 3. Configure Vite (Optional but Recommended)

For automatic theme asset bundling, add the `themerLoader` to your `vite.config.js`:

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { themerLoader } from './vite.themer.js';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        themerLoader(), // Add this line
    ],
});
```

The `vite.themer.js` file is automatically created when you run `themer:install`, or you can create it manually by copying from the package's `resources/stubs/vite.themer.js.stub` file.

### 4. Verify Installation

Check that everything is configured correctly:

```bash
php artisan theme:list
```

This command should run without errors and show an empty list (or any existing themes).
