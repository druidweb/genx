<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

beforeEach(function (): void {
  $this->requestPath = app_path('Requests');
  $this->httpRequestPath = app_path('Http/Requests');

  // Clean up any generated files
  if (File::isDirectory($this->requestPath)) {
    File::deleteDirectory($this->requestPath);
  }
  if (File::isDirectory($this->httpRequestPath)) {
    File::deleteDirectory($this->httpRequestPath);
  }
});

afterEach(function (): void {
  if (File::isDirectory($this->requestPath)) {
    File::deleteDirectory($this->requestPath);
  }
  if (File::isDirectory($this->httpRequestPath)) {
    File::deleteDirectory($this->httpRequestPath);
  }
});

describe('RequestMakeCommand', function (): void {
  it('generates request in flat structure by default', function (): void {
    Config::set('genx.paths.request', 'app/Requests');

    $this->artisan('make:request', ['name' => 'TestRequest'])
      ->assertSuccessful();

    expect(File::exists(app_path('Requests/TestRequest.php')))->toBeTrue();
  });

  it('generates request in Http structure when configured', function (): void {
    Config::set('genx.paths.request', 'app/Http/Requests');

    $this->artisan('make:request', ['name' => 'TestRequest'])
      ->assertSuccessful();

    expect(File::exists(app_path('Http/Requests/TestRequest.php')))->toBeTrue();
  });

  it('generates request with strict types when enabled', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.paths.request', 'app/Requests');

    $this->artisan('make:request', ['name' => 'StrictRequest'])
      ->assertSuccessful();

    $content = File::get(app_path('Requests/StrictRequest.php'));

    expect($content)->toContain('declare(strict_types=1);');
  });

  it('generates request without strict types when disabled', function (): void {
    Config::set('genx.strict_types', false);
    Config::set('genx.paths.request', 'app/Requests');

    $this->artisan('make:request', ['name' => 'NonStrictRequest'])
      ->assertSuccessful();

    $content = File::get(app_path('Requests/NonStrictRequest.php'));

    expect($content)->not->toContain('declare(strict_types=1);');
  });

  it('generates request with final class when enabled', function (): void {
    Config::set('genx.final_classes', true);
    Config::set('genx.paths.request', 'app/Requests');

    $this->artisan('make:request', ['name' => 'FinalRequest'])
      ->assertSuccessful();

    $content = File::get(app_path('Requests/FinalRequest.php'));

    expect($content)->toContain('final class FinalRequest');
  });

  it('generates request without final class when disabled', function (): void {
    Config::set('genx.final_classes', false);
    Config::set('genx.paths.request', 'app/Requests');

    $this->artisan('make:request', ['name' => 'NonFinalRequest'])
      ->assertSuccessful();

    $content = File::get(app_path('Requests/NonFinalRequest.php'));

    expect($content)->toContain('class NonFinalRequest');
    expect($content)->not->toContain('final class');
  });
});
