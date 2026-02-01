# Working with Views

Laravel Themer extends Blade's view system with theme-aware resolution and custom directives.

## View Resolution

### Automatic Theme Resolution

When a theme is active, Laravel Themer **prepends** theme view paths to Laravel's view finder. This means views can be referenced **with or without** the `theme::` namespace prefix.

**Resolution Order:**
1. `themes/{active-theme}/resources/views/{view}`
2. `themes/{parent-theme}/resources/views/{view}` (if parent exists)
3. `resources/views/{view}` (application default)

### View Namespaces

Laravel Themer provides multiple ways to reference theme views:

#### Option 1: Direct Reference (Recommended)

Theme paths are automatically prepended, so standard Laravel view syntax works:

```blade
{{-- Automatically resolves to active theme first --}}
@include('welcome')
@extends('layouts.app')
```

#### Option 2: Explicit Theme Namespace

Use `theme::` for explicit theme references:

```blade
{{-- Explicitly reference theme namespace --}}
@include('theme::welcome')
@extends('theme::layouts.app')
```

**Both approaches work identically.** The theme path is checked first in either case.

### Auto-Namespaces

Configured in `config/themer.php`:

```php
'auto_namespaces' => [
    'layouts' => 'resources/views/layouts',
    'pages' => 'resources/views/livewire/pages',
],
```

**Usage:**

```blade
{{-- Resolves to active theme's layouts directory --}}
<x-layouts::app>
    @yield('content')
</x-layouts::app>

{{-- Resolves to active theme's pages directory --}}
@include('pages::home')
```

## Custom Directives

### `@theme_include`

Include a view with automatic theme fallback:

```blade
@theme_include('partials.header')
```

Equivalent to:

```blade
@include('theme::partials.header')
```

## Layouts

### Creating a Layout

```bash
php artisan livewire:layout --theme=mytheme
```

**Example Layout (`themes/mytheme/resources/views/layouts/app.blade.php`):**

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'My Application' }}</title>
    
    @vite(['resources/assets/css/app.css', 'resources/assets/js/app.js'], 'themes/mytheme')
</head>
<body>
    <x-layouts::navigation />
    
    <main>
        {{ $slot }}
    </main>
    
    <x-layouts::footer />
</body>
</html>
```

### Using Layouts

```blade
<x-layouts::app>
    <x-slot:title>
        Home Page
    </x-slot:title>

    <h1>Welcome to My Theme</h1>
</x-layouts::app>
```

## Components

### Creating Components

```bash
php artisan make:component button --theme=mytheme
```

Creates:
- `themes/mytheme/app/View/Components/Button.php`
- `themes/mytheme/resources/views/components/button.blade.php`

### Anonymous Components

Create anonymous components directly in the views directory:

```
themes/mytheme/resources/views/components/
├── button.blade.php
├── card.blade.php
└── alert.blade.php
```

**Usage:**

```blade
<x-theme::button variant="primary">
    Click Me
</x-theme::button>
```

### Component Inheritance

Child themes can override parent theme components:

```
base-theme/resources/views/components/button.blade.php
corporate-theme/resources/views/components/button.blade.php  ← Overrides
```

## Partials

### Organizing Partials

```
resources/views/
├── partials/
│   ├── header.blade.php
│   ├── footer.blade.php
│   ├── navigation.blade.php
│   └── sidebar.blade.php
```

### Including Partials

```blade
@include('theme::partials.header')

<main>
    @yield('content')
</main>

@include('theme::partials.footer')
```

## View Composers

Register view composers in your theme's service provider:

```php
use Illuminate\Support\Facades\View;

public function boot(): void
{
    View::composer('theme::layouts.app', function ($view) {
        $view->with('siteName', config('app.name'));
        $view->with('currentYear', date('Y'));
    });
}
```

## Translations

### Using Translations in Views

```blade
<h1>{{ __('theme::messages.welcome', ['name' => $user->name]) }}</h1>

<nav>
    <a href="/">{{ __('theme::navigation.home') }}</a>
    <a href="/about">{{ __('theme::navigation.about') }}</a>
</nav>
```

### Translation Files

**`themes/mytheme/lang/en/messages.php`:**

```php
<?php

return [
    'welcome' => 'Welcome, :name!',
    'navigation' => [
        'home' => 'Home',
        'about' => 'About',
        'contact' => 'Contact',
    ],
];
```

## Conditional Theme Logic

### Check Active Theme

```blade
@if(is_theme_active('mytheme'))
    <div class="theme-specific-feature">
        <!-- Only shown when mytheme is active -->
    </div>
@endif
```

### Get Active Theme

```blade
@php
    $theme = get_active_theme();
@endphp

<div class="theme-{{ $theme->slug }}">
    <p>Current theme: {{ $theme->name }}</p>
    <p>Version: {{ $theme->version }}</p>
</div>
```

## Asset References

### Using Vite

```blade
@vite(['resources/assets/css/app.css', 'resources/assets/js/app.js'], 'themes/mytheme')
```

### Direct Asset URLs

```blade
<img src="{{ theme_asset('images/logo.png') }}" alt="Logo">

<link rel="stylesheet" href="{{ theme_asset('css/custom.css') }}">

<script src="{{ theme_asset('js/custom.js') }}"></script>
```

## Best Practices

### 1. Choose Your Referencing Style

Both direct and namespaced references work - pick one style and be consistent:

```blade
✅ @extends('layouts.app')  // Direct (cleaner)
✅ @extends('theme::layouts.app')  // Explicit (clearer intent)
❌ Mixing both styles inconsistently
```

### 2. Leverage Inheritance

Design base themes for reusability:

```
base-theme/
  └── layouts/app.blade.php  (foundation)
corporate-theme/
  └── layouts/app.blade.php  (extends base, adds branding)
```

### 3. Keep Views Portable

Avoid hardcoded paths:

```blade
✅ <img src="{{ theme_asset('logo.png') }}">
❌ <img src="/themes/mytheme/assets/logo.png">
```

### 4. Use Components

Break down complex views into reusable components:

```blade
<x-theme::card>
    <x-slot:header>
        <h2>{{ $title }}</h2>
    </x-slot:header>
    
    {{ $content }}
    
    <x-slot:footer>
        <x-theme::button>Learn More</x-theme::button>
    </x-slot:footer>
</x-theme::card>
```

## Next Steps

- [Livewire Integration](livewire.md)
- [Asset Management](assets.md)
- [Helper Functions](helpers.md)
