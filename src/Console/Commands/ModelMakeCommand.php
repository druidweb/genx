<?php

declare(strict_types=1);

namespace Druid\Genx\Console\Commands;

use Druid\Genx\Concerns\UsesGenxConfig;
use Illuminate\Foundation\Console\ModelMakeCommand as BaseModelMakeCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModelMakeCommand extends BaseModelMakeCommand
{
  use UsesGenxConfig;

  protected $name = 'make:model';

  /**
   * @param  string  $rootNamespace
   */
  protected function getDefaultNamespace($rootNamespace): string // @pest-ignore-type
  {
    $namespace = $this->getConfiguredNamespace('model', $rootNamespace);

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

  /**
   * Skip interactive prompts when running in module context.
   * This prevents hangs when called via callSilently from MakeModule.
   *
   * @codeCoverageIgnore
   */
  protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output): void
  {
    // Skip prompts when --module is set (being called from modules:make)
    if ($this->modulrEnabled() && $this->option('module')) {
      return;
    }

    parent::afterPromptingForMissingArguments($input, $output);
  }
}
