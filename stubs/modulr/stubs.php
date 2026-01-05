<?php

declare(strict_types=1);

/**
 * Modulr stubs configuration for use with genx.
 *
 * Add to config/modulr.php:
 * 'stubs' => require base_path('stubs/modulr/stubs.php'),
 */
return [
  'composer.json' => __DIR__.'/composer-stub-latest.json',
  'src/Providers/StubClassNamePrefixServiceProvider.php' => __DIR__.'/ServiceProvider.php',
];
