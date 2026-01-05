<?php

declare(strict_types=1);

namespace Druid\Genx\Console\Commands;

use Druid\Genx\Concerns\UsesGenxConfig;
use Illuminate\Foundation\Console\ExceptionMakeCommand as BaseExceptionMakeCommand;

class ExceptionMakeCommand extends BaseExceptionMakeCommand
{
  use UsesGenxConfig;

  protected $name = 'make:exception';

  /**
   * @param  string  $rootNamespace
   */
  protected function getDefaultNamespace($rootNamespace): string // @pest-ignore-type
  {
    $namespace = $this->getConfiguredNamespace('exception', $rootNamespace);

    return $this->getModuleAwareNamespace($rootNamespace, $namespace);
  }

  /**
   * Get the stub file for the generator.
   */
  protected function getStub(): string
  {
    if ($this->option('render')) {
      $stub = $this->option('report')
        ? '/stubs/exception-render-report.stub'
        : '/stubs/exception-render.stub';
    } elseif ($this->option('report')) {
      $stub = '/stubs/exception-report.stub';
    } else {
      $stub = '/stubs/exception.stub';
    }

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
