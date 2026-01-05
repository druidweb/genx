<?php

declare(strict_types=1);

namespace Druid\Genx\Console\Commands;

use Druid\Genx\Concerns\UsesGenxConfig;
use Illuminate\Routing\Console\ControllerMakeCommand as BaseControllerMakeCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

use function Laravel\Prompts\multiselect;

class ControllerMakeCommand extends BaseControllerMakeCommand
{
  use UsesGenxConfig;

  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'make:controller';

  /**
   * The selected middleware from the prompt.
   */
  protected string $selectedMiddleware = '';

  /**
   * Resolve the fully-qualified path to the stub.
   *
   * @param  string  $stub
   */
  protected function resolveStubPath($stub): string // @pest-ignore-type
  {
    // First check if stub exists in application's base path
    if (file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))) {
      return $customPath; // @codeCoverageIgnore
    }

    // Then check our package stubs based on route discovery setting
    // $stub is like '/stubs/controller.plain.stub', we need to replace '/stubs/' with our stub dir
    $stubDir = $this->useRouteDiscovery() ? 'route-discovery' : 'standard';
    $stubFile = Str::after($stub, '/stubs/');
    $packageStubPath = dirname(__DIR__, 3).'/stubs/'.$stubDir.'/'.$stubFile;

    if (file_exists($packageStubPath)) {
      return $packageStubPath;
    }

    // Fall back to Laravel's default stubs
    return parent::resolveStubPath($stub); // @codeCoverageIgnore
  }

  /**
   * Get the default namespace for the class.
   *
   * @param  string  $rootNamespace
   */
  protected function getDefaultNamespace($rootNamespace): string // @pest-ignore-type
  {
    $namespace = $this->getConfiguredNamespace('controller', $rootNamespace);

    if ($this->isApiController()) {
      $namespace .= '\\Api'; // @codeCoverageIgnore
    }

    return $this->getModuleAwareNamespace($rootNamespace, $namespace);
  }

  /**
   * Qualify the given model class base name.
   *
   *
   * @codeCoverageIgnore
   */
  protected function qualifyModel(string $model): string
  {
    $qualifiedModel = parent::qualifyModel($model);

    return $this->qualifyModuleModel($model, $qualifiedModel);
  }

  /**
   * Get the controller namespace suffix based on config.
   */
  protected function getControllerNamespaceSuffix(): string
  {
    $path = $this->getGenxPath('controller');

    // Extract the suffix from the path (e.g., 'app/Http/Controllers' -> 'Http\Controllers')
    $suffix = Str::after($path, 'app/');

    return Str::replace('/', '\\', $suffix);
  }

  /**
   * Determine if this is an API controller.
   */
  protected function isApiController(): bool
  {
    if ($this->option('api')) {
      return true; // @codeCoverageIgnore
    }

    return Str::contains($this->getStub(), '.api.stub');
  }

  /**
   * Prompt for middleware after Laravel's controller type prompts.
   *
   * @codeCoverageIgnore
   */
  protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output): void
  {
    // Skip all prompts if called from modules:make (it handles prompts itself)
    if ($this->option('module') !== null) {
      return;
    }

    parent::afterPromptingForMissingArguments($input, $output);

    // Prompt for middleware if route discovery is enabled and not already passed via option
    $middlewareOption = $this->option('middleware');
    if ($this->useRouteDiscovery() && ! (is_string($middlewareOption) && $middlewareOption !== '')) {
      $this->selectedMiddleware = $this->collectMiddlewareSelection();
    }
  }

  /**
   * Check if we're running from modules:make (via --module option).
   *
   * @codeCoverageIgnore
   */
  protected function runningFromModuleMake(): bool
  {
    return $this->modulrEnabled() && $this->option('module') !== null;
  }

  /**
   * Build the class with the given name.
   *
   * @param  string  $name
   *
   * @codeCoverageIgnore
   */
  protected function buildClass($name): string // @pest-ignore-type
  {
    $stub = parent::buildClass($name);
    $stub = $this->applyGenxOptions($stub);
    $stub = $this->replaceRouteAttributes($stub, $name);

    if ($this->useRouteDiscovery()) {
      return $this->addRouteDiscoveryAttributes($stub, $name);
    }

    return $stub;
  }

  /**
   * Add route discovery attributes to the controller.
   */
  protected function addRouteDiscoveryAttributes(string $stub, string $name): string
  {
    $route = $this->getRouteFromName($name);
    $routeName = $this->getRouteNameFromName($name);

    // Add the import if not present
    if (! Str::contains($stub, 'use Spatie\RouteDiscovery\Attributes\Route;')) {
      $stub = (string) preg_replace(
        '/(namespace [^;]+;)/',
        "$1\n\nuse Spatie\\RouteDiscovery\\Attributes\\Route;",
        $stub
      );
    }

    // Add the Route attribute before the class declaration
    $attribute = "#[Route(uri: '{$route}', name: '{$routeName}')]";

    return (string) preg_replace(
      '/^(final\s+)?class\s+/m',
      "{$attribute}\n$1class ",
      $stub
    );
  }

  /**
   * Replace route attribute placeholders.
   */
  protected function replaceRouteAttributes(string $stub, string $name): string
  {
    // Only process route attributes if using route discovery
    if (! $this->useRouteDiscovery()) {
      return $stub;
    }

    $route = $this->getRouteFromName($name);
    $routeName = $this->getRouteNameFromName($name);
    $middleware = $this->promptForMiddleware();

    return str_replace(
      ['{{ route }}', '{{ routeName }}', '{{ middleware }}'],
      [$route, $routeName, $middleware],
      $stub
    );
  }

  /**
   * Get the controller namespace prefix to strip from the name.
   */
  protected function getControllerNamespacePrefix(): string
  {
    return app()->getNamespace().$this->getControllerNamespaceSuffix().'\\';
  }

  /**
   * Get the route URI from the controller name.
   *
   * Example: App\Controllers\People\DashboardController -> people/dashboard
   */
  protected function getRouteFromName(string $name): string
  {
    $prefix = $this->getControllerNamespacePrefix();
    $namespace = Str::after($name, $prefix);
    $namespace = Str::beforeLast($namespace, 'Controller');

    return Str::of($namespace)
      ->replace('\\', '/')
      ->lower()
      ->toString();
  }

  /**
   * Get the route name prefix from the controller name.
   *
   * Example: App\Controllers\People\DashboardController -> people.dashboard
   */
  protected function getRouteNameFromName(string $name): string
  {
    $prefix = $this->getControllerNamespacePrefix();
    $namespace = Str::after($name, $prefix);
    $namespace = Str::beforeLast($namespace, 'Controller');

    return Str::of($namespace)
      ->replace('\\', '.')
      ->lower()
      ->toString();
  }

  /**
   * Get the middleware selection (from option, collected during prompting, or prompt now).
   */
  protected function promptForMiddleware(): string
  {
    // If middleware was passed via --middleware option (from modulr event), use it
    // @codeCoverageIgnoreStart
    $middlewareOption = $this->option('middleware');
    if (is_string($middlewareOption) && $middlewareOption !== '') {
      return $middlewareOption;
    }
    // @codeCoverageIgnoreEnd

    // If middleware was already collected during afterPromptingForMissingArguments, use it
    if ($this->selectedMiddleware !== '') {
      return $this->selectedMiddleware; // @codeCoverageIgnore
    }

    // Otherwise collect it now (when name was provided directly, not prompted)
    return $this->selectedMiddleware = $this->collectMiddlewareSelection();
  }

  /**
   * Collect middleware selection from the user.
   */
  protected function collectMiddlewareSelection(): string
  {
    // Skip prompt when running from modules:make command
    // @codeCoverageIgnoreStart
    if ($this->runningFromModuleMake()) {
      return "'auth'";
    }
    // @codeCoverageIgnoreEnd

    $available = $this->getAvailableMiddleware();

    $selected = multiselect(
      label: 'Which middleware should be applied?',
      options: $available,
      default: ['auth'],
      hint: 'Use space to select, enter to confirm'
    );

    if ($selected === []) {
      return ''; // @codeCoverageIgnore
    }

    /** @var list<string> $selected */
    return collect($selected)
      ->map(fn (string $m): string => "'{$m}'")
      ->implode(', ');
  }

  /**
   * Get available middleware options.
   *
   * @return array<string, string>
   */
  protected function getAvailableMiddleware(): array
  {
    $middleware = [
      'auth' => 'auth - Require authentication',
      'auth.basic' => 'auth.basic - HTTP Basic authentication',
      'guest' => 'guest - Guests only (not authenticated)',
      'password.confirm' => 'password.confirm - Require password confirmation',
      'signed' => 'signed - Validate signed URLs',
      'throttle' => 'throttle - Rate limiting',
      'verified' => 'verified - Require verified email',
    ];

    // Add custom middleware from app/Middleware
    // @codeCoverageIgnoreStart
    $customMiddleware = $this->getCustomMiddleware();
    foreach ($customMiddleware as $alias => $description) {
      $middleware[$alias] = $description;
    }
    // @codeCoverageIgnoreEnd

    return $middleware;
  }

  /**
   * Get custom middleware from app/Middleware directory.
   *
   * @return array<string, string>
   *
   * @codeCoverageIgnore
   */
  protected function getCustomMiddleware(): array
  {
    $path = app_path('Middleware');

    if (! is_dir($path)) {
      return [];
    }

    $middleware = [];
    $finder = (new Finder)->files()->name('*.php')->in($path);

    foreach ($finder as $file) {
      $className = $file->getBasename('.php');

      // Skip Inertia/Appearance handlers - they're global, not route middleware
      if (in_array($className, ['HandleInertiaRequests', 'HandleAppearance'])) {
        continue;
      }

      $alias = Str::of($className)->kebab()->toString();
      $middleware[$alias] = "{$alias} - App\\Middleware\\{$className}";
    }

    return $middleware;
  }

  /**
   * Get the console command options.
   *
   * @return array<int, array<int, mixed>>
   */
  protected function getOptions(): array
  {
    /** @var array<int, array<int, mixed>> $options */
    $options = array_merge(parent::getOptions(), [
      ['middleware', null, InputOption::VALUE_OPTIONAL, 'The middleware to apply to the controller (for route discovery)'],
    ]);

    return $options;
  }
}
