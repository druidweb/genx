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
  'strict_types' => true,
  'final_classes' => true,

  /**
   * PACKAGE INTEGRATIONS
   *
   * Enable integrations with optional packages.
   *
   * route_discovery: When enabled, generated controllers include Spatie Route Discovery
   *                  `#[Route]` attributes for automatic route registration. Requires
   *                  the spatie/laravel-route-discovery package to be installed.
   *
   *                  @see https://github.com/spatie/laravel-route-discovery
   *
   * modulr: When enabled, generators are aware of zenphp/modulr's modular architecture.
   *         This allows generating files within specific modules when the --module
   *         option is provided to make commands.
   *         @see https://github.com/zenphp/modulr
   */
  'route_discovery' => false,
  'modulr' => false,

  /**
   * GENERATOR PATHS
   *
   * Define where each type of file should be generated. Paths are relative
   * to the application root. Use forward slashes for directory separators.
   */
  'paths' => [
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
  ],
];
