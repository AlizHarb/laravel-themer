# Configuration

Publish the configuration file to customize the package:

```bash
php artisan vendor:publish --tag="themer-config"
```

## Options

### `themes_path`
The directory where your themes are stored. Defaults to `base_path('themes')`.

### `active`
The name of the theme to be active by default. Can be overridden via `.env`.

### `assets`
Configuration for asset publishing.
- `path`: The subdirectory in `public/` to publish to (e.g., `themes` -> `public/themes`).
- `publish_on_activate`: Automatically publish assets when `theme:activate` is run.
- `symlink`: Use symlinks instead of copying (recommended for dev).

### `discovery`
- `scan_modules`: Set to `true` to enable discovery of themes inside `modules/` (requires `laravel-modular`).

> [!TIP]
> Use `php artisan theme:cache` in production to optimize performance. This generates a static registry that eliminates all filesystem hits during the boot process.
