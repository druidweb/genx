<?php

declare(strict_types=1);

namespace Druid\Genx\Console\Commands;

use Druid\Genx\Concerns\UsesGenxConfig;
use Illuminate\Foundation\Console\ComponentMakeCommand as BaseComponentMakeCommand;

class ComponentMakeCommand extends BaseComponentMakeCommand
{
  use UsesGenxConfig;

  protected $name = 'make:component';

  /**
   * @param  string  $rootNamespace
   */
  protected function getDefaultNamespace($rootNamespace): string // @pest-ignore-type
  {
    $namespace = $this->getConfiguredNamespace('component', $rootNamespace);

    return $this->getModuleAwareNamespace($rootNamespace, $namespace);
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
