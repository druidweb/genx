<?php

declare(strict_types=1);

namespace Druid\Genx\Console\Commands;

use Druid\Genx\Concerns\UsesGenxConfig;
use Illuminate\Foundation\Console\ObserverMakeCommand as BaseObserverMakeCommand;

class ObserverMakeCommand extends BaseObserverMakeCommand
{
  use UsesGenxConfig;

  protected $name = 'make:observer';

  /**
   * @param  string  $rootNamespace
   */
  protected function getDefaultNamespace($rootNamespace): string // @pest-ignore-type
  {
    $namespace = $this->getConfiguredNamespace('observer', $rootNamespace);

    return $this->getModuleAwareNamespace($rootNamespace, $namespace);
  }

  /**
   * Qualify the given model class base name.
   *
   * @return string
   */
  protected function qualifyModel(string $model): string // @pest-ignore-type
  {
    $qualifiedModel = parent::qualifyModel($model);

    return $this->qualifyModuleModel($model, $qualifiedModel);
  }

  /**
   * @param  string  $stub
   */
  protected function resolveStubPath($stub): string // @pest-ignore-type
  {
    return $this->resolveGenxStubPath($stub);
  }

  /**
   * @param  string  $name
   */
  protected function buildClass($name): string // @pest-ignore-type
  {
    $stub = parent::buildClass($name);

    return $this->applyGenxOptions($stub);
  }
}
