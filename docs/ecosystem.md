# Ecosystem

Laravel Themer is part of a powerful modular ecosystem for Laravel applications. These official packages work seamlessly together to provide enterprise-grade architecture.

## Official Packages

| Package | Description |
| :--- | :--- |
| **[laravel-modular](https://github.com/alizharb/laravel-modular)** | **Core Modular Architecture**<br>The foundation package providing DDD-based module structure, automatic discovery, and zero-config integration. |
| **[laravel-themer](https://github.com/alizharb/laravel-themer)** | **Theme System**<br>Advanced theme management with deep inheritance, Livewire 4 support, and per-theme asset compilation. |
| **[laravel-modular-livewire](https://github.com/alizharb/laravel-modular-livewire)** | **Livewire Integration**<br>Automatic component discovery and registration within modules. No manual `Livewire::component()` calls needed. |
| **[laravel-modular-filament](https://github.com/alizharb/laravel-modular-filament)** | **Filament Admin Support**<br>Filament v5 admin panel integration with automatic resource, page, and widget discovery inside modules. |
| **[laravel-hooks](https://github.com/alizharb/laravel-hooks)** | **Plugin System**<br>Universal extensibility and hook system (actions/filters) for your Laravel application. |

## Filament Tools

| Package | Description |
| :--- | :--- |
| **[filament-themer-luncher](https://github.com/alizharb/filament-themer-luncher)** | **Theme Manager GUI**<br>Comprehensive Filament v5 interface for previewing, managing, and switching active themes. |
| **[filament-modular-luncher](https://github.com/alizharb/filament-modular-luncher)** | **Module Manager GUI**<br>Powerful Filament v5 manager for listing, toggling, enabling/disabling, and backing up system modules. |

## How They Work Together

### Modular + Themer

```php
// Module-specific themes
themes/
├── blog-theme/          # Theme for Blog module
│   └── module: "Blog"
└── shop-theme/          # Theme for Shop module
    └── module: "Shop"
```

Laravel Themer can automatically discover and activate themes associated with specific modules.

### Themer + Livewire

```bash
# Create Livewire component in active theme
php artisan make:livewire pages.home --class --theme=mytheme
```

Automatic component discovery and namespace registration for theme-specific Livewire components.

### Complete Stack

```bash
# Install the complete ecosystem
composer require alizharb/laravel-modular
composer require alizharb/laravel-themer
composer require alizharb/laravel-modular-livewire
composer require alizharb/laravel-modular-filament
composer require alizharb/laravel-hooks
```

## Installation

Install any package via Composer:

```bash
composer require alizharb/laravel-themer
```

## Community

- **GitHub**: [github.com/alizharb](https://github.com/alizharb)
- **Sponsor**: [github.com/sponsors/alizharb](https://github.com/sponsors/alizharb)
