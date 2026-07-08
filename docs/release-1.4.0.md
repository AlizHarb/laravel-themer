# Laravel Themer v1.4.0

Laravel Themer v1.4.0 focuses on production theme operations, Laravel Boost support, design tokens, safer previews, richer manifests, and stronger Laravel 13 testing.

## Highlights

- `theme:doctor`, `theme:status`, `theme:debug`, `theme:graph`, and `theme:why`.
- `theme:refresh` with cache freshness metadata.
- `theme:preview` for inactive theme QA.
- `theme:tokens`, `theme_token()`, `theme_tokens()`, and `@themeTokens`.
- Manifest validation for `requires`, `conflicts`, `provides`, and `tokens`.
- Laravel Boost guidelines and skill.
- Expanded lifecycle events.
- Laravel 13/Testbench 11 CI matrix.

## Recommended Upgrade

```bash
composer update alizharb/laravel-themer
php artisan theme:refresh
php artisan theme:doctor
php artisan theme:check
vendor/bin/pest
```
