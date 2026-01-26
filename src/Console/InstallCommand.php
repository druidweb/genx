<?php

declare(strict_types=1);

namespace Druid\Genx\Console;

use Illuminate\Console\Command;
use Spatie\RouteDiscovery\RouteDiscoveryServiceProvider;
use Zen\Modulr\ModulrServiceProvider;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;
use function Laravel\Prompts\select;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class InstallCommand extends Command
{
  protected $signature = 'genx:install';

  protected $description = 'Install and configure the Genx generator package';

  /** @var array{strict_types: bool, final_classes: bool, route_discovery: bool, modulr: bool, paths: array<string, string>} */
  private array $config = [
    'strict_types' => true,
    'final_classes' => true,
    'route_discovery' => false,
    'modulr' => false,
    'paths' => [],
  ];

  public function handle(): int
  {
    info('Welcome to Genx - Laravel Generator Configuration');
    note('This installer will help you configure how your generated files are formatted and organized.');

    $this->promptCodeStyle();
    $this->promptIntegrations();
    $this->promptStructure();
    $this->promptCustomPaths();
    $this->displaySummary();

    if (! $this->confirmInstall()) {
      warning('Installation cancelled.');

      return self::FAILURE;
    }

    $this->publishConfig();

    info('Genx has been configured successfully!');

    return self::SUCCESS;
  }

  private function promptCodeStyle(): void
  {
    note('Code Style Options');

    $this->config['strict_types'] = confirm(
      label: 'Add declare(strict_types=1) to all generated files?',
      default: true,
    );

    $this->config['final_classes'] = confirm(
      label: 'Use final class declarations by default?',
      default: true,
    );
  }

  private function promptIntegrations(): void
  {
    note('Package Integrations');

    // Route Discovery
    if ($this->isPackageInstalled('spatie/laravel-route-discovery')) {
      $this->config['route_discovery'] = confirm(
        label: 'Enable Spatie Route Discovery integration for controllers?',
        default: false,
        hint: 'Adds #[Route] attributes to generated controllers',
      );
    } else {
      $wantsRouteDiscovery = confirm(
        label: 'Enable Spatie Route Discovery integration for controllers?',
        default: false,
        hint: 'Adds #[Route] attributes to generated controllers',
      );

      if ($wantsRouteDiscovery) {
        warning('spatie/laravel-route-discovery is not installed. Setting route_discovery to false.');
        warning('Install it first: composer require spatie/laravel-route-discovery');
      }

      $this->config['route_discovery'] = false;
    }

    // Modulr
    if ($this->isPackageInstalled('zenphp/modulr')) {
      $this->config['modulr'] = confirm(
        label: 'Are you using zenphp/modulr for modular architecture?',
        default: false,
      );
    } else {
      $wantsModulr = confirm(
        label: 'Are you using zenphp/modulr for modular architecture?',
        default: false,
      );

      if ($wantsModulr) {
        warning('zenphp/modulr is not installed. Setting modulr to false.');
        warning('Install it first: composer require zenphp/modulr');
      }

      $this->config['modulr'] = false;
    }
  }

  protected function isPackageInstalled(string $package): bool
  {
    // Check if the package's main class exists (works even if not in app's composer.json)
    if ($this->packageClassExists($package)) {
      return true;
    }

    // Fallback: check composer.json
    return $this->packageInComposerJson($package);
  }

  protected function packageClassExists(string $package): bool
  {
    /** @var array<string, string> $classMap */
    $classMap = [
      'spatie/laravel-route-discovery' => RouteDiscoveryServiceProvider::class,
      'zenphp/modulr' => ModulrServiceProvider::class,
    ];

    return isset($classMap[$package]) && class_exists($classMap[$package]);
  }

  protected function packageInComposerJson(string $package): bool
  {
    $composerPath = $this->laravel->basePath('composer.json');

    if (! file_exists($composerPath)) {
      return false;
    }

    $contents = file_get_contents($composerPath);

    if ($contents === false) {
      return false; // @codeCoverageIgnore
    }

    /** @var array{require?: array<string, string>, require-dev?: array<string, string>}|null $composer */
    $composer = json_decode($contents, true);

    if ($composer === null) {
      return false; // @codeCoverageIgnore
    }

    return isset($composer['require'][$package]) || isset($composer['require-dev'][$package]);
  }

  private function promptStructure(): void
  {
    note('Application Structure');

    $structure = select(
      label: 'Which directory structure do you use?',
      options: [
        'standard' => 'Standard - app/Http/Controllers, app/Http/Middleware, etc.',
        'flat' => 'Flat - app/Controllers, app/Middleware, etc.',
      ],
      default: 'standard',
    );

    $this->config['paths'] = $this->getDefaultPaths();

    if ($structure === 'flat') {
      $this->config['paths']['controller'] = 'app/Controllers';
      $this->config['paths']['middleware'] = 'app/Middleware';
      $this->config['paths']['request'] = 'app/Requests';
      $this->config['paths']['resource'] = 'app/Resources';
    }
  }

  private function promptCustomPaths(): void
  {
    if (! confirm('Would you like to customize any other generator paths?', false)) {
      return;
    }

    $pathsToCustomize = multiselect(
      label: 'Select paths to customize:',
      options: array_combine(
        array_keys($this->config['paths']),
        array_map(
          fn (string $key, string $value): string => "{$key}: {$value}",
          array_keys($this->config['paths']),
          array_values($this->config['paths']),
        ),
      ),
    );

    foreach ($pathsToCustomize as $key) {
      $stringKey = (string) $key;
      $this->config['paths'][$stringKey] = text(
        label: "Path for {$stringKey}:",
        default: $this->config['paths'][$stringKey] ?? '',
        required: true,
      );
    }
  }

  private function displaySummary(): void
  {
    note('Configuration Summary');

    table(
      ['Option', 'Value'],
      [
        ['strict_types', $this->config['strict_types'] ? 'Yes' : 'No'],
        ['final_classes', $this->config['final_classes'] ? 'Yes' : 'No'],
        ['route_discovery', $this->config['route_discovery'] ? 'Yes' : 'No'],
        ['modulr', $this->config['modulr'] ? 'Yes' : 'No'],
      ],
    );

    note('Generator Paths');

    /** @var array<int, array{string, string}> $pathRows */
    $pathRows = array_map(
      fn (string $key, string $value): array => [$key, $value],
      array_keys($this->config['paths']),
      array_values($this->config['paths']),
    );

    table(['Generator', 'Path'], $pathRows);
  }

  private function confirmInstall(): bool
  {
    return confirm(
      label: 'Publish this configuration?',
      default: true,
    );
  }

  private function publishConfig(): void
  {
    $configPath = $this->laravel->configPath('genx.php');
    $content = $this->generateConfigContent();

    file_put_contents($configPath, $content);

    $this->publishStubs();
  }

  private function publishStubs(): void
  {
    $stubsPath = $this->laravel->basePath('stubs');

    if (! is_dir($stubsPath)) {
      mkdir($stubsPath, 0755, true);
    }

    // Publish all shared stubs
    $this->publishSharedStubs($stubsPath);

    // Publish controller stubs (standard or route-discovery)
    if ($this->config['route_discovery']) {
      $this->publishRouteDiscoveryStubs($stubsPath);
    } else {
      $this->publishStandardStubs($stubsPath);
    }

    // Publish modulr stubs if modulr is enabled
    if ($this->config['modulr']) {
      $this->publishModulrStubs($stubsPath);
    }
  }

  private function publishSharedStubs(string $stubsPath): void
  {
    $sourceDir = dirname(__DIR__, 2).'/stubs/shared';

    // @codeCoverageIgnoreStart
    if (! is_dir($sourceDir)) {
      return;
    }

    $files = scandir($sourceDir);

    if ($files === false) {
      return;
    }
    // @codeCoverageIgnoreEnd

    foreach ($files as $file) {
      if ($file === '.') {
        continue;
      }
      if ($file === '..') {
        continue;
      }
      $source = $sourceDir.'/'.$file;
      $destination = $stubsPath.'/'.$file;

      if (is_file($source)) {
        $content = (string) file_get_contents($source);

        // Process genx placeholders based on config
        $strictTypes = $this->config['strict_types'] ? "declare(strict_types=1);\n" : '';
        $content = str_replace('{{ strictTypes }}', $strictTypes, $content);

        $finalClass = $this->config['final_classes'] ? 'final class' : 'class';
        $content = str_replace('{{ finalClass }}', $finalClass, $content);

        file_put_contents($destination, $content);
      }
    }

    note('Shared stubs published to stubs/');
  }

  private function publishStandardStubs(string $stubsPath): void
  {
    $sourceDir = dirname(__DIR__, 2).'/stubs/standard';

    // @codeCoverageIgnoreStart
    if (! is_dir($sourceDir)) {
      return;
    }

    $files = scandir($sourceDir);

    if ($files === false) {
      return;
    }
    // @codeCoverageIgnoreEnd

    foreach ($files as $file) {
      if ($file === '.') {
        continue;
      }
      if ($file === '..') {
        continue;
      }
      $source = $sourceDir.'/'.$file;
      $destination = $stubsPath.'/'.$file;

      if (is_file($source)) {
        $content = (string) file_get_contents($source);

        // Process genx placeholders based on config
        $strictTypes = $this->config['strict_types'] ? "declare(strict_types=1);\n" : '';
        $content = str_replace('{{ strictTypes }}', $strictTypes, $content);

        $finalClass = $this->config['final_classes'] ? 'final class' : 'class';
        $content = str_replace('{{ finalClass }}', $finalClass, $content);

        file_put_contents($destination, $content);
      }
    }

    note('Standard controller stubs published to stubs/');
  }

  private function publishRouteDiscoveryStubs(string $stubsPath): void
  {
    $sourceDir = dirname(__DIR__, 2).'/stubs/route-discovery';

    // @codeCoverageIgnoreStart
    if (! is_dir($sourceDir)) {
      return;
    }

    $files = scandir($sourceDir);

    if ($files === false) {
      return;
    }
    // @codeCoverageIgnoreEnd

    foreach ($files as $file) {
      if ($file === '.') {
        continue;
      }
      if ($file === '..') {
        continue;
      }
      $source = $sourceDir.'/'.$file;
      $destination = $stubsPath.'/'.$file;

      if (is_file($source)) {
        $content = (string) file_get_contents($source);

        // Process genx placeholders based on config
        $strictTypes = $this->config['strict_types'] ? "declare(strict_types=1);\n" : '';
        $content = str_replace('{{ strictTypes }}', $strictTypes, $content);

        $finalClass = $this->config['final_classes'] ? 'final class' : 'class';
        $content = str_replace('{{ finalClass }}', $finalClass, $content);

        file_put_contents($destination, $content);
      }
    }

    note('Route Discovery controller stubs published to stubs/');
  }

  private function publishModulrStubs(string $stubsPath): void
  {
    $modulrStubsPath = $stubsPath.'/modulr';

    if (! is_dir($modulrStubsPath)) {
      mkdir($modulrStubsPath, 0755, true);
    }

    $sourceDir = dirname(__DIR__, 2).'/stubs/modulr';

    // Copy static files (no placeholder processing needed)
    $staticFiles = [
      'composer-stub-latest.json',
      'stubs.php',
    ];

    foreach ($staticFiles as $file) {
      $source = $sourceDir.'/'.$file;
      $destination = $modulrStubsPath.'/'.$file;

      if (file_exists($source)) {
        copy($source, $destination);
      }
    }

    // Process PHP stubs with genx placeholders
    $phpStubs = [
      'ServiceProvider.php',
    ];

    foreach ($phpStubs as $stub) {
      $source = $sourceDir.'/'.$stub;
      $destination = $modulrStubsPath.'/'.$stub;

      if (file_exists($source)) {
        $content = (string) file_get_contents($source);

        // Process genx placeholders based on config
        $strictTypes = $this->config['strict_types'] ? "declare(strict_types=1);\n" : '';
        $content = str_replace('{{ strictTypes }}', $strictTypes, $content);

        $finalClass = $this->config['final_classes'] ? 'final class' : 'class';
        $content = str_replace('{{ finalClass }}', $finalClass, $content);

        file_put_contents($destination, $content);
      }
    }

    note('Modulr stubs published to stubs/modulr/');
    note('To use these stubs, add the following to config/modulr.php:');
    note("'stubs' => require base_path('stubs/modulr/stubs.php'),");
  }

  private function generateConfigContent(): string
  {
    $strict = $this->config['strict_types'] ? 'true' : 'false';
    $final = $this->config['final_classes'] ? 'true' : 'false';
    $routeDiscovery = $this->config['route_discovery'] ? 'true' : 'false';
    $modulr = $this->config['modulr'] ? 'true' : 'false';

    $paths = $this->formatPathsArray($this->config['paths']);

    return <<<PHP
<?php

declare(strict_types=1);

return [

  /**
   * CODE STYLE OPTIONS
   *
   * These options control how generated PHP files are formatted.
   *
   * strict_types: When enabled, all generated files include `declare(strict_types=1)`
   *               at the top. This enforces strict type checking for function arguments
   *               and return values, catching type errors at runtime.
   *
   * final_classes: When enabled, all generated classes use the `final` keyword,
   *                preventing inheritance. This encourages composition over inheritance
   *                and makes code easier to reason about.
   */
  'strict_types' => {$strict},
  'final_classes' => {$final},

  /**
   * PACKAGE INTEGRATIONS
   *
   * Enable integrations with optional packages.
   *
   * route_discovery: When enabled, generated controllers include Spatie Route Discovery
   *                  `#[Route]` attributes for automatic route registration. Requires
   *                  the spatie/laravel-route-discovery package to be installed.
   *                  @see https://github.com/spatie/laravel-route-discovery
   *
   * modulr: When enabled, generators are aware of zenphp/modulr's modular architecture.
   *         This allows generating files within specific modules when the --module
   *         option is provided to make commands.
   *         @see https://github.com/zenphp/modulr
   */
  'route_discovery' => {$routeDiscovery},
  'modulr' => {$modulr},

  /**
   * GENERATOR PATHS
   *
   * Define where each type of file should be generated. Paths are relative
   * to the application root. Use forward slashes for directory separators.
   */
  'paths' => [
{$paths}
  ],
];

PHP;
  }

  /**
   * @param  array<string, string>  $paths
   */
  private function formatPathsArray(array $paths): string
  {
    $lines = [];

    foreach ($paths as $key => $value) {
      $lines[] = "    '{$key}' => '{$value}',";
    }

    return implode("\n", $lines);
  }

  /**
   * @return array<string, string>
   */
  private function getDefaultPaths(): array
  {
    return [
      'cast' => 'app/Casts',
      'channel' => 'app/Broadcasting',
      'class' => 'app',
      'command' => 'app/Console/Commands',
      'component' => 'app/View/Components',
      'controller' => 'app/Http/Controllers',
      'enum' => 'app/Enums',
      'event' => 'app/Events',
      'exception' => 'app/Exceptions',
      'factory' => 'database/factories',
      'interface' => 'app/Contracts',
      'job' => 'app/Jobs',
      'listener' => 'app/Listeners',
      'mail' => 'app/Mail',
      'middleware' => 'app/Http/Middleware',
      'migration' => 'database/migrations',
      'model' => 'app/Models',
      'notification' => 'app/Notifications',
      'observer' => 'app/Observers',
      'policy' => 'app/Policies',
      'provider' => 'app/Providers',
      'request' => 'app/Http/Requests',
      'resource' => 'app/Http/Resources',
      'rule' => 'app/Rules',
      'scope' => 'app/Models/Scopes',
      'seeder' => 'database/seeders',
      'test' => 'tests/Feature',
      'test_unit' => 'tests/Unit',
      'trait' => 'app/Traits',
    ];
  }
}
