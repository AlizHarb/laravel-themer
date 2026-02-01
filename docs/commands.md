# Commands Reference

Laravel Themer provides a comprehensive suite of Artisan commands for theme management.

## Theme Management

### `theme:make`

Create a new theme with complete directory structure.

```bash
php artisan theme:make {name} [options]
```

**Arguments:**
- `name` - The name of the theme (e.g., "MyTheme")

**Options:**
- `--parent=` - Optional parent theme for inheritance
- `--description=` - Theme description
- `--author=` - Theme author name
- `--tags=` - Comma-separated tags
- `--provider` - Generate a namespaced ThemeServiceProvider

**Examples:**

```bash
# Basic theme
php artisan theme:make Portfolio

# Theme with metadata
php artisan theme:make Portfolio \
  --description="Professional portfolio theme" \
  --author="John Doe" \
  --tags="portfolio,modern,responsive"

# Theme with parent and provider
php artisan theme:make PortfolioDark \
  --parent=portfolio \
  --provider
```

---

### `theme:activate`

Activate a theme and update the `.env` file.

```bash
php artisan theme:activate {theme?} [options]
```

**Arguments:**
- `theme` - Theme name or slug (optional, will prompt if not provided)

**Options:**
- `--no-interaction` - Skip confirmation prompts

**Examples:**

```bash
# Interactive selection
php artisan theme:activate

# Direct activation
php artisan theme:activate portfolio
```

---

### `theme:list`

List all discovered themes with their metadata.

```bash
php artisan theme:list
```

**Output includes:**
- Theme name and slug
- Version
- Parent theme (if any)
- Author
- Tags
- Removable/Disableable flags

---

### `theme:delete`

Delete a theme from the filesystem.

```bash
php artisan theme:delete {theme} [options]
```

**Arguments:**
- `theme` - Theme name or slug

**Options:**
- `--force` - Skip confirmation prompt

**Examples:**

```bash
# Interactive deletion
php artisan theme:delete portfolio

# Force deletion
php artisan theme:delete portfolio --force
```

> **Note:** Only themes marked as `removable: true` in `theme.json` can be deleted without `--force`.

---

### `theme:clone`

Clone an existing theme to create a new one.

```bash
php artisan theme:clone {source} {destination}
```

**Arguments:**
- `source` - Source theme name or slug
- `destination` - New theme name

**Example:**

```bash
php artisan theme:clone portfolio portfolio-dark
```

This creates a complete copy with updated metadata.

---

## Asset Management

### `theme:publish`

Publish theme assets to the public directory.

```bash
php artisan theme:publish {theme?}
```

**Arguments:**
- `theme` - Theme name or slug (optional, publishes all if not provided)

**Examples:**

```bash
# Publish all themes
php artisan theme:publish

# Publish specific theme
php artisan theme:publish portfolio
```

---

### `theme:dev`

Start Vite development server for a theme.

```bash
php artisan theme:dev {theme}
```

**Arguments:**
- `theme` - Theme name or slug

**Example:**

```bash
php artisan theme:dev portfolio
```

This runs `npm run dev` in the theme's directory with hot module replacement.

---

### `theme:build`

Build production assets for a theme.

```bash
php artisan theme:build {theme}
```

**Arguments:**
- `theme` - Theme name or slug

**Example:**

```bash
php artisan theme:build portfolio
```

This runs `npm run build` to compile optimized production assets.

---

### `theme:npm`

Run NPM commands in a theme's context.

```bash
php artisan theme:npm {theme} {command}
```

**Arguments:**
- `theme` - Theme name or slug
- `command` - NPM command to run

**Examples:**

```bash
# Install dependencies
php artisan theme:npm portfolio install

# Add a package
php artisan theme:npm portfolio "add alpinejs"

# Run custom script
php artisan theme:npm portfolio "run custom-script"
```

---

## Maintenance

### `theme:cache`

Cache theme discovery for production performance.

```bash
php artisan theme:cache
```

This creates a cache file at `bootstrap/cache/themes.php` containing all discovered themes.

---

### `theme:clear`

Clear the theme discovery cache.

```bash
php artisan theme:clear
```

Use this after adding, removing, or modifying themes during development.

---

### `theme:check`

Validate theme structure and dependencies.

```bash
php artisan theme:check {theme?}
```

**Arguments:**
- `theme` - Theme name or slug (optional, checks all if not provided)

**Checks:**
- Theme hierarchy integrity
- Circular dependency detection
- Missing parent themes
- Asset health
- Missing screenshots
- Required module dependencies (if using laravel-modular)

**Example:**

```bash
php artisan theme:check portfolio
```

---

### `theme:upgrade`

Upgrade themes to the latest asset structure.

```bash
php artisan theme:upgrade {theme?}
```

**Arguments:**
- `theme` - Theme name or slug (optional, upgrades all if not provided)

**What it does:**
- Migrates old asset structures
- Updates `package.json` and `vite.config.js`
- Runs `themer:install` to configure NPM workspaces

---

## Installation

### `themer:install`

Install and configure Laravel Themer.

```bash
php artisan themer:install
```

**What it does:**
- Publishes `config/themer.php`
- Creates `themes/` directory
- Configures NPM Workspaces in root `package.json`
- Sets up Vite integration

---

## Laravel Command Overrides

Laravel Themer extends standard Laravel and Livewire commands to be theme-aware.

### `make:livewire`

Create Livewire components in a theme.

```bash
php artisan make:livewire {name} --theme={theme}
```

**Example:**

```bash
php artisan make:livewire home --class --theme=portfolio
```

Creates:
- `themes/portfolio/app/Livewire/Home.php`
- `themes/portfolio/resources/views/livewire/home.blade.php`

---

### `livewire:layout`

Create a Livewire layout in a theme.

```bash
php artisan livewire:layout --theme={theme}
```

**Example:**

```bash
php artisan livewire:layout --theme=portfolio
```

Creates `themes/portfolio/resources/views/layouts/app.blade.php`.

---

### `make:component`

Create Blade components in a theme.

```bash
php artisan make:component {name} --theme={theme}
```

**Example:**

```bash
php artisan make:component button --theme=portfolio
```

---

### `make:view`

Create views in a theme.

```bash
php artisan make:view {name} --theme={theme}
```

**Example:**

```bash
php artisan make:view welcome --theme=portfolio
```

---

## Next Steps

- [Theme Structure](structure.md)
- [Asset Management](assets.md)
- [Livewire Integration](livewire.md)
