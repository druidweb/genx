<?php

declare(strict_types=1);

namespace Druid\Genx;

use Druid\Genx\Console\InstallCommand;
use Druid\Genx\Database\GenxMigrationCreator;
use Druid\Genx\Listeners\ControllerPromptsListener;
use Druid\Genx\Providers\GenxArtisanServiceProvider;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class GenxServiceProvider extends ServiceProvider
{
  public function register(): void
  {
    // Register the Genx Artisan service provider to override make commands
    $this->app->register(GenxArtisanServiceProvider::class);

    // Override the MigrationCreator to use genx stubs and options
    $this->app->singleton(function (): MigrationCreator {
      /** @var Filesystem $files */
      $files = $this->app->make(Filesystem::class);

      return new GenxMigrationCreator($files, $this->app->basePath('stubs'));
    });
  }

  public function boot(): void
  {
    $this->registerEventListeners();

    if ($this->app->runningInConsole()) {
      $this->commands([
        InstallCommand::class,
      ]);
    }
  }

  /**
   * Register event listeners for modulr integration.
   *
   * @codeCoverageIgnore
   */
  protected function registerEventListeners(): void
  {
    // Only register if modulr is installed
    if (! class_exists(\Zen\Modulr\Events\ControllerPromptsCollecting::class)) {
      return;
    }

    Event::listen(
      \Zen\Modulr\Events\ControllerPromptsCollecting::class,
      ControllerPromptsListener::class
    );
  }
}
