# Changelog

All notable changes to `laravel-themer` will be documented in this file.

## v1.2.0 - 2026-02-01

### Power User Features & DX

- **Theme Loop Guard**: Safety mechanism to prevent infinite recursion in theme parent resolution.
- **is_theme_active() Helper**: Global helper and `ThemeManager::isActive()` method for easy theme state checks.
- **@theme_include Directive**: New Blade directive for theme-aware view inclusions.
- **Theme NPM Command**: Added `theme:npm` to manage theme-specific dependencies via NPM Workspaces.
- **Theme Asset Commands**: Added `theme:dev` and `theme:build` shortcuts for per-theme Vite development and building.
- **Theme Management Commands**: Added `theme:clone` and `theme:delete` for easier theme lifecycle management.
- **Metadata Flags**: Added `removable` and `disableable` flags to `theme.json` to control theme management actions.
- **Rich Metadata**: Added support for `screenshots` and `tags` in `theme.json` for enhanced UI integration.
- **Standardized Selection**: Integrated `HasThemeOption` trait across all theme-related commands for consistent `--theme` selection.
- **Enhanced Validation**: Improved `theme:check` with asset health and missing screenshot detection.
- **Reliable Discovery**: Added `ThemeManager::find()` for robust theme lookup by name, slug, or directory.
- **Theme Upgrade Command**: Added `theme:upgrade` to automatically migrate existing themes to the new asset structure.
- **Per-Theme Assets**: `theme:make` now generates a `package.json` and `vite.config.js` for each theme, enabling independent asset management.
- **Zero-Config Workspaces**: `themer:install` now automatically configures NPM Workspaces in the root `package.json` for optimized storage and dependency sharing.
- **Vite Version Sync**: Theme `package.json` stubs now automatically synchronize their Vite version with the root project.

### Audit Enhancements & Scaffolding

- **Simple Visual Placeholders**: `theme:make` now automatically includes clean Light and Dark mode screenshot placeholders in `resources/assets/screenshots/`.
- **Advanced Metadata Support**: Added `--description`, `--author`, and `--tags` options to `theme:make` for professional theme initialization.
- **Namespaced Service Providers**: `theme:make --provider` now generates a namespaced `ThemeServiceProvider` (e.g., `Theme\{Slug}\ThemeServiceProvider`), resolving class collision issues in multi-theme environments.
- **Dynamic Provider Resolution**: Updated `ThemeManager` to robustly load namespaced providers while maintaining backward compatibility for global `ThemeServiceProvider` classes.
- **Deep Theme Inheritance**: Upgraded Livewire component resolution to support infinite inheritance depth with recursive parent lookup and loop protection.
- **Smart Asset Publishing**: Optimized boot performance by skipping redundant symlink/copy operations if assets are already synchronized.
- **Safer Directory Caching**: Replaced risky production optimizations with robust request-level caching for theme resource existence check.
- **Configurable Auto-Namespaces**: Moved `layouts::` and `pages::` auto-discovery defaults to the `themer.php` configuration file for maximum flexibility.
- **Improved Force Deletion**: `theme:delete` now bypasses confirmation when the `--force` option is used, enabling better automation.

---

## v1.1.2 - 2026-01-31

### Fixed Issues

- **Blade Component Discovery**: Fixed a bug where `layouts::` and `pages::` view namespaces were not registered as Blade component prefixes, causing `<x-layouts::>` and `<x-pages::>` tags to fail.
- **Theme Discovery**: Fixed a bug where themes registered by multiple keys (name, slug, path) were causing duplicate results in `ThemeManager::all()`.
- **Command Registration**: Enabled theme commands (like `theme:cache`) to be available in both web and console environments, fixing failures when calling them via `Artisan::call()` in web controllers.

---

## v1.1.1 - 2026-01-26

### New Features

- **Independent Vite Loader**: Introduced `vite.themer.js` for clean, standalone asset discovery in `themes/`.
- **New Install Command**: Added `themer:install` to automate Vite configuration with user consent.
- **Improved Installation**: `themer:install` now provides manual configuration instructions if automatic setup is declined.
- **Enhanced Livewire Redirection**: Fixed Livewire component location redirection to correctly target theme directories.

### Bug Fixes

- **Missing Theme Handling**: Added friendly error handling in `make:livewire` when the specified theme is not found.

---

## v1.1.0 - 2026-01-25

### Key Features

- **Zero-IO Discovery Engine**: A new high-performance scanner that caches resource existence (Providers, Views, Lang, Livewire) to eliminate filesystem hits in production.
- **Multi-Level Inheritance**: Full support for deeply nested theme parents. Views, translations, and Livewire components now cascade through the entire inheritance chain.
- **Theme Versioning**: Support for the `version` field in `theme.json`, allowing themes to track their internal versions independently.
- **Artisan `theme:check`**: A new validation command to verify theme hierarchy integrity, detect circular dependencies, and identify missing parent themes.
- **Professional Stubs**: Enhanced `theme:make` to generate a standardized directory structure including `app/Livewire` and `lang`.
- **`SetTheme` Middleware**: A new route-level tool to enforce specific themes for specific routes or route groups.
- **`Themer::forTheme()`**: An ephemeral switcher for branded sessions like emails or PDF generation.
- **Auto-Blade Discovery**: Automatic registration and inheritance of standard Blade components in `resources/views/components`.
- **Modular Dependency Guard**: Updated `theme:check` to verify required `laravel-modular` modules defined in `theme.json`.

### Behavioral Changes

- **Optimized Boot Phase**: Refactored `ThemeManager` to use cached discovery flags, delivering near-zero boot time for large theme libraries.
- **Refined `theme:make`**: Removed automatic layout generation to encourage the use of the more flexible `php artisan livewire:layout --theme=` command.
- **Production-Grade DX**: 100% PHPStan compatibility with strict literal typing and detailed PHPDoc array-shape hints.

### Other Fixes

- Fixed a bug where only the immediate parent's Livewire components were registered.
- Fixed inconsistent casing for view namespace resolution.

---

## v1.0.0 - 2026-01-10

- Initial release.
