<?php

declare(strict_types=1);

use Druid\Genx\Console\InstallCommand;
use Illuminate\Support\Facades\File;

use function Pest\Laravel\artisan;

beforeEach(function (): void {
  $this->configPath = config_path('genx.php');
  $this->stubsPath = base_path('stubs');

  if (File::exists($this->configPath)) {
    File::delete($this->configPath);
  }

  if (File::isDirectory($this->stubsPath)) {
    File::deleteDirectory($this->stubsPath);
  }
});

afterEach(function (): void {
  if (File::exists($this->configPath)) {
    File::delete($this->configPath);
  }

  if (File::isDirectory($this->stubsPath)) {
    File::deleteDirectory($this->stubsPath);
  }
});

describe('InstallCommand', function (): void {
  it('registers the install command', function (): void {
    artisan('list')
      ->expectsOutputToContain('genx:install')
      ->assertSuccessful();
  });

  it('publishes config with default options', function (): void {
    artisan('genx:install')
      ->expectsConfirmation('Add declare(strict_types=1) to all generated files?', 'yes')
      ->expectsConfirmation('Use final class declarations by default?', 'yes')
      ->expectsConfirmation('Enable Spatie Route Discovery integration for controllers?', 'no')
      ->expectsConfirmation('Are you using zenphp/modulr for modular architecture?', 'no')
      ->expectsQuestion('Which directory structure do you use?', 'standard')
      ->expectsConfirmation('Would you like to customize any other generator paths?', 'no')
      ->expectsConfirmation('Publish this configuration?', 'yes')
      ->assertSuccessful();

    expect(File::exists($this->configPath))->toBeTrue();

    $config = include $this->configPath;

    expect($config['strict_types'])->toBeTrue()
      ->and($config['final_classes'])->toBeTrue()
      ->and($config['route_discovery'])->toBeFalse()
      ->and($config['modulr'])->toBeFalse()
      ->and($config['paths']['controller'])->toBe('app/Http/Controllers');
  });

  it('publishes config with flat structure', function (): void {
    artisan('genx:install')
      ->expectsConfirmation('Add declare(strict_types=1) to all generated files?', 'yes')
      ->expectsConfirmation('Use final class declarations by default?', 'yes')
      ->expectsConfirmation('Enable Spatie Route Discovery integration for controllers?', 'no')
      ->expectsConfirmation('Are you using zenphp/modulr for modular architecture?', 'no')
      ->expectsQuestion('Which directory structure do you use?', 'flat')
      ->expectsConfirmation('Would you like to customize any other generator paths?', 'no')
      ->expectsConfirmation('Publish this configuration?', 'yes')
      ->assertSuccessful();

    $config = include $this->configPath;

    expect($config['paths']['controller'])->toBe('app/Controllers')
      ->and($config['paths']['middleware'])->toBe('app/Middleware')
      ->and($config['paths']['request'])->toBe('app/Requests')
      ->and($config['paths']['resource'])->toBe('app/Resources');
  });

  it('publishes config with route discovery enabled when package is installed', function (): void {
    // spatie/laravel-route-discovery IS installed in require-dev
    artisan('genx:install')
      ->expectsConfirmation('Add declare(strict_types=1) to all generated files?', 'yes')
      ->expectsConfirmation('Use final class declarations by default?', 'yes')
      ->expectsConfirmation('Enable Spatie Route Discovery integration for controllers?', 'yes')
      ->expectsConfirmation('Are you using zenphp/modulr for modular architecture?', 'no')
      ->expectsQuestion('Which directory structure do you use?', 'standard')
      ->expectsConfirmation('Would you like to customize any other generator paths?', 'no')
      ->expectsConfirmation('Publish this configuration?', 'yes')
      ->assertSuccessful();

    $config = include $this->configPath;

    expect($config['route_discovery'])->toBeTrue();
  });

  it('publishes config with route discovery disabled when user declines', function (): void {
    artisan('genx:install')
      ->expectsConfirmation('Add declare(strict_types=1) to all generated files?', 'yes')
      ->expectsConfirmation('Use final class declarations by default?', 'yes')
      ->expectsConfirmation('Enable Spatie Route Discovery integration for controllers?', 'no')
      ->expectsConfirmation('Are you using zenphp/modulr for modular architecture?', 'no')
      ->expectsQuestion('Which directory structure do you use?', 'standard')
      ->expectsConfirmation('Would you like to customize any other generator paths?', 'no')
      ->expectsConfirmation('Publish this configuration?', 'yes')
      ->assertSuccessful();

    $config = include $this->configPath;

    expect($config['route_discovery'])->toBeFalse();
  });

  it('publishes config with modulr enabled when package is installed', function (): void {
    // zenphp/modulr IS installed in require-dev, so this should work
    artisan('genx:install')
      ->expectsConfirmation('Add declare(strict_types=1) to all generated files?', 'yes')
      ->expectsConfirmation('Use final class declarations by default?', 'yes')
      ->expectsConfirmation('Enable Spatie Route Discovery integration for controllers?', 'no')
      ->expectsConfirmation('Are you using zenphp/modulr for modular architecture?', 'yes')
      ->expectsQuestion('Which directory structure do you use?', 'standard')
      ->expectsConfirmation('Would you like to customize any other generator paths?', 'no')
      ->expectsConfirmation('Publish this configuration?', 'yes')
      ->assertSuccessful();

    $config = include $this->configPath;

    expect($config['modulr'])->toBeTrue();
  });

  it('publishes config with modulr disabled when user declines', function (): void {
    artisan('genx:install')
      ->expectsConfirmation('Add declare(strict_types=1) to all generated files?', 'yes')
      ->expectsConfirmation('Use final class declarations by default?', 'yes')
      ->expectsConfirmation('Enable Spatie Route Discovery integration for controllers?', 'no')
      ->expectsConfirmation('Are you using zenphp/modulr for modular architecture?', 'no')
      ->expectsQuestion('Which directory structure do you use?', 'standard')
      ->expectsConfirmation('Would you like to customize any other generator paths?', 'no')
      ->expectsConfirmation('Publish this configuration?', 'yes')
      ->assertSuccessful();

    $config = include $this->configPath;

    expect($config['modulr'])->toBeFalse();
  });

  it('cancels installation when user declines confirmation', function (): void {
    artisan('genx:install')
      ->expectsConfirmation('Add declare(strict_types=1) to all generated files?', 'yes')
      ->expectsConfirmation('Use final class declarations by default?', 'yes')
      ->expectsConfirmation('Enable Spatie Route Discovery integration for controllers?', 'no')
      ->expectsConfirmation('Are you using zenphp/modulr for modular architecture?', 'no')
      ->expectsQuestion('Which directory structure do you use?', 'standard')
      ->expectsConfirmation('Would you like to customize any other generator paths?', 'no')
      ->expectsConfirmation('Publish this configuration?', 'no')
      ->assertFailed();

    expect(File::exists($this->configPath))->toBeFalse();
  });

  it('publishes config with strict types disabled', function (): void {
    artisan('genx:install')
      ->expectsConfirmation('Add declare(strict_types=1) to all generated files?', 'no')
      ->expectsConfirmation('Use final class declarations by default?', 'yes')
      ->expectsConfirmation('Enable Spatie Route Discovery integration for controllers?', 'no')
      ->expectsConfirmation('Are you using zenphp/modulr for modular architecture?', 'no')
      ->expectsQuestion('Which directory structure do you use?', 'standard')
      ->expectsConfirmation('Would you like to customize any other generator paths?', 'no')
      ->expectsConfirmation('Publish this configuration?', 'yes')
      ->assertSuccessful();

    $config = include $this->configPath;

    expect($config['strict_types'])->toBeFalse();
  });

  it('publishes config with final classes disabled', function (): void {
    artisan('genx:install')
      ->expectsConfirmation('Add declare(strict_types=1) to all generated files?', 'yes')
      ->expectsConfirmation('Use final class declarations by default?', 'no')
      ->expectsConfirmation('Enable Spatie Route Discovery integration for controllers?', 'no')
      ->expectsConfirmation('Are you using zenphp/modulr for modular architecture?', 'no')
      ->expectsQuestion('Which directory structure do you use?', 'standard')
      ->expectsConfirmation('Would you like to customize any other generator paths?', 'no')
      ->expectsConfirmation('Publish this configuration?', 'yes')
      ->assertSuccessful();

    $config = include $this->configPath;

    expect($config['final_classes'])->toBeFalse();
  });

  it('publishes config with custom path overrides', function (): void {
    artisan('genx:install')
      ->expectsConfirmation('Add declare(strict_types=1) to all generated files?', 'yes')
      ->expectsConfirmation('Use final class declarations by default?', 'yes')
      ->expectsConfirmation('Enable Spatie Route Discovery integration for controllers?', 'no')
      ->expectsConfirmation('Are you using zenphp/modulr for modular architecture?', 'no')
      ->expectsQuestion('Which directory structure do you use?', 'standard')
      ->expectsConfirmation('Would you like to customize any other generator paths?', 'yes')
      ->expectsChoice('Select paths to customize:', ['model'], [
        'cast' => 'cast: app/Casts',
        'channel' => 'channel: app/Broadcasting',
        'class' => 'class: app',
        'command' => 'command: app/Console/Commands',
        'component' => 'component: app/View/Components',
        'controller' => 'controller: app/Http/Controllers',
        'enum' => 'enum: app/Enums',
        'event' => 'event: app/Events',
        'exception' => 'exception: app/Exceptions',
        'factory' => 'factory: database/factories',
        'interface' => 'interface: app/Contracts',
        'job' => 'job: app/Jobs',
        'listener' => 'listener: app/Listeners',
        'mail' => 'mail: app/Mail',
        'middleware' => 'middleware: app/Http/Middleware',
        'migration' => 'migration: database/migrations',
        'model' => 'model: app/Models',
        'notification' => 'notification: app/Notifications',
        'observer' => 'observer: app/Observers',
        'policy' => 'policy: app/Policies',
        'provider' => 'provider: app/Providers',
        'request' => 'request: app/Http/Requests',
        'resource' => 'resource: app/Http/Resources',
        'rule' => 'rule: app/Rules',
        'scope' => 'scope: app/Models/Scopes',
        'seeder' => 'seeder: database/seeders',
        'test' => 'test: tests/Feature',
        'test_unit' => 'test_unit: tests/Unit',
        'trait' => 'trait: app/Traits',
      ])
      ->expectsQuestion('Path for model:', 'app/Domain/Models')
      ->expectsConfirmation('Publish this configuration?', 'yes')
      ->assertSuccessful();

    $config = include $this->configPath;

    expect($config['paths']['model'])->toBe('app/Domain/Models');
  });

  it('shows warning when route discovery requested but package not installed', function (): void {
    // Register a test command that simulates route-discovery not installed
    $testCommand = new class extends InstallCommand
    {
      protected $signature = 'genx:install';

      protected function packageClassExists(string $package): bool
      {
        if ($package === 'spatie/laravel-route-discovery') {
          return false;
        }

        return parent::packageClassExists($package);
      }

      protected function packageInComposerJson(string $package): bool
      {
        if ($package === 'spatie/laravel-route-discovery') {
          return false;
        }

        return parent::packageInComposerJson($package);
      }
    };

    Illuminate\Support\Facades\Artisan::registerCommand($testCommand);

    artisan('genx:install')
      ->expectsConfirmation('Add declare(strict_types=1) to all generated files?', 'yes')
      ->expectsConfirmation('Use final class declarations by default?', 'yes')
      ->expectsConfirmation('Enable Spatie Route Discovery integration for controllers?', 'yes')
      ->expectsOutputToContain('spatie/laravel-route-discovery is not installed')
      ->expectsConfirmation('Are you using zenphp/modulr for modular architecture?', 'no')
      ->expectsQuestion('Which directory structure do you use?', 'standard')
      ->expectsConfirmation('Would you like to customize any other generator paths?', 'no')
      ->expectsConfirmation('Publish this configuration?', 'yes')
      ->assertSuccessful();

    $config = include $this->configPath;

    expect($config['route_discovery'])->toBeFalse();
  });

  it('shows warning when modulr requested but package not installed', function (): void {
    // Register a test command that simulates modulr not installed
    $testCommand = new class extends InstallCommand
    {
      protected $signature = 'genx:install';

      protected function packageClassExists(string $package): bool
      {
        if ($package === 'zenphp/modulr') {
          return false;
        }

        return parent::packageClassExists($package);
      }

      protected function packageInComposerJson(string $package): bool
      {
        if ($package === 'zenphp/modulr') {
          return false;
        }

        return parent::packageInComposerJson($package);
      }
    };

    Illuminate\Support\Facades\Artisan::registerCommand($testCommand);

    artisan('genx:install')
      ->expectsConfirmation('Add declare(strict_types=1) to all generated files?', 'yes')
      ->expectsConfirmation('Use final class declarations by default?', 'yes')
      ->expectsConfirmation('Enable Spatie Route Discovery integration for controllers?', 'no')
      ->expectsConfirmation('Are you using zenphp/modulr for modular architecture?', 'yes')
      ->expectsOutputToContain('zenphp/modulr is not installed')
      ->expectsQuestion('Which directory structure do you use?', 'standard')
      ->expectsConfirmation('Would you like to customize any other generator paths?', 'no')
      ->expectsConfirmation('Publish this configuration?', 'yes')
      ->assertSuccessful();

    $config = include $this->configPath;

    expect($config['modulr'])->toBeFalse();
  });

  it('checks composer.json when class does not exist', function (): void {
    // Add our packages to the testbench's composer.json
    $composerPath = base_path('composer.json');
    File::put($composerPath, json_encode([
      'autoload' => [
        'psr-4' => [
          'App\\' => 'app/',
        ],
      ],
      'require-dev' => [
        'spatie/laravel-route-discovery' => '^1.0',
        'zenphp/modulr' => '^1.0',
      ],
    ], JSON_THROW_ON_ERROR));

    // Test the packageInComposerJson fallback path
    $testCommand = new class extends InstallCommand
    {
      protected $signature = 'genx:install';

      // Simulate class not existing - will fall back to composer.json
      protected function packageClassExists(string $package): bool
      {
        return false;
      }
    };

    Illuminate\Support\Facades\Artisan::registerCommand($testCommand);

    // Both packages are in composer.json require-dev, so they should still be detected
    artisan('genx:install')
      ->expectsConfirmation('Add declare(strict_types=1) to all generated files?', 'yes')
      ->expectsConfirmation('Use final class declarations by default?', 'yes')
      ->expectsConfirmation('Enable Spatie Route Discovery integration for controllers?', 'yes')
      ->expectsConfirmation('Are you using zenphp/modulr for modular architecture?', 'yes')
      ->expectsQuestion('Which directory structure do you use?', 'standard')
      ->expectsConfirmation('Would you like to customize any other generator paths?', 'no')
      ->expectsConfirmation('Publish this configuration?', 'yes')
      ->assertSuccessful();

    $config = include $this->configPath;

    expect($config['route_discovery'])->toBeTrue()
      ->and($config['modulr'])->toBeTrue();

    // Restore composer.json to base state (without packages)
    File::put($composerPath, json_encode([
      'autoload' => [
        'psr-4' => [
          'App\\' => 'app/',
        ],
      ],
    ], JSON_THROW_ON_ERROR));
  });

  it('handles missing composer.json gracefully', function (): void {
    // Delete the composer.json to test the real code path
    $composerPath = base_path('composer.json');
    File::delete($composerPath);

    // Test the case where composer.json doesn't exist
    $testCommand = new class extends InstallCommand
    {
      protected $signature = 'genx:install';

      protected function packageClassExists(string $package): bool
      {
        return false;
      }

      // Let the real packageInComposerJson run - it will return false since file doesn't exist
    };

    Illuminate\Support\Facades\Artisan::registerCommand($testCommand);

    artisan('genx:install')
      ->expectsConfirmation('Add declare(strict_types=1) to all generated files?', 'yes')
      ->expectsConfirmation('Use final class declarations by default?', 'yes')
      ->expectsConfirmation('Enable Spatie Route Discovery integration for controllers?', 'yes')
      ->expectsOutputToContain('spatie/laravel-route-discovery is not installed')
      ->expectsConfirmation('Are you using zenphp/modulr for modular architecture?', 'yes')
      ->expectsOutputToContain('zenphp/modulr is not installed')
      ->expectsQuestion('Which directory structure do you use?', 'standard')
      ->expectsConfirmation('Would you like to customize any other generator paths?', 'no')
      ->expectsConfirmation('Publish this configuration?', 'yes')
      ->assertSuccessful();

    $config = include $this->configPath;

    expect($config['route_discovery'])->toBeFalse()
      ->and($config['modulr'])->toBeFalse();

    // Restore composer.json for other tests
    File::put($composerPath, json_encode([
      'autoload' => [
        'psr-4' => [
          'App\\' => 'app/',
        ],
      ],
    ], JSON_THROW_ON_ERROR));
  });

  it('publishes migration stubs with strict types when enabled', function (): void {
    artisan('genx:install')
      ->expectsConfirmation('Add declare(strict_types=1) to all generated files?', 'yes')
      ->expectsConfirmation('Use final class declarations by default?', 'yes')
      ->expectsConfirmation('Enable Spatie Route Discovery integration for controllers?', 'no')
      ->expectsConfirmation('Are you using zenphp/modulr for modular architecture?', 'no')
      ->expectsQuestion('Which directory structure do you use?', 'standard')
      ->expectsConfirmation('Would you like to customize any other generator paths?', 'no')
      ->expectsConfirmation('Publish this configuration?', 'yes')
      ->assertSuccessful();

    expect(File::exists($this->stubsPath.'/migration.stub'))->toBeTrue()
      ->and(File::exists($this->stubsPath.'/migration.create.stub'))->toBeTrue()
      ->and(File::exists($this->stubsPath.'/migration.update.stub'))->toBeTrue();

    $content = File::get($this->stubsPath.'/migration.stub');
    expect($content)->toContain('declare(strict_types=1);')
      ->and($content)->not->toContain('{{ strictTypes }}');
  });

  it('publishes migration stubs without strict types when disabled', function (): void {
    artisan('genx:install')
      ->expectsConfirmation('Add declare(strict_types=1) to all generated files?', 'no')
      ->expectsConfirmation('Use final class declarations by default?', 'yes')
      ->expectsConfirmation('Enable Spatie Route Discovery integration for controllers?', 'no')
      ->expectsConfirmation('Are you using zenphp/modulr for modular architecture?', 'no')
      ->expectsQuestion('Which directory structure do you use?', 'standard')
      ->expectsConfirmation('Would you like to customize any other generator paths?', 'no')
      ->expectsConfirmation('Publish this configuration?', 'yes')
      ->assertSuccessful();

    $content = File::get($this->stubsPath.'/migration.stub');
    expect($content)->not->toContain('declare(strict_types=1);')
      ->and($content)->not->toContain('{{ strictTypes }}');
  });

  it('publishes modulr stubs when modulr is enabled', function (): void {
    artisan('genx:install')
      ->expectsConfirmation('Add declare(strict_types=1) to all generated files?', 'yes')
      ->expectsConfirmation('Use final class declarations by default?', 'yes')
      ->expectsConfirmation('Enable Spatie Route Discovery integration for controllers?', 'no')
      ->expectsConfirmation('Are you using zenphp/modulr for modular architecture?', 'yes')
      ->expectsQuestion('Which directory structure do you use?', 'standard')
      ->expectsConfirmation('Would you like to customize any other generator paths?', 'no')
      ->expectsConfirmation('Publish this configuration?', 'yes')
      ->assertSuccessful();

    expect(File::exists($this->stubsPath.'/modulr/ServiceProvider.php'))->toBeTrue()
      ->and(File::exists($this->stubsPath.'/modulr/composer-stub-latest.json'))->toBeTrue()
      ->and(File::exists($this->stubsPath.'/modulr/stubs.php'))->toBeTrue();

    $content = File::get($this->stubsPath.'/modulr/ServiceProvider.php');
    expect($content)->toContain('declare(strict_types=1);')
      ->and($content)->toContain('final class')
      ->and($content)->not->toContain('{{ strictTypes }}')
      ->and($content)->not->toContain('{{ finalClass }}');
  });
});
