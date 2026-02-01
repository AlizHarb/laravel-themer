# Livewire Integration

Laravel Themer provides first-class support for Livewire 4, with automatic component discovery, theme-specific namespaces, and deep inheritance.

## Creating Livewire Components

### Using the Theme Option

```bash
php artisan make:livewire home --class --theme=mytheme
```

Creates:
- `themes/mytheme/app/Livewire/Home.php`
- `themes/mytheme/resources/views/livewire/home.blade.php`

### Component Class

**`themes/mytheme/app/Livewire/Home.php`:**

```php
<?php

namespace Theme\MyTheme\Livewire;

use Livewire\Component;

class Home extends Component
{
    public string $title = 'Welcome';
    
    public function render()
    {
        return view('livewire.home');
    }
}
```

### Component View

**`themes/mytheme/resources/views/livewire/pages/home.blade.php`:**

```blade
<div>
    <h1>{{ $title }}</h1>
    <p>This is a theme-specific Livewire component.</p>
</div>
```

## Component Namespaces

Laravel Themer automatically registers theme-specific Livewire namespaces.

### Theme Namespace

Each theme gets its own namespace based on the theme slug:

```blade
{{-- Renders component from active theme --}}
<livewire:mytheme::home />
```

### Auto-Namespaces

Configured in `config/themer.php`:

```php
'auto_namespaces' => [
    'layouts' => 'resources/views/layouts',
    'pages' => 'resources/views/livewire/pages',
],
```

**Usage:**

```blade
{{-- Resolves to active theme's pages directory --}}
<livewire:pages::home />

{{-- Resolves to active theme's layouts directory --}}
<livewire:layouts::navigation />
```

## Component Discovery

### Automatic Registration

When a theme is activated, Laravel Themer automatically:

1. Scans `app/Livewire/` for class-based components
2. Scans `resources/views/livewire/` for view-based components (SFC/MFC)
3. Registers theme-specific namespaces
4. Configures auto-namespaces from config

### View-Based Components (Livewire 4)

Create single-file or multi-file components directly in views:

```
themes/mytheme/resources/views/livewire/
├── pages/
│   ├── ⚡home.blade.php        # Single-file component
│   └── about/
│       ├── ⚡about.blade.php   # Multi-file component
│       └── ⚡about.js
```

**Usage:**

```blade
<livewire:pages::home />
<livewire:pages::about />
```

## Component Inheritance

### Deep Inheritance Support

Laravel Themer supports infinite inheritance depth with automatic loop protection.

**Example Hierarchy:**

```
base-theme/
  └── app/Livewire/Components/Button.php
corporate-theme/
  └── app/Livewire/Components/Button.php  ← Overrides
corporate-dark/
  └── app/Livewire/Components/Button.php  ← Overrides again
```

### Resolution Order

When rendering `<livewire:button />`:

1. Active theme (`corporate-dark`)
2. Parent theme (`corporate-theme`)
3. Grandparent theme (`base-theme`)
4. Application default (`app/Livewire`)

### Fallback Mechanism

If a component doesn't exist in the active theme, Laravel Themer automatically searches parent themes:

```blade
{{-- Component exists in parent theme --}}
<livewire:components::card />
```

## Layouts

### Creating a Livewire Layout

```bash
php artisan livewire:layout --theme=mytheme
```

**`themes/mytheme/resources/views/layouts/app.blade.php`:**

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    
    @vite(['resources/assets/css/app.css', 'resources/assets/js/app.js'], 'themes/mytheme')
</head>
<body>
    {{ $slot }}
</body>
</html>
```

### Using the Layout

```php
public function render()
{
    return view('livewire.home')
        ->layout('layouts::app');
}
```

Or configure globally in `config/livewire.php`:

```php
'component_layout' => 'layouts::app',
```

## Full-Page Components

### Defining Routes

```php

Route::get('/', function () {
    return view('livewire.pages.home');
})->middleware('web');

// Or using Livewire routing
Route::get('/about', \Theme\MyTheme\Livewire\About::class);
```

### Page Component

```php
<?php

namespace Theme\MyTheme\Livewire\Pages;

