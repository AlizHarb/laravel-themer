# Diagnostics

Laravel Themer v1.4.0 adds production-grade diagnostics for theme health, manifests, inheritance, assets, cache freshness, and automation.

## Doctor

```bash
php artisan theme:doctor
php artisan theme:doctor --json
php artisan theme:doctor --fix
```

`theme:doctor` checks missing directories, invalid manifests, missing parents, duplicate slugs, missing assets, screenshots, node_modules leakage, missing authors, and stale cache metadata.

Use `--json` in CI or deployment dashboards.

Use `--fix` for safe repairs such as creating the themes directory and refreshing stale cache.

## Status

```bash
php artisan theme:status
php artisan theme:status --json
```

Shows the active theme, total themes, issue counts, and cache state.

## Debug

```bash
php artisan theme:debug brand
php artisan theme:debug brand --json
```

Shows manifest data, inheritance, resources, tokens, conflicts, provided capabilities, and cache metadata.

## Graph

```bash
php artisan theme:graph
php artisan theme:graph --format=json
php artisan theme:graph --format=dot
```

Renders parent/child relationships for inheritance review.

## Why

```bash
php artisan theme:why brand
php artisan theme:why brand --json
```

Explains what a theme provides, whether it is active, what it requires, and which themes inherit from it.
