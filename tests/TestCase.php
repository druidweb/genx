<?php

declare(strict_types=1);

namespace Tests;

use Druid\Genx\GenxServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;
use Zen\Modulr\ModulrServiceProvider;

abstract class TestCase extends Orchestra
{
  protected function setUp(): void
  {
    // Ensure testbench has a composer.json before Laravel Application is created
    $composerPath = __DIR__.'/../vendor/orchestra/testbench-core/laravel/composer.json';

    if (! file_exists($composerPath)) {
      file_put_contents($composerPath, json_encode([
        'autoload' => [
          'psr-4' => [
            'App\\' => 'app/',
          ],
        ],
      ], JSON_THROW_ON_ERROR));
    }

    parent::setUp();
  }

  /**
   * @param  Application  $app
   * @return array<int, class-string>
   */
  protected function getPackageProviders($app): array
  {
    return [
      ModulrServiceProvider::class,
      GenxServiceProvider::class,
    ];
  }
}
