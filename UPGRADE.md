# Upgrade Guide: Laravel Themer v1.1.x to v1.2.0

Laravel Themer v1.2.0 introduces several new features and significant internal improvements for type safety and stability.

---

## Automated Upgrade (Recommended)

We have provided a new Artisan command to automate most of the upgrade process.

1.  **Run the upgrade command**:
    ```bash
    # Upgrade all themes
    php artisan theme:upgrade

    # OR upgrade a specific theme
    php artisan theme:upgrade --theme=your-theme-name
    ```

    This command will:
    - Add `package.json` to any theme that is missing it.
    - Add `vite.config.js` to any theme that is missing it.
    - Automatically sync the `vite` version with your root project.
    - Configure **NPM Workspaces** in your root `package.json`.
    - Add helper scripts (`themes:dev`, `themes:build`) to your root `package.json`.

---

---

## New Asset Shortcuts

Build and develop your themes directly via Artisan.

```bash
# Run Vite dev server for a theme
php artisan theme:dev --theme=my-theme

# Build production assets for a theme
php artisan theme:build --theme=my-theme
```

---

## New Management Tools

### Cloning
Quickly create a new theme based on an existing one.
```bash
php artisan theme:clone new-theme --theme=source-theme
```

### Safe Deletion
Delete themes and their published assets. The command respects the `removable` flag in `theme.json` to prevent accidental deletion of core themes.
```bash
php artisan theme:delete --theme=temporary-theme
```

---

## New Command: `theme:npm`

You can now manage theme-specific NPM dependencies directly from the root project.

```bash
php artisan theme:npm --theme=your-theme-name install package-name
```

---

## Theme Registration Flags

Themes now support `removable` and `disableable` flags in `theme.json` for better integration with management tools.

```json
{
    "name": "My Theme",
    "slug": "my-theme",
    "asset_path": "themes/my-theme",
    "removable": true,
    "disableable": true
}
```

---

## Initialize Workspaces
After running the upgrade command, initialize your workspaces:

```bash
npm install
```

---

## Why Upgrade?

- **Zero Storage Overhead**: No more duplicated `node_modules` folders in every theme.
- **Fast Build Times**: Vite is significantly faster than legacy asset managers.
- **Independence**: Each theme can manage its own libraries without polluting the root project.
- **Unified Workflow**: Use `npm run themes:dev` to serve all your theme assets at once.
