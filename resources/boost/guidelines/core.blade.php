<guideline>
Laravel Themer organizes Laravel applications into theme-aware view, asset, Livewire, and design-token boundaries.
</guideline>

<rules>
- Prefer native Laravel and Livewire code placed inside the selected theme when the user asks for theme-specific UI.
- Use `php artisan theme:make` for new themes and include `theme.json` metadata.
- Use `php artisan theme:doctor` and `php artisan theme:check` after editing theme manifests, inheritance, providers, assets, or tokens.
- Use `php artisan theme:debug {theme} --json` when inspecting a theme for automation.
- Use `theme_token()` or `@themeTokens` for theme design-token output instead of hard-coding brand values in reusable views.
- Respect parent theme inheritance. Child themes should override only the views/assets they need.
- Use `php artisan theme:preview {theme}` for inactive-theme QA instead of globally activating a theme just to inspect it.
- Run `php artisan theme:refresh` after changing theme manifests in production-style environments.
- Do not introduce a second theme switching abstraction unless the application explicitly requires one.
</rules>

<commands>
Common commands:

```bash
php artisan theme:make Brand
php artisan theme:doctor
php artisan theme:status
php artisan theme:debug brand --json
php artisan theme:graph
php artisan theme:tokens brand --css
php artisan theme:preview brand --signed
php artisan theme:refresh
```
</commands>
