# Laravel Themer 🎨

<img src="art/banner.png" alt="Laravel Themer Banner" width="100%" height="300">

[![Latest Version on Packagist](https://img.shields.io/packagist/v/alizharb/laravel-themer.svg?style=flat-square)](https://packagist.org/packages/alizharb/laravel-themer)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/alizharb/laravel-themer/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/alizharb/laravel-themer/actions?query=workflow%3ATests+branch%3Amain)
[![GitHub PHPStan Action Status](https://img.shields.io/github/actions/workflow/status/alizharb/laravel-themer/tests.yml?branch=main&label=phpstan&style=flat-square)](https://github.com/alizharb/laravel-themer/actions?query=workflow%3ATests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/alizharb/laravel-themer.svg?style=flat-square)](https://packagist.org/packages/alizharb/laravel-themer)
[![Licence](https://img.shields.io/packagist/l/alizharb/laravel-themer.svg?style=flat-square)](https://packagist.org/packages/alizharb/laravel-themer)

**Laravel Themer** is a robust, enterprise-grade theme management package for Laravel applications. It provides a seamless way to manage themes, including asset publishing, view overrides, Livewire 4 integration, and modular support. Designed for modern TALL stack applications, it simplifies the creation of multi-themed applications without the complexity.

## ✨ Features

- 🏗️ **Independent Vite Builds**: Per-theme `package.json` and `vite.config.js` for total asset isolation.
- 📦 **NPM Workspaces**: Zero-config workspace integration for shared dependencies and optimized storage.
- ⚡ **Asset Shortcuts**: Launch dev servers or build theme assets directly via `theme:dev` and `theme:build`.
- 🛠️ **Theme Management**: Create, clone, safe-delete, and activate themes with consistent `--theme` support.
- 🚀 **Zero-IO Discovery**: High-performance architecture that eliminates filesystem scans in production via deep caching.
- 🌈 **Rich Metadata**: Support for `screenshots`, `tags`, and management flags (`removable`, `disableable`) in `theme.json`.
- ⚡ **Livewire 4 Integration**: First-class support for Livewire components and layouts with theme-aware resolution.
- 🎨 **View Overrides**: Intelligent view resolution cascading from Active Theme -> Parent Theme -> Application.
- 🚀 **Auto-Blade Discovery**: Automatic registration of theme-specific Blade components and inheritance.
- 🏷️ **Theme Versioning**: Support for version metadata in `theme.json` for easier dependency mapping.
- 🚦 **Ephemeral Switching**: Temporarily switch themes for specific tasks using `Themer::forTheme()`.
- 🛣️ **Route Middleware**: Enforce specific themes for routes or groups via `middleware('theme:name')`.
- 🧩 **Modular Support**: Native integration with `laravel-modular` including dependency verification in `theme:check`.
- 🛡️ **Hierarchy Guard**: Built-in validation to detect circular dependencies, missing parents, and asset health.
- 🔧 **Artisan Commands**: A comprehensive suite of commands (`make`, `npm`, `dev`, `build`, `clone`, `delete`, `list`, `check`, `cache`) to manage themes.

## 📚 Documentation


For full documentation, please visit [**alizharb.github.io/laravel-themer**](https://alizharb.github.io/laravel-themer) or browse the [docs/](docs/) directory.

---

## 📦 Installation

Install the package via Composer:

```bash
composer require alizharb/laravel-themer
```

### Quick Start (Recommended)

Run the installation command to automatically set up Laravel Themer:

```bash
php artisan themer:install
```

This interactive command will:

- ✅ Publish the configuration file (`config/themer.php`)
- ✅ Create the themes directory (default: `themes/`)
- ✅ Optionally configure `vite.config.js` with the `themerLoader` for automatic theme asset bundling

### Manual Configuration

If you prefer manual setup or need more control, see the [Installation Guide](docs/installation.md) for detailed instructions on:

- Publishing configuration files
- Setting up Vite integration
- Configuring theme paths and discovery rules

---

## 🔗 Related Packages

Laravel Themer is part of a comprehensive modular ecosystem for Laravel applications:

| Package                                                                              | Description                                                                                                                                       |
| ------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------- |
| [**laravel-modular**](https://github.com/AlizHarb/laravel-modular)                   | Framework-agnostic modular architecture with zero-config autoloading and 29+ Artisan command overrides. **Required** for modular theme discovery. |
| [**laravel-modular-livewire**](https://github.com/AlizHarb/laravel-modular-livewire) | Official Livewire 4 bridge providing automatic component discovery and registration within modules.                                               |
| [**laravel-modular-filament**](https://github.com/AlizHarb/laravel-modular-filament) | Official Filament v5 bridge enabling admin panel integration with automatic resource discovery in modules.                                        |
| [**laravel-hooks**](https://github.com/AlizHarb/laravel-hooks)                       | Universal extensibility and plugin system for Laravel 12+ applications with WordPress-style hooks and filters.                                    |
| [**filament-themer-luncher**](https://github.com/AlizHarb/filament-themer-luncher)   | A comprehensive Filament v5 interface for managing, switching, and backing up themes.                                                             |
| [**filament-modular-luncher**](https://github.com/AlizHarb/filament-modular-luncher) | A powerful Filament v5 manager for listing, toggling, and managing system modules.                                                                |

These packages work seamlessly together to provide a complete modular development experience.

---

## 📖 Usage

### Creating a Theme

Generate a new theme with a standard directory structure:

```bash
php artisan theme:make "Dark Theme"
```

### Managing Dependencies

Install packages into a theme workspace directly from the root:

```bash
php artisan theme:npm --theme=dark-theme install lodash
```

### Developing Assets

Launch a dedicated Vite dev server for your theme:

```bash
php artisan theme:dev --theme=dark-theme
```

### Cloning & Deletion

Safe and fast lifecycle management:

```bash
# Duplicate an existing theme
php artisan theme:clone "Summer Edition" --theme=dark-theme

# Safe removal (respects 'removable' flag)
php artisan theme:delete --theme=summer-edition
```

### Activating a Theme

Switch the active theme globally:

```bash
php artisan theme:activate dark-theme
```

### Livewire Integration

The package automatically registers Livewire components within your theme.

```bash
php artisan make:livewire Header --theme=dark-theme
```

---


## 🧪 Testing

We strictly enforce testing. Use the provided test suite to verify your themes:

```bash
vendor/bin/pest
```

---

## 💖 Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel Themer development. If you are interested in becoming a sponsor, please visit the [Laravel Themer GitHub Sponsors page](https://github.com/sponsors/alizharb).

---

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 🌟 Acknowledgments

- **Laravel**: For creating the most elegant PHP framework.
- **Spatie**: For setting the standard on Laravel package development.

---

## 🔒 Security

If you discover any security-related issues, please email **Ali Harb** at [harbzali@gmail.com](mailto:harbzali@gmail.com).

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---

<p align="center">
    Made with ❤️ by <strong>Ali Harb</strong>
</p>
