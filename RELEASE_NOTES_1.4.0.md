# Laravel Themer v1.4.0

Laravel Themer v1.4.0 turns the package into a stronger production theme operations layer for Laravel, Livewire, modular apps, and multi-brand products.

## Highlights

- Added production diagnostics: `theme:doctor`, `theme:status`, `theme:debug`, `theme:graph`, and `theme:why`.
- Added `theme:refresh` with cache freshness metadata.
- Added preview URL generation with `theme:preview`.
- Added design-token support with `theme:tokens`, `theme_token()`, `theme_tokens()`, and `@themeTokens`.
- Added manifest validation for richer `theme.json` metadata.
- Added Laravel Boost guidelines and a `laravel-themer-development` skill.
- Added lifecycle events for caching, refresh, and preview workflows.
- Expanded CI support for Laravel 13 and Orchestra Testbench 11.

## Upgrade

```bash
composer update alizharb/laravel-themer
php artisan theme:refresh
php artisan theme:doctor
php artisan theme:check
```

## Why It Matters

This release makes Laravel Themer more than a theme path resolver. It adds the operational tooling teams need to safely inspect, preview, validate, cache, and automate themes in production.
