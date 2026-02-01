# Advanced Features

Explore advanced theming techniques and patterns for complex applications.

## Theme Events

Laravel Themer dispatches events during theme lifecycle operations.

### Available Events

```php
use AlizHarb\Themer\Events\ThemeActivated;
use AlizHarb\Themer\Events\ThemeDeactivated;
use AlizHarb\Themer\Events\ThemeRegistered;
```

### Listening to Events

**In a Service Provider:**

```php
use Illuminate\Support\Facades\Event;
use AlizHarb\Themer\Events\ThemeActivated;

public function boot()
{
    Event::listen(ThemeActivated::class, function ($event) {
        \Log::info('Theme activated:', [
            'theme' => $event->theme->name,
            'version' => $event->theme->version,
        ]);
        
        // Clear caches
        \Artisan::call('cache:clear');
        \Artisan::call('view:clear');
    });
}
```

### Event Properties

```php
$event->theme; // Theme instance
```

## Custom Theme Service Providers

Create advanced theme logic with service providers.

### Generating a Provider

```bash
php artisan theme:make MyTheme --provider
```

### Example Provider

```php
<?php

namespace Theme\MyTheme;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

class ThemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register theme-specific services
        $this->app->singleton('mytheme.settings', function () {
            return [
                'primary_color' => '#6366f1',
                'font_family' => 'Inter',
            ];
        });
    }

    public function boot(): void
    {
        // Register custom Blade directives
        Blade::directive('theme_button', function ($expression) {
            return "<?php echo view('theme::components.button', $expression); ?>";
        });
        
        // Share data with all views
        View::share('themeSettings', app('mytheme.settings'));
        
        // Register middleware
        $this->app['router']->pushMiddlewareToGroup(
            'web',
            \Theme\MyTheme\Http\Middleware\ThemeMiddleware::class
        );
    }
}
```

## Theme-Specific Middleware

Create middleware that only runs when a theme is active.

### Creating Middleware

```php
<?php

namespace Theme\MyTheme\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ThemeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!is_theme_active('mytheme')) {
            return $next($request);
        }
        
        // Theme-specific logic
        view()->share('darkMode', $request->cookie('dark_mode', false));
        
        return $next($request);
    }
}
```

## Dynamic Theme Switching

Allow users to switch themes at runtime.

### Controller Method

```php
public function switchTheme(Request $request, string $theme)
{
    $manager = app('themer');
    
    if (!$manager->find($theme)) {
        abort(404, 'Theme not found');
    }
    
    // Store in session
    session(['active_theme' => $theme]);
    
    // Or store in user preferences
    $request->user()->update(['theme' => $theme]);
    
    return redirect()->back();
}
```

### Middleware for User Themes

```php
public function handle(Request $request, Closure $next)
{
    if ($user = $request->user()) {
        $theme = $user->theme ?? config('themer.active');
        
        app('themer')->setActiveTheme($theme);
    }
    
    return $next($request);
}
```

## Multi-Tenancy Support

Different themes for different tenants.

### Tenant-Specific Themes

```php
// In a service provider
public function boot()
{
    if ($tenant = tenant()) {
        $theme = $tenant->theme ?? 'default';
        app('themer')->setActiveTheme($theme);
    }
}
```

### Tenant Theme Resolver

```php
class TenantThemeResolver
{
    public function resolve(): string
    {
        $tenant = tenant();
        
        return match($tenant->plan) {
            'enterprise' => 'premium-theme',
            'pro' => 'professional-theme',
            default => 'basic-theme',
        };
    }
}
```

## Theme Variants

Create theme variants for different contexts.

### Dark Mode Variant

```json
{
  "name": "corporate-dark",
  "parent": "corporate",
  "tags": ["dark-mode", "variant"]
}
```

### Seasonal Variants

```php
public function getSeasonalTheme(): string
{
    $month = now()->month;
    
    return match(true) {
        $month === 12 => 'holiday-theme',
        $month >= 6 && $month <= 8 => 'summer-theme',
        default => 'default-theme',
    };
}
```

## Performance Optimization

### Theme Caching

```bash
# Cache theme discovery
php artisan theme:cache
```

This creates `bootstrap/cache/themes.php` with all discovered themes.

### Conditional Asset Loading

```blade
@if(get_active_theme()->slug === 'premium')
    @vite(['resources/assets/css/premium.css'], 'themes/premium')
@endif
```

### Lazy Load Components

```blade
<livewire:heavy-component lazy />
```

## Testing Themes

### Feature Tests

```php
use Tests\TestCase;

class ThemeTest extends TestCase
{
    public function test_theme_activation()
    {
        $this->artisan('theme:activate mytheme')
             ->assertSuccessful();
        
        $this->assertEquals('mytheme', config('themer.active'));
    }
    
    public function test_theme_views_resolve()
    {
        app('themer')->setActiveTheme('mytheme');
        
        $this->assertTrue(view()->exists('theme::welcome'));
    }
}
```

### Unit Tests

```php
public function test_theme_asset_helper()
{
    app('themer')->setActiveTheme('mytheme');
    
    $asset = theme_asset('logo.png');
    
    $this->assertEquals('/themes/mytheme/logo.png', $asset);
}
```

## Custom Theme Loaders

Extend theme discovery for custom sources.

### Database Theme Loader

```php
class DatabaseThemeLoader
{
    public function load(): Collection
    {
        return DB::table('themes')
            ->where('active', true)
            ->get()
            ->map(function ($row) {
                return new Theme(
                    name: $row->name,
                    slug: $row->slug,
                    path: storage_path("themes/{$row->slug}"),
                    version: $row->version,
                );
            });
    }
}
```

### Remote Theme Loader

```php
class RemoteThemeLoader
{
    public function load(): Collection
    {
        $response = Http::get('https://themes.example.com/api/themes');
        
        return collect($response->json())->map(function ($data) {
            // Download and extract theme
            $this->downloadTheme($data['url'], $data['slug']);
            
            return new Theme(
                name: $data['name'],
                slug: $data['slug'],
                path: base_path("themes/{$data['slug']}"),
            );
        });
    }
}
```

## Theme Marketplace

Build a theme marketplace for your application.

### Theme Installation

```php
public function install(string $themeSlug)
{
    // Download theme package
    $package = Http::get("https://marketplace.example.com/themes/{$themeSlug}/download");
    
    // Extract to themes directory
    $zip = new ZipArchive;
    $zip->open(storage_path("themes/{$themeSlug}.zip"));
    $zip->extractTo(base_path("themes/{$themeSlug}"));
    $zip->close();
    
    // Run theme:check
    Artisan::call('theme:check', ['theme' => $themeSlug]);
    
    // Install dependencies
    Artisan::call('theme:npm', ['theme' => $themeSlug, 'command' => 'install']);
}
```

## Best Practices

### 1. Use Events for Side Effects

Don't modify theme activation logic directly - use events.

### 2. Keep Themes Portable

Avoid hardcoded paths and environment-specific logic.

### 3. Version Your Themes

Use semantic versioning in `theme.json`.

### 4. Document Theme Dependencies

List required packages in theme README.

### 5. Test Theme Inheritance

Ensure child themes properly override parent resources.

## Next Steps

- [Production Deployment](deployment.md)
- [API Reference](api.md)
