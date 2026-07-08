<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Tests\Feature;

it('ships laravel boost resources', function () {
    expect(file_exists(__DIR__.'/../../resources/boost/guidelines/core.blade.php'))->toBeTrue()
        ->and(file_exists(__DIR__.'/../../resources/boost/skills/laravel-themer-development/SKILL.md'))->toBeTrue();
});
