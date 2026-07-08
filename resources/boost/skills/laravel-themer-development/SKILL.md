---
name: laravel-themer-development
description: Build and maintain Laravel Themer themes using native Laravel, Livewire, Vite, diagnostics, previewing, and design-token workflows.
---

# Laravel Themer Development

Use this skill when working in an application that uses `alizharb/laravel-themer`.

## Workflow

1. Inspect themes with `php artisan theme:list`, `php artisan theme:status`, and `php artisan theme:debug {theme} --json`.
2. Generate new themes with `php artisan theme:make`.
3. Put theme views in `resources/views`, Livewire classes in `app/Livewire`, and assets in `resources/assets`.
4. Use `theme.json` for metadata, inheritance, required modules, conflicts, provided capabilities, screenshots, hooks, and design tokens.
5. Use `theme_token()` and `@themeTokens` for reusable brand values.
6. Use `php artisan theme:preview {theme}` to QA inactive themes.
7. Run `php artisan theme:doctor`, `php artisan theme:check`, and `php artisan theme:refresh` after structural changes.

## Guardrails

- Do not bypass theme inheritance by hard-coding absolute theme paths in views.
- Do not globally activate a theme just to inspect it; use preview URLs.
- Do not duplicate parent theme views unless the child theme intentionally overrides them.
- Keep theme manifests valid and machine-readable.
