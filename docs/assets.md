# Asset Management

Laravel Themer integrates seamlessly with Vite for modern asset compilation with hot module replacement.

## Vite Configuration

### Per-Theme Vite Config

Each theme gets its own `vite.config.js`:

**`themes/mytheme/vite.config.js`:**

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/assets/css/app.css',
                'resources/assets/js/app.js',
            ],
            publicDirectory: '../../public',
            buildDirectory: 'themes/mytheme',
        }),
    ],
});
```

### NPM Workspaces

Laravel Themer automatically configures NPM workspaces in your root `package.json`:

```json
{
  "workspaces": [
    "themes/*"
  ]
}
```

This allows you to manage all theme dependencies from the root:

```bash
npm install
```

## Development Workflow

### Start Development Server

```bash
php artisan theme:dev mytheme
```

This runs `npm run dev` in the theme's directory with hot module replacement.

### Build for Production

```bash
php artisan theme:build mytheme
```

This compiles and minifies assets for production.

## Using Assets in Views

### Vite Directive

```blade
@vite(['resources/assets/css/app.css', 'resources/assets/js/app.js'], 'themes/mytheme')
```

The second parameter specifies the theme's build directory.

### Theme Asset Helper

For non-Vite assets (images, fonts, etc.):

```blade
<img src="{{ theme_asset('images/logo.png') }}" alt="Logo">

<link rel="stylesheet" href="{{ theme_asset('css/custom.css') }}">

<script src="{{ theme_asset('js/custom.js') }}"></script>
```

## Asset Structure

### Recommended Organization

```
resources/assets/
├── css/
│   ├── app.css              # Main entry point
│   ├── components/
│   │   ├── buttons.css
│   │   ├── forms.css
│   │   └── cards.css
│   ├── layouts/
│   │   ├── header.css
│   │   ├── footer.css
│   │   └── sidebar.css
│   └── utilities/
│       ├── colors.css
│       └── spacing.css
├── js/
│   ├── app.js               # Main entry point
│   ├── components/
│   │   ├── dropdown.js
│   │   ├── modal.js
│   │   └── tabs.js
│   └── utils/
│       └── helpers.js
├── images/
│   ├── logo.png
│   ├── hero.jpg
│   └── icons/
└── fonts/
    ├── inter-regular.woff2
    └── inter-bold.woff2
```

## CSS with Tailwind

### Installing Tailwind

```bash
php artisan theme:npm mytheme "add -D tailwindcss postcss autoprefixer"
php artisan theme:npm mytheme "exec tailwindcss init -p"
```

### Tailwind Config

**`themes/mytheme/tailwind.config.js`:**

```js
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./app/Livewire/**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#6366f1',
        secondary: '#8b5cf6',
      },
    },
  },
  plugins: [],
}
```

### CSS Entry Point

**`themes/mytheme/resources/assets/css/app.css`:**

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer components {
  .btn-primary {
    @apply bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90;
  }
}
```

## JavaScript with Alpine.js

### Installing Alpine

```bash
php artisan theme:npm mytheme "add alpinejs"
```

### JavaScript Entry Point

**`themes/mytheme/resources/assets/js/app.js`:**

```js
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Custom Alpine components
Alpine.data('dropdown', () => ({
    open: false,
    toggle() {
        this.open = !this.open;
    }
}));
```

### Using in Views

```blade
<div x-data="dropdown">
    <button @click="toggle">Toggle</button>
    <div x-show="open" x-cloak>
        Dropdown content
    </div>
</div>
```

## Asset Publishing

### Automatic Publishing

By default, assets are published when a theme is activated:

```bash
php artisan theme:activate mytheme
```

### Manual Publishing

```bash
# Publish specific theme
php artisan theme:publish mytheme

# Publish all themes
php artisan theme:publish
```

### Symlink vs Copy

Configure in `config/themer.php`:

```php
'assets' => [
    'symlink' => env('THEMER_SYMLINK', true),
],
```

**Symlink (Development):**
- Faster
- Changes reflect immediately
- Requires server permissions

**Copy (Production):**
- More compatible
- Safer for deployments
- Requires manual republishing after changes

## Asset Inheritance

### Parent Theme Assets

Child themes automatically inherit parent theme assets:

```
base-theme/resources/assets/
  └── css/variables.css
corporate-theme/resources/assets/
  └── css/app.css  (can import ../../../base-theme/resources/assets/css/variables.css)
```

### Importing Parent Assets

**In CSS:**

```css
@import '../../../base-theme/resources/assets/css/variables.css';

:root {
  --primary-color: var(--base-primary);
}
```

**In JavaScript:**

```js
import { helper } from '../../../base-theme/resources/assets/js/utils/helpers.js';

helper.doSomething();
```

## Images and Fonts

### Storing Assets

```
resources/assets/
├── images/
│   ├── logo.png
│   ├── hero.jpg
│   └── icons/
│       ├── home.svg
│       └── user.svg
└── fonts/
    ├── inter-regular.woff2
    └── inter-bold.woff2
```

### Using in CSS

```css
@font-face {
  font-family: 'Inter';
  src: url('../fonts/inter-regular.woff2') format('woff2');
  font-weight: 400;
}

.hero {
  background-image: url('../images/hero.jpg');
}
```

### Using in Views

```blade
<img src="{{ theme_asset('images/logo.png') }}" alt="Logo">
```

## Production Optimization

### Build Assets

```bash
php artisan theme:build mytheme
```

This:
- Minifies CSS and JavaScript
- Optimizes images
- Generates source maps
- Creates manifest file

### Cache Busting

Vite automatically handles cache busting via content hashing:

```html
<link rel="stylesheet" href="/themes/mytheme/assets/app-abc123.css">
```

### Preloading

Add preload hints for critical assets:

```blade
@vite(['resources/assets/css/app.css'], 'themes/mytheme')

<link rel="preload" href="{{ theme_asset('fonts/inter-regular.woff2') }}" as="font" type="font/woff2" crossorigin>
```

## Best Practices

### 1. Use Vite for Compilation

Always compile CSS/JS through Vite, not direct linking.

### 2. Organize by Feature

Group related styles and scripts:

```
css/
├── components/
│   └── button.css
js/
├── components/
│   └── button.js
```

### 3. Leverage Tree Shaking

Import only what you need:

```js
import { debounce } from 'lodash-es';
```

### 4. Optimize Images

Use modern formats (WebP, AVIF) and responsive images:

```blade
<picture>
    <source srcset="{{ theme_asset('images/hero.avif') }}" type="image/avif">
    <source srcset="{{ theme_asset('images/hero.webp') }}" type="image/webp">
    <img src="{{ theme_asset('images/hero.jpg') }}" alt="Hero">
</picture>
```

### 5. Use CSS Variables

Define theme colors and spacing as CSS variables:

```css
:root {
  --color-primary: #6366f1;
  --color-secondary: #8b5cf6;
  --spacing-unit: 0.25rem;
}
```

## Next Steps

- [Helper Functions](helpers.md)
- [Advanced Features](advanced.md)
- [Production Deployment](deployment.md)
