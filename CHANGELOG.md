# Changelog

All notable changes to `laravel-themer` will be documented in this file.

## v1.4.0 - 2026-07-08

### Production Theme Operations

- **Diagnostics Commands**: Added `theme:doctor`, `theme:status`, `theme:debug`, `theme:graph`, and `theme:why` for production visibility and CI automation.
- **Cache Freshness**: Added cache metadata with manifest hashes and `theme:refresh` for safe rebuilds.
- **Preview URLs**: Added `theme:preview` for inactive theme QA, including signed preview URL support.
- **Lifecycle Events**: Added events for caching, refresh, and preview workflows.

### Design System Support

- **Design Tokens**: Added `tokens` support in `theme.json`.
- **Token Helpers**: Added `theme_token()`, `theme_tokens()`, `@themeTokens`, and `theme:tokens`.

### Manifest & Compatibility

- **Manifest Validation**: Added validation for richer `theme.json` metadata including `requires`, `conflicts`, `provides`, and `tokens`.
- **Laravel Boost**: Added package guidelines and a `laravel-themer-development` skill.
- **Laravel 13 CI**: Expanded the GitHub Actions matrix for Laravel 13 and Orchestra Testbench 11.

---

## v1.3.0 - 2026-03-01

### New Features & Power Tools

- **Native Vite Integration**: Refactored `ThemeManager` to natively hook into Laravel 11/12/13's `Vite::useBuildDirectory` and `Vite::useHotFile()`. The legacy symlink/copy workaround for `theme:publish` is no longer the primary Vite pipeline. Themes can simply use standard `@vite` tags.
- **PreviewTheme Middleware**: Added a dedicated middleware that allows administrators or internal tools to securely preview inactive themes using Signed URLs or the `?preview_theme=slug` query parameter.
- **System Event Hooks**: Added a `hooks` array property to the `theme.json` configuration. Themes can now execute native Artisan or Shell commands on specific lifecycle events (e.g., `'after_activate' => ['php artisan db:seed --class=EcommerceSeeder']`).
- **Safe Mode Fallback**: Improved `ThemeManager::set()` and `ThemeActivating` dispatch to natively catch fatal theme boot exceptions. If a theme's `ThemeServiceProvider` crashes the application, Safe Mode intercepts the error, logs it, and silently fails-over to the default configuration theme, drastically improving production reliability.
- **Zero-IO Production Cache**: Supercharged `theme:cache`. It now fully analyzes and serializes the deep inheritance chain of every theme into `bootstrap/cache/themes.php`. The `ThemeManager` boots instantly from this array, completely bypassing filesystem I/O in production.
- **Laravel 13 Support**: Formalized compatibility constraints for Laravel 13 within the package's dependencies (`illuminate/support`, `illuminate/contracts`, `illuminate/view`).

### Developer Experience & Workflow Automation

- **Interactive CLI Wizard**: Refactored `theme:make` and `theme:activate` to natively use `laravel/prompts`. Creating a theme now initiates a beautiful interactive wizard for author, description, and options if arguments are omitted.
- **Theme Linter Command**: Introduced `php artisan theme:lint {theme?}`. This isolates code formatting by safely spinning up Laravel Pint for the theme's PHP classes, and concurrently triggering `npm run format` for its CSS/JS assets within a single command.
- **Auto-Proxy Dev Command**: Upgraded `php artisan theme:dev` to automatically detect the currently active theme context if none is manually specified.
- **Git Workspace Synchronization**: `theme:make` now automatically copies a robust `.gitignore` stub into newly generated themes so their vendor/build assets are ignored properly out of the box.
- **Upgrade Path Automation**: The `php artisan theme:upgrade` utility now gracefully automates the v1.2.x -> v1.3.0 schema migration. It scans existing themes, injects missing `.gitignore` files, and safely initializes `hooks` structures inside their `theme.json` configuration.

### Bug Fixes & Refinements

- **Stability**: Refactored underlying command/process integrations which resolved sporadic mock exceptions. The package maintains a 100% passing Pest test-suite and zero errors on PHPStan level-max.

---

## v1.2.1 - 2026-02-06

### DX & Diagnostics

- **theme:info Command**: New diagnostic command to display exhaustive theme metadata, resource detection, and inheritance chains.
- **Enhanced theme:list**: Added Version and Author columns with sorted and colorized output.
- **Improved theme:check**: Added author validation and optimization suggestions.
- **Unified Inheritance**: Extracted `getInheritanceChain()` into `ThemeManager` for robust, reusable traversal.
- **Blade Directives**: Added `@theme_asset` and `@theme_vite` for cleaner template-level asset resolution.

### Fixed Issues

- **Livewire Component Resolution**: Fixed a critical bug where a static resolver flag prevented Livewire missing component resolution from being re-registered during sequential test execution or application refreshes.

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
