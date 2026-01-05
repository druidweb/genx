<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

beforeEach(function (): void {
  $this->middlewarePath = app_path('Middleware');
  $this->httpMiddlewarePath = app_path('Http/Middleware');

  // Clean up any generated files
  if (File::isDirectory($this->middlewarePath)) {
    File::deleteDirectory($this->middlewarePath);
  }
  if (File::isDirectory($this->httpMiddlewarePath)) {
    File::deleteDirectory($this->httpMiddlewarePath);
  }
});

afterEach(function (): void {
  if (File::isDirectory($this->middlewarePath)) {
    File::deleteDirectory($this->middlewarePath);
  }
  if (File::isDirectory($this->httpMiddlewarePath)) {
    File::deleteDirectory($this->httpMiddlewarePath);
  }
});

describe('MiddlewareMakeCommand', function (): void {
  it('generates middleware in flat structure by default', function (): void {
    Config::set('genx.paths.middleware', 'app/Middleware');

    $this->artisan('make:middleware', ['name' => 'TestMiddleware'])
      ->assertSuccessful();

    expect(File::exists(app_path('Middleware/TestMiddleware.php')))->toBeTrue();
  });

  it('generates middleware in Http structure when configured', function (): void {
    Config::set('genx.paths.middleware', 'app/Http/Middleware');

    $this->artisan('make:middleware', ['name' => 'TestMiddleware'])
      ->assertSuccessful();

    expect(File::exists(app_path('Http/Middleware/TestMiddleware.php')))->toBeTrue();
  });

  it('generates middleware with strict types when enabled', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.paths.middleware', 'app/Middleware');

    $this->artisan('make:middleware', ['name' => 'StrictMiddleware'])
      ->assertSuccessful();

    $content = File::get(app_path('Middleware/StrictMiddleware.php'));

    expect($content)->toContain('declare(strict_types=1);');
  });

  it('generates middleware without strict types when disabled', function (): void {
    Config::set('genx.strict_types', false);
    Config::set('genx.paths.middleware', 'app/Middleware');

    $this->artisan('make:middleware', ['name' => 'NonStrictMiddleware'])
      ->assertSuccessful();

    $content = File::get(app_path('Middleware/NonStrictMiddleware.php'));

    expect($content)->not->toContain('declare(strict_types=1);');
  });

  it('generates middleware with final class when enabled', function (): void {
    Config::set('genx.final_classes', true);
    Config::set('genx.paths.middleware', 'app/Middleware');

    $this->artisan('make:middleware', ['name' => 'FinalMiddleware'])
      ->assertSuccessful();

    $content = File::get(app_path('Middleware/FinalMiddleware.php'));

    expect($content)->toContain('final class FinalMiddleware');
  });

  it('generates middleware without final class when disabled', function (): void {
    Config::set('genx.final_classes', false);
    Config::set('genx.paths.middleware', 'app/Middleware');

    $this->artisan('make:middleware', ['name' => 'NonFinalMiddleware'])
      ->assertSuccessful();

    $content = File::get(app_path('Middleware/NonFinalMiddleware.php'));

    expect($content)->toContain('class NonFinalMiddleware');
    expect($content)->not->toContain('final class');
  });
});
