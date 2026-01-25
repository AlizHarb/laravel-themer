# Directory Structure

Laravel Themer encourages a consistent structure for all your themes. When you run `php artisan theme:make`, the following structure is generated:

```text
themes/
├── my-theme/
│   ├── theme.json           # Theme configuration
│   ├── app/                 # Theme-specific logic
│   │   └── Livewire/        # Livewire components
│   ├── resources/
│   │   ├── assets/          # CSS, JS, Images
│   │   │   ├── css/
│   │   │   └── js/
│   │   └── views/           # Blade templates
│   │       ├── layouts/
│   │       └── livewire/
│   └── lang/                # Translations
```

## theme.json

The `theme.json` file is the heart of your theme. It defines metadata and configuration:

```json
{
    "name": "My Theme",
    "asset_path": "themes/my-theme",
    "parent": "base-theme"
}
```

- **name**: The display name of the theme.
- **asset_path**: Where assets should be published to in `public/`.
- **parent**: (Optional) The name of a parent theme to inherit from.
