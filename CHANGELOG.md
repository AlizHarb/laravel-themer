# Changelog

All notable changes to `laravel-themer` will be documented in this file.

## v1.1.1 - 2026-01-26

### Added
- **Independent Vite Loader**: Introduced `vite.themer.js` for clean, standalone asset discovery in `themes/`.
- **New Install Command**: Added `themer:install` to automate Vite configuration with user consent.
- **Improved Installation**: `themer:install` now provides manual configuration instructions if automatic setup is declined.
- **Enhanced Livewire Redirection**: Fixed Livewire component location redirection to correctly target theme directories.

### Fixed
- **Missing Theme Handling**: Added friendly error handling in `make:livewire` when the specified theme is not found.

---

## v1.1.0 - 2026-01-25

### Added
- **Zero-IO Discovery Engine**: A new high-performance scanner that caches resource existence (Providers, Views, Lang, Livewire) to eliminate filesystem hits in production.
- **Multi-Level Inheritance**: Full support for deeply nested theme parents. Views, translations, and Livewire components now cascade through the entire inheritance chain.
- **Theme Versioning**: Support for the `version` field in `theme.json`, allowing themes to track their internal versions independently.
- **Artisan `theme:check`**: A new validation command to verify theme hierarchy integrity, detect circular dependencies, and identify missing parent themes.
- **Professional Stubs**: Enhanced `theme:make` to generate a standardized directory structure including `app/Livewire` and `lang`.
- **`SetTheme` Middleware**: A new route-level tool to enforce specific themes for specific routes or route groups.
- **`Themer::forTheme()`**: An ephemeral switcher for branded sessions like emails or PDF generation.
- **Auto-Blade Discovery**: Automatic registration and inheritance of standard Blade components in `resources/views/components`.
- **Modular Dependency Guard**: Updated `theme:check` to verify required `laravel-modular` modules defined in `theme.json`.

### Changed
- **Optimized Boot Phase**: Refactored `ThemeManager` to use cached discovery flags, delivering near-zero boot time for large theme libraries.
- **Refined `theme:make`**: Removed automatic layout generation to encourage the use of the more flexible `php artisan livewire:layout --theme=` command.
- **Production-Grade DX**: 100% PHPStan compatibility with strict literal typing and detailed PHPDoc array-shape hints.

### Fixed
- Fixed a bug where only the immediate parent's Livewire components were registered.
- Fixed inconsistent casing for view namespace resolution.

---

## v1.0.0 - 2026-01-10

- Initial release.
