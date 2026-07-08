# Design Tokens

Themes may expose design tokens from `theme.json`.

```json
{
  "tokens": {
    "color.primary": "#2563eb",
    "color.background": "#ffffff",
    "radius.card": "1rem"
  }
}
```

## PHP

```php
theme_token('color.primary');
theme_token('color.missing', '#000000');
theme_tokens();
```

## Blade

```blade
@themeTokens
```

This outputs CSS variables such as:

```css
:root {
  --theme-color-primary: #2563eb;
}
```

## CLI

```bash
php artisan theme:tokens brand
php artisan theme:tokens brand --json
php artisan theme:tokens brand --css
```
