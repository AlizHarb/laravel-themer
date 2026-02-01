# Testing Themes

Learn how to test your Laravel Themer themes effectively.

## Feature Tests

### Testing Theme Activation

```php
<?php

use AlizHarb\Themer\ThemeManager;

test('theme can be activated', function () {
    $manager = app(ThemeManager::class);
    
    $manager->set('mytheme');
    
    expect($manager->getActiveTheme()->slug)->toBe('mytheme');
});

test('theme activation updates environment', function () {
    $this->artisan('theme:activate mytheme')
         ->assertSuccessful();
    
    expect(config('themer.active'))->toBe('mytheme');
});
```

### Testing View Resolution

```php
test('theme views are resolved correctly', function () {
    app(ThemeManager::class)->set('mytheme');
    
    expect(view()->exists('theme::welcome'))->toBeTrue();
    expect(view()->exists('welcome'))->toBeTrue(); // Also works without namespace
});

test('theme views override application views', function () {
    app(ThemeManager::class)->set('mytheme');
    
    $content = view('layouts.app')->render();
    
    expect($content)->toContain('mytheme'); // Verify theme-specific content
});
```

### Testing Theme Inheritance

```php
test('child theme inherits parent views', function () {
    $manager = app(ThemeManager::class);
    $manager->set('child-theme'); // Has parent: 'parent-theme'
    
    // View exists in parent but not child
    expect(view()->exists('theme::parent-only-view'))->toBeTrue();
});

test('child theme overrides parent views', function () {
    $manager = app(ThemeManager::class);
    $manager->set('child-theme');
    
    $content = view('theme::shared-view')->render();
    
    // Should render child's version, not parent's
    expect($content)->toContain('child-specific-content');
});
```

## Unit Tests

### Testing Helper Functions

```php
test('get_active_theme returns current theme', function () {
    app(ThemeManager::class)->set('mytheme');
    
    $theme = get_active_theme();
    
    expect($theme)->not->toBeNull();
    expect($theme->slug)->toBe('mytheme');
});

test('is_theme_active checks correctly', function () {
    app(ThemeManager::class)->set('mytheme');
    
    expect(is_theme_active('mytheme'))->toBeTrue();
    expect(is_theme_active('othertheme'))->toBeFalse();
});

test('theme_asset generates correct urls', function () {
    app(ThemeManager::class)->set('mytheme');
    
    $url = theme_asset('images/logo.png');
    
    expect($url)->toContain('/themes/mytheme/images/logo.png');
});
```

### Testing Theme Manager

```php
test('theme manager finds themes by name', function () {
    $manager = app(ThemeManager::class);
    
    $theme = $manager->find('mytheme');
    
    expect($theme)->not->toBeNull();
    expect($theme->name)->toBe('mytheme');
});

test('theme manager returns all themes', function () {
    $manager = app(ThemeManager::class);
    
    $themes = $manager->all();
    
    expect($themes)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    expect($themes->count())->toBeGreaterThan(0);
});
```

## Livewire Component Tests

### Testing Theme-Specific Components

```php
use Livewire\Livewire;

test('theme livewire component renders', function () {
    app(ThemeManager::class)->set('mytheme');
    
    Livewire::test('mytheme::pages.home')
        ->assertStatus(200)
        ->assertSee('Welcome');
});

test('theme component uses correct view', function () {
    app(ThemeManager::class)->set('mytheme');
    
    $component = Livewire::test('mytheme::components.button');
    
    expect($component->viewData('theme'))->toBe('mytheme');
});
```

## Asset Tests

### Testing Asset Publishing

```php
test('theme assets are published correctly', function () {
    $manager = app(ThemeManager::class);
    $theme = $manager->find('mytheme');
    
    $manager->publishAssets($theme);
    
    expect(File::exists(public_path('themes/mytheme/css/app.css')))->toBeTrue();
});

test('theme vite directive works', function () {
    app(ThemeManager::class)->set('mytheme');
    
    $html = Blade::render("@vite(['resources/assets/css/app.css'])");
    
    expect($html)->toContain('themes/mytheme');
});
```

## Command Tests

### Testing Artisan Commands

