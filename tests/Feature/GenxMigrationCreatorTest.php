<?php

declare(strict_types=1);

use Druid\Genx\Database\GenxMigrationCreator;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;

beforeEach(function (): void {
  $this->migrationsPath = database_path('migrations');

  if (! File::isDirectory($this->migrationsPath)) {
    File::makeDirectory($this->migrationsPath, 0755, true);
  }
});

afterEach(function (): void {
  // Clean up any created migrations
  $files = File::glob($this->migrationsPath.'/*_test_migration*.php');
  foreach ($files as $file) {
    File::delete($file);
  }
});

describe('GenxMigrationCreator', function (): void {
  it('creates migration with strict types when enabled', function (): void {
    config(['genx.strict_types' => true]);

    $creator = new GenxMigrationCreator(
      resolve(Filesystem::class),
      base_path('stubs')
    );

    $name = 'test_migration_strict_'.uniqid();
    $path = $creator->create($name, $this->migrationsPath, null, false);

    expect(File::exists($path))->toBeTrue();

    $content = File::get($path);
    expect($content)->toContain('declare(strict_types=1);');

    File::delete($path);
  });

  it('creates migration without strict types when disabled', function (): void {
    config(['genx.strict_types' => false]);

    $creator = new GenxMigrationCreator(
      resolve(Filesystem::class),
      base_path('stubs')
    );

    $name = 'test_migration_no_strict_'.uniqid();
    $path = $creator->create($name, $this->migrationsPath, null, false);

    expect(File::exists($path))->toBeTrue();

    $content = File::get($path);
    expect($content)->not->toContain('declare(strict_types=1);');

    File::delete($path);
  });

  it('creates migration with table creation when --create is used', function (): void {
    config(['genx.strict_types' => true]);

    $creator = new GenxMigrationCreator(
      resolve(Filesystem::class),
      base_path('stubs')
    );

    $name = 'test_migration_create_users_'.uniqid();
    $path = $creator->create($name, $this->migrationsPath, 'users', true);

    expect(File::exists($path))->toBeTrue();

    $content = File::get($path);
    expect($content)->toContain('declare(strict_types=1);')
      ->and($content)->toContain("Schema::create('users'");

    File::delete($path);
  });

  it('creates migration with table update when --table is used', function (): void {
    config(['genx.strict_types' => true]);

    $creator = new GenxMigrationCreator(
      resolve(Filesystem::class),
      base_path('stubs')
    );

    $name = 'test_migration_update_posts_'.uniqid();
    $path = $creator->create($name, $this->migrationsPath, 'posts', false);

    expect(File::exists($path))->toBeTrue();

    $content = File::get($path);
    expect($content)->toContain('declare(strict_types=1);')
      ->and($content)->toContain("Schema::table('posts'");

    File::delete($path);
  });

  it('uses genx stub path', function (): void {
    $creator = new GenxMigrationCreator(
      resolve(Filesystem::class),
      base_path('stubs')
    );

    $stubPath = $creator->stubPath();

    expect($stubPath)->toContain('stubs/shared');
  });

  it('is bound in the container as MigrationCreator', function (): void {
    $creator = resolve(MigrationCreator::class);

    expect($creator)->toBeInstanceOf(GenxMigrationCreator::class);
  });
});
