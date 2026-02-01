# Production Deployment

Best practices for deploying Laravel Themer applications to production.

## Pre-Deployment Checklist

### 1. Build Theme Assets

```bash
php artisan theme:build mytheme
```

This compiles and minifies all CSS and JavaScript files.

### 2. Cache Theme Discovery

```bash
php artisan theme:cache
```

This creates `bootstrap/cache/themes.php` for faster theme loading.

### 3. Publish Theme Assets

```bash
php artisan theme:publish
```

Ensure all theme assets are in the public directory.

### 4. Optimize Application

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Environment Configuration

### Production `.env`

```env
APP_ENV=production
APP_DEBUG=false

# Active theme
THEME=mytheme

# Disable symlinks in production
THEMER_SYMLINK=false
```

### Disable Auto-Publishing

In `config/themer.php`:

```php
'assets' => [
    'publish_on_activate' => false,
    'symlink' => false,
],
```

## Deployment Workflow

### Standard Deployment

```bash
#!/bin/bash

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install --production

# Build theme assets
php artisan theme:build mytheme

# Publish theme assets
php artisan theme:publish

# Cache everything
php artisan theme:cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
php artisan queue:restart
```

### Zero-Downtime Deployment

```bash
#!/bin/bash

# Use Laravel Envoy or Deployer
# Example with Envoy:

@servers(['production' => 'user@server'])

@task('deploy', ['on' => 'production'])
    cd /var/www/app
    
    # Maintenance mode
    php artisan down
    
    # Update code
    git pull origin main
    
    # Dependencies
    composer install --no-dev --optimize-autoloader
    npm ci --production
    
    # Build assets
    php artisan theme:build mytheme
    php artisan theme:publish
    
    # Cache
    php artisan theme:cache
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Migrations
    php artisan migrate --force
    
    # Exit maintenance
    php artisan up
    
    # Restart services
    php artisan queue:restart
@endtask
```

## CI/CD Integration

### GitHub Actions

```yaml
name: Deploy

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      
      - name: Setup Node
        uses: actions/setup-node@v3
        with:
          node-version: '20'
      
      - name: Install Dependencies
        run: |
          composer install --no-dev --optimize-autoloader
          npm ci --production
      
      - name: Build Theme Assets
        run: php artisan theme:build mytheme
      
      - name: Run Tests
        run: php artisan test
      
      - name: Deploy to Production
        run: |
          # Your deployment script
          ./deploy.sh
```

## Asset Optimization

### Image Optimization

Use tools like `spatie/image-optimizer`:

```bash
composer require spatie/image-optimizer
```

```php
use Spatie\ImageOptimizer\OptimizerChainFactory;

$optimizerChain = OptimizerChainFactory::create();
$optimizerChain->optimize(get_active_theme()->path . '/resources/assets/images/hero.jpg');
```

### CSS Purging

**Tailwind CSS 4** uses a config-less approach with `@theme` directive:

**`themes/mytheme/resources/assets/css/app.css`:**

```css
@import "tailwindcss";

@theme {
  --color-primary: oklch(60% 0.20 280);
  --color-secondary: oklch(70% 0.16 280);
}

@layer components {
  .btn-primary {
    background: var(--color-primary);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
  }
}
```

Content paths are automatically detected from your imports. No configuration file needed!

### JavaScript Minification

Vite automatically minifies in production builds.

## CDN Integration

### Configure Asset URL

In `.env`:

```env
ASSET_URL=https://cdn.example.com
```

### Upload Assets to CDN

```bash
# After building
php artisan theme:build mytheme

# Sync to S3/CloudFront
aws s3 sync public/themes/mytheme s3://your-bucket/themes/mytheme --delete
```

## Performance Monitoring

### Laravel Telescope

```bash
composer require laravel/telescope --dev
php artisan telescope:install
```

Monitor theme asset loading and view rendering times.

### New Relic / DataDog

Track theme-specific metrics:

```php
// In ThemeServiceProvider
public function boot()
{
    if (app()->environment('production')) {
        newrelic_add_custom_parameter('theme', get_active_theme()->slug);
    }
}
```

## Security Considerations

### 1. Validate Theme Sources

Only install themes from trusted sources.

### 2. Sanitize User Input

If allowing user theme selection:

```php
public function switchTheme(Request $request)
{
    $theme = $request->input('theme');
    
    if (!app('themer')->find($theme)) {
        abort(404);
    }
    
    // Validate against whitelist
    $allowed = ['default', 'dark', 'light'];
    if (!in_array($theme, $allowed)) {
        abort(403);
    }
    
    session(['theme' => $theme]);
}
```

### 3. Protect Theme Files

Ensure theme directories are not writable by web server:

```bash
chmod -R 755 themes/
```

### 4. Content Security Policy

Configure CSP headers for theme assets:

```php
// In middleware
$response->headers->set('Content-Security-Policy', 
    "default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self'"
);
```

## Troubleshooting

### Assets Not Loading

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan theme:clear

# Republish assets
php artisan theme:publish --force
```

### Theme Not Activating

```bash
# Check theme discovery
php artisan theme:list

# Validate theme structure
php artisan theme:check mytheme

# Rebuild cache
php artisan theme:cache
```

### Performance Issues

```bash
# Enable query logging
DB::enableQueryLog();

# Check for N+1 queries
php artisan telescope:prune

# Profile asset loading
php artisan debugbar:clear
```

## Rollback Strategy

### Quick Rollback

```bash
# Revert to previous theme
php artisan theme:activate previous-theme

# Clear caches
php artisan cache:clear
php artisan view:clear
```

### Full Rollback

```bash
# Revert code
git revert HEAD

# Rebuild assets
php artisan theme:build old-theme
php artisan theme:publish

# Clear caches
php artisan theme:cache
php artisan config:cache
```

## Monitoring

### Health Checks

```php
// routes/web.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'theme' => get_active_theme()?->slug,
        'version' => get_active_theme()?->version,
    ]);
});
```

### Error Tracking

Use Sentry or Bugsnag to track theme-related errors:

```php
if ($exception instanceof ThemeNotFoundException) {
    Sentry::captureException($exception);
}
```

## Best Practices

### 1. Always Build Before Deploy

Never deploy without building theme assets.

### 2. Use Asset Versioning

Vite handles this automatically via content hashing.

### 3. Test in Staging

Always test theme changes in a staging environment first.

### 4. Monitor Performance

Track theme asset load times and rendering performance.

### 5. Have a Rollback Plan

Always be able to quickly revert to the previous theme.

## Next Steps

- [API Reference](api.md)
- [Troubleshooting Guide](troubleshooting.md)
