# Commands

Laravel Themer provides a suite of Artisan commands to manage your themes effectively.

## Creating a Theme

Generate a new theme with a standard directory structure:

```bash
php artisan theme:make "Dark Theme"
```

Optional arguments:
- `--parent=`: Specify a parent theme to inherit from.

## Activating a Theme

Switch the active theme globally. This updates your `.env` file (`THEME=...`) and handles asset publishing if configured.

```bash
php artisan theme:activate "Dark Theme"
```

## Listing Themes

View all discovered themes and their status:

```bash
php artisan theme:list
```

## Publishing Assets

Publish theme assets to the public directory. This is usually handled automatically, but can be run manually:

```bash
php artisan theme:publish "Dark Theme"
```

If no theme is specified, it publishes assets for all themes.

## Hierarchy Integrity

Validate theme inheritance chains and detect circular dependencies:

```bash
php artisan theme:check
```

## Production Optimization

Enable Zero-IO discovery by caching theme metadata. This eliminates filesystem hits during the Laravel boot cycle:

```bash
php artisan theme:cache
```

To clear the cache:

```bash
php artisan theme:clear
```
## Making Components

Create a theme-specific Livewire component:

```bash
php artisan make:livewire Header --theme="Dark Theme"
```

This places the component in `themes/dark-theme/app/Livewire` and the view in `themes/dark-theme/resources/views/livewire`.
