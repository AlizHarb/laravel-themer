<?php

// Include root autoloader to share dependencies like Livewire
if (file_exists(__DIR__.'/../../../vendor/autoload.php')) {
    require_once __DIR__.'/../../../vendor/autoload.php';
}

require_once __DIR__.'/TestCase.php';

use AlizHarb\Themer\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);
