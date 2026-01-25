# Livewire Integration

One of the strongest features of Laravel Themer is its deep integration with Livewire 4.

## Automatic Discovery

Themer automatically registers Livewire components found in your active theme.

- **Class Path**: `themes/{theme}/app/Livewire`
- **View Path**: `themes/{theme}/resources/views/livewire`

## Layouts

Themes can define their own Livewire layouts.

1. Create `themes/{theme}/resources/views/layouts/app.blade.php`.
2. Use standard Livewire layout syntax in your components:

```php
public function render() 
{
    return view('theme::livewire.post-show')->layout('theme::layouts.app');
}
```

Or use normal view without theme namespace that will search in active theme livewire path:

```php
public function render() 
{
    return view('livewire.post-show');
}
```

## Inheritance

If you are using a child theme, you can inherit components from the parent. Themer's resolver will look for the component in the child theme first, and fall back to the parent theme if not found.
