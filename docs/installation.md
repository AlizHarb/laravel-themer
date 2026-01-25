# Installation

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or higher

## Composer

Install the package via Composer:

```bash
composer require alizharb/laravel-themer
```

## Configuration

Publish the configuration file to customize the package behavior:

```bash
php artisan vendor:publish --tag="themer-config"
```

This will create a `config/themer.php` file where you can configure:

- **themes_path**: Directory where themes are stored.
- **active**: Default active theme.
- **assets**: Asset publishing and symlinking settings.
- **discovery**: Discovery rules for themes.
