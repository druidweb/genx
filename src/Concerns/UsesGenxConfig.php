<?php

declare(strict_types=1);

namespace Druid\Genx\Concerns;

use Illuminate\Support\Str;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputOption;
use Zen\Modulr\Support\ConfigStore;
use Zen\Modulr\Support\Registry;

trait UsesGenxConfig
{
  /**
   * Check if Modulr is enabled (config is true AND package is installed).
   */
  protected function modulrEnabled(): bool
  {
    return (bool) config('genx.modulr', false) && class_exists(Registry::class);
  }

  /**
   * Get the current module if --module option is set.
   */
  protected function module(): ?ConfigStore
  {
    if (! $this->modulrEnabled()) {
      return null;
    }

    $optionValue = $this->option('module');

    if (is_string($optionValue) && $optionValue !== '') {
      /** @var Registry $registry */
      $registry = $this->getLaravel()->make(Registry::class);

      if ($module = $registry->module($optionValue)) {
        return $module;
      }

      throw new InvalidOptionException(sprintf('The "%s" module does not exist.', $optionValue));
    }

    return null;
  }

  /**
   * Configure the command - adds --module option if Modulr is installed.
   */
  protected function configure(): void
  {
    parent::configure();

    if ($this->modulrEnabled()) {
      $this->getDefinition()->addOption(
        new InputOption(
          '--module',
          null,
          InputOption::VALUE_REQUIRED,
          'Run inside an application module'
        )
      );
    }
  }

  /**
   * Get the module-aware root namespace.
   * Call this from getDefaultNamespace() in commands that need module support.
   */
  protected function getModuleAwareNamespace(string $rootNamespace, string $namespace): string
  {
    $module = $this->module();

    if ($module === null) {
      return $namespace;
    }

    $firstNamespace = $module->namespaces->first();

    if (is_string($firstNamespace) && ! str_contains($rootNamespace, $firstNamespace)) {
      $find = rtrim($rootNamespace, '\\');
      $replace = rtrim($firstNamespace, '\\');
      $namespace = str_replace($find, $replace, $namespace);
    }

    return $namespace;
  }

  /**
   * Get the destination class path.
   *
   * @param  string  $name
   */
  protected function getPath($name): string // @pest-ignore-type
  {
    $module = $this->module();

    if ($module !== null) {
      // Strip the module namespace prefix from the name for path calculation
      $firstNamespace = $module->namespaces->first();
      if (is_string($firstNamespace)) {
        $name = Str::replaceFirst($firstNamespace, '', (string) $name);
      }
    }

    /** @var string $path */
    $path = parent::getPath($name);

    if ($module !== null) {
      $firstKey = $module->namespaces->keys()->first();

      // Set up our replacements as a [find -> replace] array
      $replacements = [
        $this->laravel->path() => is_string($firstKey) ? $firstKey : '',
        $this->laravel->basePath('tests/Tests') => $module->path('tests'),
        $this->laravel->databasePath() => $module->path('database'),
      ];

      // Normalize all our paths for compatibility's sake
      $normalize = fn (string $p): string => rtrim($p, '/').'/';

      $find = array_map($normalize, array_keys($replacements));
      $replace = array_map($normalize, array_values($replacements));

      $path = str_replace($find, $replace, $path);
    }

    return $path;
  }

  /**
   * Qualify a class name with module awareness.
   *
   * @param  string  $name
   */
  protected function qualifyClass($name): string // @pest-ignore-type
  {
    $name = ltrim((string) $name, '\\/');
    $module = $this->module();

    if ($module === null) {
      return parent::qualifyClass($name);
    }

    $firstNamespace = $module->namespaces->first();

    if (is_string($firstNamespace) && Str::startsWith($name, $firstNamespace)) {
      return $name;
    }

    return parent::qualifyClass($name);
  }

  /**
   * Qualify a model name for module context.
   */
  protected function qualifyModuleModel(string $model, string $qualifiedModel): string
  {
    $module = $this->module();

    if ($module === null) {
      return $qualifiedModel;
    }

    $model = str_replace('/', '\\', ltrim($model, '\\/'));

    if (Str::startsWith($model, $module->namespace())) {
      return $model;
    }

    return $module->qualify('Models\\'.$model);
  }

  /**
   * Pass the --module flag to subsequent commands.
   *
   * @param  string  $command
   * @param  array<string, mixed>  $arguments
   */
  public function call($command, array $arguments = []): int
  {
    if ($this->modulrEnabled() && $module = $this->option('module')) {
      $arguments['--module'] = $module;
    }

    return $this->runCommand($command, $arguments, $this->output);
  }

  /**
   * Resolve the fully-qualified path to the stub.
   */
  protected function resolveGenxStubPath(string $stub): string
  {
    // First check if stub exists in application's base path
    $customPath = $this->laravel->basePath(trim($stub, '/'));

    if (file_exists($customPath)) {
      return $customPath; // @codeCoverageIgnore
    }

    // Then check our package shared stubs
    $stubFile = Str::after($stub, '/stubs/');
    $packageStubPath = dirname(__DIR__, 2).'/stubs/shared/'.$stubFile;

    if (file_exists($packageStubPath)) {
      return $packageStubPath;
    }

    // Fall back - should not happen for our stubs
    return $customPath; // @codeCoverageIgnore
  }

  /**
   * Get a path from the genx config.
   */
  protected function getGenxPath(string $type): string
  {
    /** @var string $path */
    $path = config("genx.paths.{$type}", $this->getDefaultGenxPath($type));

    return $path;
  }

  /**
   * Get the default path for a generator type.
   */
  protected function getDefaultGenxPath(string $type): string
  {
    $defaults = [
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
      'job_middleware' => 'app/Jobs/Middleware',
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
      'view' => 'resources/views',
    ];

    return $defaults[$type] ?? 'app';
  }

  /**
   * Get the namespace for a generator type based on config.
   */
  protected function getConfiguredNamespace(string $type, string $rootNamespace): string
  {
    $path = $this->getGenxPath($type);

    // Handle paths that are exactly 'app' (no subdirectory)
    if ($path === 'app') {
      return $rootNamespace;
    }

    // Handle paths that don't start with 'app/'
    if (! Str::startsWith($path, 'app/')) {
      return $rootNamespace.'\\'.Str::of($path)->replace('/', '\\')->toString(); // @codeCoverageIgnore
    }

    $namespace = Str::after($path, 'app/');

    return $rootNamespace.'\\'.Str::replace('/', '\\', $namespace);
  }

  /**
   * Check if strict types are enabled in config.
   */
  protected function useStrictTypes(): bool
  {
    return (bool) config('genx.strict_types', true);
  }

  /**
   * Check if final classes are enabled in config.
   */
  protected function useFinalClasses(): bool
  {
    return (bool) config('genx.final_classes', true);
  }

  /**
   * Check if route discovery is enabled in config.
   */
  protected function useRouteDiscovery(): bool
  {
    return (bool) config('genx.route_discovery', false);
  }

  /**
   * Build the class content with genx options applied.
   */
  protected function applyGenxOptions(string $stub): string
  {
    // Replace {{ strictTypes }} placeholder
    $strictTypes = $this->useStrictTypes() ? "declare(strict_types=1);\n" : '';
    $stub = str_replace('{{ strictTypes }}', $strictTypes, $stub);

    // Replace {{ finalClass }} placeholder
    $finalClass = $this->useFinalClasses() ? 'final class' : 'class';

    return str_replace('{{ finalClass }}', $finalClass, $stub);
  }
}
