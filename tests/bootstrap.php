<?php

// Load Composer Autoloader
require_once __DIR__.'/../vendor/autoload.php';

// Robust Manual Autoloading for Package Testing
spl_autoload_register(function ($class) {
    // Autoload Package Source
    $prefix = 'AlizHarb\\Themer\\';
    $base_dir = __DIR__.'/../src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relative_class = substr($class, $len);
        $file = $base_dir.str_replace('\\', '/', $relative_class).'.php';

        if (file_exists($file)) {
            require_once $file;

            return;
        }
    }

    // Autoload Package Tests (e.g. TestCase)
    $test_prefix = 'AlizHarb\\Themer\\Tests\\';
    $test_base_dir = __DIR__.'/';

    $test_len = strlen($test_prefix);
    if (strncmp($test_prefix, $class, $test_len) === 0) {
        $relative_class = substr($class, $test_len);
        $file = $test_base_dir.str_replace('\\', '/', $relative_class).'.php';

        if (file_exists($file)) {
            require_once $file;

            return;
        }
    }
}, true, true);
