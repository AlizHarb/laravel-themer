<?php

// Include root autoloader to share dependencies like Livewire
if (file_exists(__DIR__.'/../../../vendor/autoload.php')) {
    require_once __DIR__.'/../../../vendor/autoload.php';
}

require_once __DIR__.'/TestCase.php';

use AlizHarb\Themer\Tests\TestCase;
use Illuminate\Support\Facades\File;

uses(TestCase::class)->in(__DIR__);

function createThemeFixture(string $slug, array $manifest = []): string
{
    $path = base_path("themes/{$slug}");
    File::makeDirectory($path.'/resources/views', 0755, true);
    File::put($path.'/theme.json', json_encode(array_merge([
        'name' => str($slug)->headline()->toString(),
        'slug' => $slug,
        'asset_path' => "themes/{$slug}",
        'version' => '1.0.0',
        'author' => 'Test',
    ], $manifest), JSON_PRETTY_PRINT));

    return $path;
}
