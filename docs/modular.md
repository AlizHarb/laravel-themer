# Modular Integration

Laravel Themer integrates seamlessly with `laravel-modular` to provide a powerful modular theming experience.

## Overview

When both packages are installed, Themer can automatically discover themes located within your modules. This allows you to encapsulate themes alongside their related business logic.

## Discovery

To enable modular discovery, ensure `scan_modules` is set to `true` in `config/themer.php`.

```php
'discovery' => [
    'filename' => 'theme.json',
    'scan_modules' => true,
],
```

## Structure

Place your theme inside the `resources/theme` directory of your module:

```text
modules/
├── Blog/
│   ├── resources/
│   │   ├── theme/          # Theme root
│   │   │   ├── theme.json
│   │   │   └── ...
```

Themer will automatically detect `modules/Blog/resources/theme/theme.json` and register it as a theme (e.g., `module-blog`).
