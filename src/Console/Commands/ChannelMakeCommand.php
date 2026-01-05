<?php

declare(strict_types=1);

namespace Druid\Genx\Console\Commands;

use Druid\Genx\Concerns\UsesGenxConfig;
use Illuminate\Foundation\Console\ChannelMakeCommand as BaseChannelMakeCommand;

class ChannelMakeCommand extends BaseChannelMakeCommand
{
  use UsesGenxConfig;

  protected $name = 'make:channel';

  /**
   * @param  string  $rootNamespace
   */
  protected function getDefaultNamespace($rootNamespace): string // @pest-ignore-type
  {
    $namespace = $this->getConfiguredNamespace('channel', $rootNamespace);

    return $this->getModuleAwareNamespace($rootNamespace, $namespace);
  }

  /**
   * Get the stub file for the generator.
   */
  protected function getStub(): string
  {
    return $this->resolveGenxStubPath('/stubs/channel.stub');
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
