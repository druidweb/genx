<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Zen\Modulr\Console\Commands\Make\MakeModule;
use Zen\Modulr\Support\Registry;

beforeEach(function (): void {
  $this->controllerPath = app_path('Controllers');
  $this->httpControllerPath = app_path('Http/Controllers');
  $this->modulesPath = base_path('modules');
  $this->modelsPath = app_path('Models');

  // Clean up any generated files
  if (File::isDirectory($this->controllerPath)) {
    File::deleteDirectory($this->controllerPath);
  }
  if (File::isDirectory($this->httpControllerPath)) {
    File::deleteDirectory($this->httpControllerPath);
  }
  if (File::isDirectory($this->modulesPath)) {
    File::deleteDirectory($this->modulesPath);
  }
  // Clean up models except User.php
  if (File::isDirectory($this->modelsPath)) {
    foreach (File::files($this->modelsPath) as $file) {
      if ($file->getFilename() !== 'User.php') {
        File::delete($file->getPathname());
      }
    }
  }
});

afterEach(function (): void {
  if (File::isDirectory($this->controllerPath)) {
    File::deleteDirectory($this->controllerPath);
  }
  if (File::isDirectory($this->httpControllerPath)) {
    File::deleteDirectory($this->httpControllerPath);
  }
  if (File::isDirectory($this->modulesPath)) {
    File::deleteDirectory($this->modulesPath);
  }
  // Clean up models except User.php
  if (File::isDirectory($this->modelsPath)) {
    foreach (File::files($this->modelsPath) as $file) {
      if ($file->getFilename() !== 'User.php') {
        File::delete($file->getPathname());
      }
    }
  }
});

