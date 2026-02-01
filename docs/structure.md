# Theme Structure

Understanding the theme directory structure is essential for building powerful, maintainable themes.

## Directory Layout

```
themes/mytheme/
├── theme.json              # Theme metadata (required)
├── ThemeServiceProvider.php # Optional service provider
├── package.json            # NPM dependencies
├── vite.config.js          # Vite configuration
├── app/
│   └── Livewire/          # Livewire components
│       ├── Pages/
│       ├── Layouts/
│       └── Components/
├── resources/
│   ├── assets/
│   │   ├── css/
│   │   │   └── app.css    # Main stylesheet
│   │   ├── js/
│   │   │   └── app.js     # Main JavaScript
│   │   └── screenshots/
│   │       ├── screenshot-light.png
│   │       └── screenshot-dark.png
│   └── views/
│       ├── layouts/       # Layout files
│       ├── livewire/      # Livewire views
│       ├── components/    # Blade components
│       └── pages/         # Page templates
└── lang/                  # Translations
    ├── en/
    └── ar/
```

## theme.json

The `theme.json` file contains theme metadata and configuration.

### Minimal Example

```json
{
  "name": "mytheme",
  "version": "1.0.0"
}
```

### Complete Example

```json
{
  "name": "mytheme",
  "slug": "mytheme",
  "version": "1.2.0",
  "description": "A professional, modern theme",
  "author": "John Doe",
  "authors": [
    {
      "name": "John Doe",
      "email": "john@example.com",
      "role": "Lead Developer"
    }
  ],
  "parent": "basetheme",
  "tags": ["modern", "responsive", "dark-mode"],
  "screenshots": [
    "resources/assets/screenshots/screenshot-light.png",
    "resources/assets/screenshots/screenshot-dark.png"
  ],
  "removable": true,
  "disableable": true,
  "module": "Blog"
}
```

### Field Reference

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | ✅ | Theme name (used for display) |
| `slug` | string | | URL-safe identifier (auto-generated from name) |
| `version` | string | | Semantic version (default: "1.0.0") |
| `description` | string | | Short description |
| `author` | string | | Primary author name |
| `authors` | array | | Detailed author information |
| `parent` | string | | Parent theme slug for inheritance |
| `tags` | array | | Searchable tags |
| `screenshots` | array | | Paths to preview images |
| `removable` | boolean | | Can be deleted via CLI (default: true) |
| `disableable` | boolean | | Can be deactivated (default: true) |
| `module` | string | | Associated Laravel Modular module |

## Theme Inheritance

Themes can inherit from parent themes, creating a powerful cascading system.

### Example Hierarchy

```
base-theme (foundation)
  └── corporate-theme (branding)
      └── corporate-dark (variant)
```

### Defining a Parent

```json
{
  "name": "corporate-dark",
  "parent": "corporate-theme"
}
```

### Inheritance Behavior

- **Views**: Child theme views override parent views
- **Assets**: Child assets take precedence
- **Translations**: Merged with child overriding parent
- **Livewire Components**: Child components override parent components

### Loop Protection

Laravel Themer automatically detects and prevents circular inheritance:

```
Theme A → Theme B → Theme C → Theme A ❌ (Error)
```

## Service Provider

Generate a namespaced service provider for custom theme logic:

```bash
php artisan theme:make MyTheme --provider
```

### Example Service Provider

```php
<?php

namespace Theme\MyTheme;

use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind theme-specific services
        $this->app->singleton('mytheme.config', function () {
            return config('mytheme');
        });
    }

    public function boot(): void
    {
        // Register middleware
        $this->app['router']->pushMiddlewareToGroup('web', \Theme\MyTheme\Http\Middleware\ThemeMiddleware::class);
        
        // Register event listeners
        \Illuminate\Support\Facades\Event::listen(
            \AlizHarb\Themer\Events\ThemeActivated::class,
            \Theme\MyTheme\Listeners\OnThemeActivated::class
        );
    }
}
```

## Asset Structure

### CSS Organization

```
resources/assets/css/
├── app.css              # Main entry point
├── components/
│   ├── buttons.css
│   ├── forms.css
│   └── cards.css
├── layouts/
│   ├── header.css
│   ├── footer.css
│   └── sidebar.css
└── utilities/
    ├── colors.css
    └── spacing.css
```

### JavaScript Organization

```
resources/assets/js/
├── app.js               # Main entry point
├── components/
│   ├── dropdown.js
│   └── modal.js
└── utils/
    └── helpers.js
```

## View Organization

### Layouts

```
resources/views/layouts/
├── app.blade.php        # Main layout
├── guest.blade.php      # Guest layout
└── admin.blade.php      # Admin layout
```

### Livewire Views

```
resources/views/livewire/
├── pages/
│   ├── home.blade.php
│   ├── about.blade.php
│   └── contact.blade.php
├── layouts/
│   └── navigation.blade.php
└── components/
    ├── button.blade.php
    └── card.blade.php
```

## Translations

```
lang/
├── en/
│   ├── theme.php
│   └── messages.php
└── ar/
    ├── theme.php
    └── messages.php
```

**Example `theme.php`:**

```php
<?php

return [
    'welcome' => 'Welcome to :theme',
    'navigation' => [
        'home' => 'Home',
        'about' => 'About',
        'contact' => 'Contact',
    ],
];
```

## Next Steps

- [Working with Views](views.md)
- [Livewire Integration](livewire.md)
- [Asset Management](assets.md)
