# Quick Start

Get up and running with Laravel Themer in under 5 minutes.

## Installation

```bash
composer require alizharb/laravel-themer
php artisan themer:install
```

## Create a Theme

```bash
php artisan theme:make Portfolio \
  --description="Professional portfolio theme" \
  --author="Your Name" \
  --tags="portfolio,modern,dark"
```

## Theme Structure

Your new theme is created at `themes/portfolio/`:

```
portfolio/
├── theme.json              # Theme metadata
├── package.json            # NPM dependencies
├── vite.config.js          # Vite configuration
├── app/
│   └── Livewire/          # Livewire components
├── resources/
│   ├── assets/
│   │   ├── css/app.css    # Theme styles
│   │   ├── js/app.js      # Theme scripts
│   │   └── screenshots/   # Theme previews
│   └── views/             # Blade templates
└── lang/                  # Translations
```

## Create a Layout

```bash
php artisan livewire:layout --theme=portfolio
```

This creates `themes/portfolio/resources/views/layouts/app.blade.php`.

## Create a Page

```bash
php artisan make:livewire home --class --theme=portfolio
```

This creates:
- `themes/portfolio/app/Livewire/Home.php`
- `themes/portfolio/resources/views/livewire/home.blade.php`

## Activate Your Theme

```bash
php artisan theme:activate portfolio
```

## Compile Assets

```bash
php artisan theme:dev portfolio
```

This starts Vite in development mode with hot module replacement.

## Access Your Theme

Visit your application - it's now using your new theme!

## Next Steps

- [Theme Structure Deep Dive](structure.md)
- [Working with Views](views.md)
- [Livewire Integration](livewire.md)
- [Asset Management](assets.md)
