# Laravel Themer

**Professional theme management for Laravel 11/12 applications with first-class Livewire 4 support.**

Laravel Themer is a production-ready package that brings powerful, flexible theming capabilities to your Laravel applications. Built with strict typing, zero-config modular integration, and performance optimization at its core.

## Why Laravel Themer?

- **Zero-Config Discovery**: Automatic theme scanning and registration
- **Deep Inheritance**: Unlimited theme parent chains with loop protection
- **Livewire 4 Native**: First-class support for view-based and class-based components
- **Vite Integration**: Per-theme asset compilation with hot module replacement
- **Production Optimized**: Smart asset publishing, request-level caching, and minimal boot overhead
- **Strict Typing**: 100% PHPStan Level 5 compliance
- **15+ Artisan Commands**: Complete CLI tooling for theme management

## Quick Start

```bash
composer require alizharb/laravel-themer
php artisan themer:install
php artisan theme:make MyTheme
```

## Features at a Glance

### Theme Structure
- Modular directory structure with views, assets, translations, and Livewire components
- Automatic resource discovery and registration
- Theme metadata via `theme.json` with schema validation

### Asset Management
- Per-theme Vite configuration with NPM workspaces
- Automatic asset publishing with symlink support
- Theme-aware `@vite` directive

### View System
- Blade view inheritance with automatic fallback
- Custom directives: `@theme_include`
- Configurable auto-namespaces for layouts and pages

### Livewire Integration
- Automatic component discovery and registration
- Theme-specific namespaces
- Deep inheritance support for components

### Developer Experience
- Comprehensive command suite
- Theme cloning and scaffolding
- Built-in validation and health checks
- Professional error messages

## Next Steps

- [Installation Guide](installation.md)
- [Configuration](configuration.md)
- [Creating Your First Theme](quickstart.md)
- [Commands Reference](commands.md)