use Livewire\Component;

class About extends Component
{
    public function render()
    {
        return view('livewire.about')
            ->layout('layouts::app')
            ->title('About Us');
    }
}
```

## Nested Components

### Parent Component

```blade
<div>
    <h1>Dashboard</h1>
    
    <livewire:components::stats-card 
        :title="'Total Users'" 
        :value="$userCount" 
    />
    
    <livewire:components::stats-card 
        :title="'Revenue'" 
        :value="$revenue" 
    />
</div>
```

### Child Component

```php
<?php

namespace Theme\MyTheme\Livewire\Components;

use Livewire\Component;

class StatsCard extends Component
{
    public string $title;
    public mixed $value;
    
    public function render()
    {
        return view('livewire.components.stats-card');
    }
}
```

## Dynamic Properties

### Using Wire:Model

```blade
<div>
    <input type="text" wire:model.live="search" placeholder="Search...">
    
    @foreach($results as $result)
        <div>{{ $result->name }}</div>
    @endforeach
</div>
```

```php
public string $search = '';

public function getResultsProperty()
{
    return User::where('name', 'like', "%{$this->search}%")->get();
}
```

## Events

### Dispatching Events

```php
public function save()
{
    // Save logic...
    
    $this->dispatch('user-saved', userId: $this->user->id);
}
```

### Listening to Events

```php
protected $listeners = ['user-saved' => 'refreshData'];

public function refreshData($userId)
{
    // Refresh component data
}
```

### Browser Events

```php
$this->dispatch('show-toast', message: 'Saved successfully!');
```

```blade
<div x-data @show-toast.window="alert($event.detail.message)">
    <!-- Component content -->
</div>
```

## Forms

### Form Component

```php
<?php

namespace Theme\MyTheme\Livewire\Forms;

use Livewire\Component;
use Livewire\Attributes\Validate;

class ContactForm extends Component
{
    #[Validate('required|min:3')]
    public string $name = '';
    
    #[Validate('required|email')]
    public string $email = '';
    
    #[Validate('required|min:10')]
    public string $message = '';
    
    public function submit()
    {
        $this->validate();
        
        // Send email logic...
        
        session()->flash('success', 'Message sent!');
        $this->reset();
    }
    
    public function render()
    {
        return view('livewire.forms.contact-form');
    }
}
```

### Form View

```blade
<form wire:submit="submit">
    <div>
        <label>Name</label>
        <input type="text" wire:model="name">
        @error('name') <span>{{ $message }}</span> @enderror
    </div>
    
    <div>
        <label>Email</label>
        <input type="email" wire:model="email">
        @error('email') <span>{{ $message }}</span> @enderror
    </div>
    
    <div>
        <label>Message</label>
        <textarea wire:model="message"></textarea>
        @error('message') <span>{{ $message }}</span> @enderror
    </div>
    
    <button type="submit">Send Message</button>
</form>
```

## File Uploads

```php
use Livewire\WithFileUploads;

class UploadForm extends Component
{
    use WithFileUploads;
    
    #[Validate('image|max:1024')]
    public $photo;
    
    public function save()
    {
        $this->validate();
        
        $path = $this->photo->store('photos', 'public');
        
        // Save to database...
    }
}
```

```blade
<form wire:submit="save">
    <input type="file" wire:model="photo">
    
    @if ($photo)
        <img src="{{ $photo->temporaryUrl() }}">
    @endif
    
    <button type="submit">Upload</button>
</form>
```

## Best Practices

### 1. Use Theme Namespaces

```blade
✅ <livewire:pages::home />
❌ <livewire:home />
```

### 2. Leverage Inheritance

Design reusable base components in parent themes.

### 3. Keep Components Focused

Each component should have a single responsibility.

### 4. Use Lazy Loading

```blade
<livewire:heavy-component lazy />
```

### 5. Optimize Queries

Use computed properties for expensive operations:

```php
public function getUsersProperty()
{
    return User::with('profile')->get();
}
```

## Next Steps

- [Asset Management](assets.md)
- [Helper Functions](helpers.md)
- [Advanced Features](advanced.md)