```php
test('theme make command creates structure', function () {
    $this->artisan('theme:make TestTheme')
         ->assertSuccessful();
    
    $path = config('themer.themes_path').'/testtheme';
    
    expect(File::exists($path.'/theme.json'))->toBeTrue();
    expect(File::exists($path.'/resources/views'))->toBeTrue();
    
    // Cleanup
    File::deleteDirectory($path);
});

test('theme list command shows themes', function () {
    $this->artisan('theme:list')
         ->expectsTable(['Name', 'Slug', 'Version'], [
             ['mytheme', 'mytheme', '1.0.0'],
         ])
         ->assertSuccessful();
});

test('theme check validates structure', function () {
    $this->artisan('theme:check mytheme')
         ->assertSuccessful()
         ->expectsOutput('Theme structure is valid');
});
```

## Browser Tests (Dusk)

### Testing Theme UI

```php
use Laravel\Dusk\Browser;

test('theme renders correctly in browser', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
                ->assertSee('Welcome')
                ->assertSourceHas('themes/mytheme/css/app.css');
    });
});

test('theme switching works', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs(User::factory()->create())
                ->visit('/settings')
                ->select('theme', 'dark-theme')
                ->press('Save')
                ->assertPathIs('/')
                ->assertSourceHas('themes/dark-theme');
    });
});
```

## Test Helpers

### Custom Assertions

```php
// tests/TestCase.php
protected function assertThemeActive(string $themeName): void
{
    $this->assertEquals(
        $themeName,
        get_active_theme()?->slug,
        "Expected theme '{$themeName}' to be active"
    );
}

protected function assertViewUsesTheme(string $view, string $themeName): void
{
    $content = view($view)->render();
    
    $this->assertStringContainsString(
        $themeName,
        $content,
        "View '{$view}' does not use theme '{$themeName}'"
    );
}
```

### Usage

```php
test('custom assertions work', function () {
    app(ThemeManager::class)->set('mytheme');
    
    $this->assertThemeActive('mytheme');
    $this->assertViewUsesTheme('welcome', 'mytheme');
});
```

## Mocking Themes

### Creating Test Themes

```php
// tests/Fixtures/TestTheme.php
class TestTheme
{
    public static function create(string $name = 'test-theme'): string
    {
        $path = config('themer.themes_path').'/'.$name;
        
        File::makeDirectory($path.'/resources/views', 0755, true);
        
        File::put($path.'/theme.json', json_encode([
            'name' => $name,
            'version' => '1.0.0',
        ]));
        
        File::put($path.'/resources/views/test.blade.php', '<div>Test View</div>');
        
        return $path;
    }
    
    public static function cleanup(string $name = 'test-theme'): void
    {
        $path = config('themer.themes_path').'/'.$name;
        File::deleteDirectory($path);
    }
}
```

### Usage in Tests

```php
test('test theme works', function () {
    $path = TestTheme::create('my-test-theme');
    
    app(ThemeManager::class)->scan(config('themer.themes_path'));
    app(ThemeManager::class)->set('my-test-theme');
    
    expect(view()->exists('theme::test'))->toBeTrue();
    
    TestTheme::cleanup('my-test-theme');
});
```

## Best Practices

### 1. Reset Theme State

Always reset theme state between tests:

```php
beforeEach(function () {
    app(ThemeManager::class)->reset();
});
```

### 2. Use Factories

Create theme factories for consistent test data:

```php
// database/factories/ThemeFactory.php
class ThemeFactory
{
    public static function make(array $attributes = []): Theme
    {
        return new Theme(
            name: $attributes['name'] ?? 'test-theme',
            slug: $attributes['slug'] ?? 'test-theme',
            path: $attributes['path'] ?? '/path/to/theme',
            // ... other attributes
        );
    }
}
```

### 3. Test Theme Discovery

Ensure theme discovery works correctly:

```php
test('theme discovery finds all themes', function () {
    $manager = app(ThemeManager::class);
    $manager->scan(config('themer.themes_path'));
    
    $themes = $manager->all();
    
    expect($themes->count())->toBeGreaterThan(0);
});
```

### 4. Test Error Handling

```php
test('activating non-existent theme throws exception', function () {
    app(ThemeManager::class)->set('non-existent-theme');
})->throws(\AlizHarb\Themer\Exceptions\ThemeNotFoundException::class);
```

## Next Steps

- [Production Deployment](deployment.md)
- [Advanced Features](advanced.md)
- [Helper Functions](helpers.md)
