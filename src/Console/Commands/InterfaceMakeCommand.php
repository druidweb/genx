<?php

declare(strict_types=1);

namespace Druid\Genx\Console\Commands;

use Druid\Genx\Concerns\UsesGenxConfig;
use Illuminate\Foundation\Console\InterfaceMakeCommand as BaseInterfaceMakeCommand;

class InterfaceMakeCommand extends BaseInterfaceMakeCommand
{
  use UsesGenxConfig;

  protected $name = 'make:interface';

  /**
   * @param  string  $rootNamespace
   */
  protected function getDefaultNamespace($rootNamespace): string // @pest-ignore-type
  {
    $namespace = $this->getConfiguredNamespace('interface', $rootNamespace);

    return $this->getModuleAwareNamespace($rootNamespace, $namespace);
  }

  /**
   * Get the stub file for the generator.
   */
  protected function getStub(): string
  {
    return $this->resolveGenxStubPath('/stubs/interface.stub');
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