describe('ControllerMakeCommand', function (): void {
  it('generates controller in flat structure by default', function (): void {
    Config::set('genx.paths.controller', 'app/Controllers');

    $this->artisan('make:controller', ['name' => 'TestController'])
      ->assertSuccessful();

    expect(File::exists(app_path('Controllers/TestController.php')))->toBeTrue();
  });

  it('generates controller in Http structure when configured', function (): void {
    Config::set('genx.paths.controller', 'app/Http/Controllers');

    $this->artisan('make:controller', ['name' => 'TestController'])
      ->assertSuccessful();

    expect(File::exists(app_path('Http/Controllers/TestController.php')))->toBeTrue();
  });

  it('generates controller with strict types when enabled', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.paths.controller', 'app/Controllers');

    $this->artisan('make:controller', ['name' => 'StrictController'])
      ->assertSuccessful();

    $content = File::get(app_path('Controllers/StrictController.php'));

    expect($content)->toContain('declare(strict_types=1);');
  });

  it('generates controller without strict types when disabled', function (): void {
    Config::set('genx.strict_types', false);
    Config::set('genx.paths.controller', 'app/Controllers');

    $this->artisan('make:controller', ['name' => 'NonStrictController'])
      ->assertSuccessful();

    $content = File::get(app_path('Controllers/NonStrictController.php'));

    expect($content)->not->toContain('declare(strict_types=1);');
  });

  it('generates controller with final class when enabled', function (): void {
    Config::set('genx.final_classes', true);
    Config::set('genx.paths.controller', 'app/Controllers');

    $this->artisan('make:controller', ['name' => 'FinalController'])
      ->assertSuccessful();

    $content = File::get(app_path('Controllers/FinalController.php'));

    expect($content)->toContain('final class FinalController');
  });

  it('generates controller without final class when disabled', function (): void {
    Config::set('genx.final_classes', false);
    Config::set('genx.paths.controller', 'app/Controllers');

    $this->artisan('make:controller', ['name' => 'NonFinalController'])
      ->assertSuccessful();

    $content = File::get(app_path('Controllers/NonFinalController.php'));

    expect($content)->toContain('class NonFinalController');
    expect($content)->not->toContain('final class');
  });

  it('generates controller with route discovery attributes when enabled', function (): void {
    Config::set('genx.route_discovery', true);
    Config::set('genx.paths.controller', 'app/Controllers');

    $this->artisan('make:controller', ['name' => 'RouteDiscoveryController'])
      ->expectsChoice('Which middleware should be applied?', ['auth'], [
        'auth' => 'auth - Require authentication',
        'auth.basic' => 'auth.basic - HTTP Basic authentication',
        'guest' => 'guest - Guests only (not authenticated)',
        'password.confirm' => 'password.confirm - Require password confirmation',
        'signed' => 'signed - Validate signed URLs',
        'throttle' => 'throttle - Rate limiting',
        'verified' => 'verified - Require verified email',
      ])
      ->assertSuccessful();

    $content = File::get(app_path('Controllers/RouteDiscoveryController.php'));

    expect($content)->toContain('#[Route(');
    expect($content)->toContain('use Spatie\RouteDiscovery\Attributes\Route;');
  });

  it('generates controller without route discovery when disabled', function (): void {
    Config::set('genx.route_discovery', false);
    Config::set('genx.paths.controller', 'app/Controllers');

    $this->artisan('make:controller', ['name' => 'NoRouteDiscoveryController'])
      ->assertSuccessful();

    $content = File::get(app_path('Controllers/NoRouteDiscoveryController.php'));

    expect($content)->not->toContain('#[Route(');
  });

  it('generates resource controller with 2-space indentation', function (): void {
    Config::set('genx.paths.controller', 'app/Controllers');

    $this->artisan('make:controller', ['name' => 'IndentController', '--resource' => true])
      ->assertSuccessful();

    $content = File::get(app_path('Controllers/IndentController.php'));

    // Should have 2-space indentation, not 4-space or tabs
    expect($content)->toContain("\n  /**");
    expect($content)->toContain("\n  public function");
    expect($content)->not->toContain("\n    ");
    expect($content)->not->toContain("\t");
  });

  it('adds --module option when modulr is enabled', function (): void {
    Config::set('genx.modulr', true);

    $this->artisan('make:controller', ['--help' => true])
      ->expectsOutputToContain('--module')
      ->assertSuccessful();
  });

  it('generates controller in module with genx stubs', function (): void {
    Config::set('genx.modulr', true);
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    // Create a module first
    $this->artisan(MakeModule::class, ['name' => 'test-module', '--accept-namespace' => true])
      ->assertSuccessful();

    // Reload the module registry
    app()->make(Registry::class)->reload();

    // Generate controller in the module
    $this->artisan('make:controller', ['name' => 'ModuleController', '--module' => 'test-module'])
      ->assertSuccessful();

    $modulePath = base_path('modules/test-module/src/Http/Controllers/ModuleController.php');
    expect(File::exists($modulePath))->toBeTrue();

    $content = File::get($modulePath);

    // Should have genx stubs applied
    expect($content)->toContain('declare(strict_types=1);')
      ->and($content)->toContain('final class ModuleController')
      ->and($content)->toContain('namespace Modules\TestModule\Http\Controllers;');
  });

  it('throws error for non-existent module', function (): void {
    Config::set('genx.modulr', true);

    $this->artisan('make:controller', ['name' => 'TestController', '--module' => 'non-existent'])
      ->assertFailed();
  })->throws(InvalidOptionException::class, 'The "non-existent" module does not exist.');

  it('passes --module flag to subsequent commands', function (): void {
    Config::set('genx.modulr', true);
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    // Create a module first
    $this->artisan(MakeModule::class, ['name' => 'test-module', '--accept-namespace' => true])
      ->assertSuccessful();

    // Reload the module registry
    app()->make(Registry::class)->reload();

    // Generate a model with factory in the module (this calls make:factory internally)
    $this->artisan('make:model', ['name' => 'ModuleModel', '--module' => 'test-module', '--factory' => true])
      ->assertSuccessful();

    $modelPath = base_path('modules/test-module/src/Models/ModuleModel.php');
    $factoryPath = base_path('modules/test-module/database/factories/ModuleModelFactory.php');

    expect(File::exists($modelPath))->toBeTrue()
      ->and(File::exists($factoryPath))->toBeTrue();

    $modelContent = File::get($modelPath);
    $factoryContent = File::get($factoryPath);

    // Both should have genx stubs applied
    expect($modelContent)->toContain('declare(strict_types=1);')
      ->and($factoryContent)->toContain('declare(strict_types=1);');
  });

  it('generates invokable controller in module', function (): void {
    Config::set('genx.modulr', true);
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    // Create a module first
    $this->artisan(MakeModule::class, ['name' => 'test-module', '--accept-namespace' => true])
      ->assertSuccessful();

    // Reload the module registry
    app()->make(Registry::class)->reload();

    // Generate an invokable controller in the module
    $this->artisan('make:controller', [
      'name' => 'InvokableController',
      '--module' => 'test-module',
      '--invokable' => true,
    ])->assertSuccessful();

    $controllerPath = base_path('modules/test-module/src/Http/Controllers/InvokableController.php');
    expect(File::exists($controllerPath))->toBeTrue();

    $content = File::get($controllerPath);

    // Should have genx stubs applied
    expect($content)->toContain('declare(strict_types=1);')
      ->and($content)->toContain('final class InvokableController')
      ->and($content)->toContain('namespace Modules\TestModule\Http\Controllers;')
      ->and($content)->toContain('public function __invoke(');
  });

  it('works without --module option when modulr is enabled', function (): void {
    Config::set('genx.modulr', true);
    Config::set('genx.strict_types', true);
    Config::set('genx.paths.controller', 'app/Controllers');

    // Generate controller without --module option
    $this->artisan('make:controller', ['name' => 'RegularController'])
      ->assertSuccessful();

    $controllerPath = app_path('Controllers/RegularController.php');
    expect(File::exists($controllerPath))->toBeTrue();

    $content = File::get($controllerPath);
    expect($content)->toContain('declare(strict_types=1);')
      ->and($content)->toContain('namespace App\Controllers;');
  });

  it('qualifies model without module option', function (): void {
    Config::set('genx.modulr', false);
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);
    Config::set('genx.paths.controller', 'app/Controllers');

    // Create a model first
    $this->artisan('make:model', ['name' => 'Product'])
      ->assertSuccessful();

    // Load the model class so it's available for class_exists check
    $modelPath = app_path('Models/Product.php');
    require_once $modelPath;

    // Generate a singleton controller with the model (no --module option)
    $this->artisan('make:controller', [
      'name' => 'ProductController',
      '--singleton' => true,
      '--model' => 'Product',
    ])->assertSuccessful();

    $controllerPath = app_path('Controllers/ProductController.php');
    expect(File::exists($controllerPath))->toBeTrue();

    $content = File::get($controllerPath);

    // Should reference the app's model namespace
    expect($content)->toContain('use App\Models\Product;')
      ->and($content)->toContain('declare(strict_types=1);')
      ->and($content)->toContain('final class ProductController');
  });

  it('qualifies model in module context', function (): void {
    Config::set('genx.modulr', true);
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    // Create a module first
    $this->artisan(MakeModule::class, ['name' => 'test-module', '--accept-namespace' => true])
      ->assertSuccessful();

    // Reload the module registry
    app()->make(Registry::class)->reload();

    // Create a model in the module first
    $this->artisan('make:model', ['name' => 'Article', '--module' => 'test-module'])
      ->assertSuccessful();

    // Load the model class so it's available for class_exists check
    $modelPath = base_path('modules/test-module/src/Models/Article.php');
    require_once $modelPath;

    // Generate a singleton controller with the model in the module
    // The model already exists and is loaded, so no prompt will be shown
    $this->artisan('make:controller', [
      'name' => 'ArticleController',
      '--module' => 'test-module',
      '--singleton' => true,
      '--model' => 'Article',
    ])->assertSuccessful();

    $controllerPath = base_path('modules/test-module/src/Http/Controllers/ArticleController.php');
    expect(File::exists($controllerPath))->toBeTrue();

    $content = File::get($controllerPath);

    // Should reference the module's model namespace
    expect($content)->toContain('use Modules\TestModule\Models\Article;')
      ->and($content)->toContain('declare(strict_types=1);')
      ->and($content)->toContain('final class ArticleController');
  });

  it('qualifies model with fully qualified namespace in module', function (): void {
    Config::set('genx.modulr', true);
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    // Create a module first
    $this->artisan(MakeModule::class, ['name' => 'test-module', '--accept-namespace' => true])
      ->assertSuccessful();

    // Reload the module registry
    app()->make(Registry::class)->reload();

    // Create a model in the module first
    $this->artisan('make:model', ['name' => 'Comment', '--module' => 'test-module'])
      ->assertSuccessful();

    // Load the model class so it's available for class_exists check
    $modelPath = base_path('modules/test-module/src/Models/Comment.php');
    require_once $modelPath;

    // Generate a singleton controller with the fully qualified model name
    // This tests the case where model already starts with module namespace
    $this->artisan('make:controller', [
      'name' => 'CommentController',
      '--module' => 'test-module',
      '--singleton' => true,
      '--model' => 'Modules\TestModule\Models\Comment',
    ])->assertSuccessful();

    $controllerPath = base_path('modules/test-module/src/Http/Controllers/CommentController.php');
    expect(File::exists($controllerPath))->toBeTrue();

    $content = File::get($controllerPath);

    // Should reference the module's model namespace
    expect($content)->toContain('use Modules\TestModule\Models\Comment;')
      ->and($content)->toContain('declare(strict_types=1);')
      ->and($content)->toContain('final class CommentController');
  });
});
