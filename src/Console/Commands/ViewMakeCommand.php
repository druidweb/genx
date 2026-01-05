<?php

declare(strict_types=1);

namespace Druid\Genx\Console\Commands;

use Druid\Genx\Concerns\UsesGenxConfig;
use Illuminate\Foundation\Console\ViewMakeCommand as BaseViewMakeCommand;

class ViewMakeCommand extends BaseViewMakeCommand
{
  use UsesGenxConfig;

  protected $name = 'make:view';
}
