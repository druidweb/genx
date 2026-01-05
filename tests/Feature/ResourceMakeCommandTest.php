<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

beforeEach(function (): void {
  $this->resourcePath = app_path('Resources');
  $this->httpResourcePath = app_path('Http/Resources');

  // Clean up any generated files
  if (File::isDirectory($this->resourcePath)) {
    File::deleteDirectory($this->resourcePath);
  }
  if (File::isDirectory($this->httpResourcePath)) {
    File::deleteDirectory($this->httpResourcePath);
  }
});

afterEach(function (): void {
  if (File::isDirectory($this->resourcePath)) {
    File::deleteDirectory($this->resourcePath);
  }
  if (File::isDirectory($this->httpResourcePath)) {
    File::deleteDirectory($this->httpResourcePath);
  }
});

describe('ResourceMakeCommand', function (): void {
  it('generates resource in flat structure by default', function (): void {
    Config::set('genx.paths.resource', 'app/Resources');

    $this->artisan('make:resource', ['name' => 'TestResource'])
      ->assertSuccessful();

    expect(File::exists(app_path('Resources/TestResource.php')))->toBeTrue();
  });

  it('generates resource in Http structure when configured', function (): void {
    Config::set('genx.paths.resource', 'app/Http/Resources');

    $this->artisan('make:resource', ['name' => 'TestResource'])
      ->assertSuccessful();

    expect(File::exists(app_path('Http/Resources/TestResource.php')))->toBeTrue();
  });

  it('generates resource with strict types when enabled', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.paths.resource', 'app/Resources');

    $this->artisan('make:resource', ['name' => 'StrictResource'])
      ->assertSuccessful();

    $content = File::get(app_path('Resources/StrictResource.php'));

    expect($content)->toContain('declare(strict_types=1);');
  });

  it('generates resource without strict types when disabled', function (): void {
    Config::set('genx.strict_types', false);
    Config::set('genx.paths.resource', 'app/Resources');

    $this->artisan('make:resource', ['name' => 'NonStrictResource'])
      ->assertSuccessful();

    $content = File::get(app_path('Resources/NonStrictResource.php'));

    expect($content)->not->toContain('declare(strict_types=1);');
  });

  it('generates resource with final class when enabled', function (): void {
    Config::set('genx.final_classes', true);
    Config::set('genx.paths.resource', 'app/Resources');

    $this->artisan('make:resource', ['name' => 'FinalResource'])
      ->assertSuccessful();

    $content = File::get(app_path('Resources/FinalResource.php'));

    expect($content)->toContain('final class FinalResource');
  });

  it('generates resource without final class when disabled', function (): void {
    Config::set('genx.final_classes', false);
    Config::set('genx.paths.resource', 'app/Resources');

    $this->artisan('make:resource', ['name' => 'NonFinalResource'])
      ->assertSuccessful();

    $content = File::get(app_path('Resources/NonFinalResource.php'));

    expect($content)->toContain('class NonFinalResource');
    expect($content)->not->toContain('final class');
  });

  it('generates resource collection with final class when enabled', function (): void {
    Config::set('genx.final_classes', true);
    Config::set('genx.paths.resource', 'app/Resources');

    $this->artisan('make:resource', ['name' => 'FinalCollection', '--collection' => true])
      ->assertSuccessful();

    $content = File::get(app_path('Resources/FinalCollection.php'));

    expect($content)->toContain('final class FinalCollection');
  });
});
