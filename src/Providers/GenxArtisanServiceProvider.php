<?php

declare(strict_types=1);

namespace Druid\Genx\Providers;

use Druid\Genx\Console\Commands\CastMakeCommand;
use Druid\Genx\Console\Commands\ChannelMakeCommand;
use Druid\Genx\Console\Commands\ClassMakeCommand;
use Druid\Genx\Console\Commands\ComponentMakeCommand;
use Druid\Genx\Console\Commands\ConsoleMakeCommand;
use Druid\Genx\Console\Commands\ControllerMakeCommand;
use Druid\Genx\Console\Commands\EnumMakeCommand;
use Druid\Genx\Console\Commands\EventMakeCommand;
use Druid\Genx\Console\Commands\ExceptionMakeCommand;
use Druid\Genx\Console\Commands\FactoryMakeCommand;
use Druid\Genx\Console\Commands\InterfaceMakeCommand;
use Druid\Genx\Console\Commands\JobMakeCommand;
use Druid\Genx\Console\Commands\JobMiddlewareMakeCommand;
use Druid\Genx\Console\Commands\ListenerMakeCommand;
use Druid\Genx\Console\Commands\MailMakeCommand;
use Druid\Genx\Console\Commands\MiddlewareMakeCommand;
use Druid\Genx\Console\Commands\ModelMakeCommand;
use Druid\Genx\Console\Commands\NotificationMakeCommand;
use Druid\Genx\Console\Commands\ObserverMakeCommand;
use Druid\Genx\Console\Commands\PolicyMakeCommand;
use Druid\Genx\Console\Commands\ProviderMakeCommand;
use Druid\Genx\Console\Commands\RequestMakeCommand;
use Druid\Genx\Console\Commands\ResourceMakeCommand;
use Druid\Genx\Console\Commands\RuleMakeCommand;
use Druid\Genx\Console\Commands\ScopeMakeCommand;
use Druid\Genx\Console\Commands\SeederMakeCommand;
use Druid\Genx\Console\Commands\TestMakeCommand;
use Druid\Genx\Console\Commands\TraitMakeCommand;
use Druid\Genx\Console\Commands\ViewMakeCommand;
use Illuminate\Foundation\Providers\ArtisanServiceProvider;

class GenxArtisanServiceProvider extends ArtisanServiceProvider
{
  /**
   * Genx make command overrides.
   *
   * @var array<string, class-string>
   */
  protected array $genxCommands = [
    'CastMake' => CastMakeCommand::class,
    'ChannelMake' => ChannelMakeCommand::class,
    'ClassMake' => ClassMakeCommand::class,
    'ComponentMake' => ComponentMakeCommand::class,
    'ConsoleMake' => ConsoleMakeCommand::class,
    'ControllerMake' => ControllerMakeCommand::class,
    'EnumMake' => EnumMakeCommand::class,
    'EventMake' => EventMakeCommand::class,
    'ExceptionMake' => ExceptionMakeCommand::class,
    'FactoryMake' => FactoryMakeCommand::class,
    'InterfaceMake' => InterfaceMakeCommand::class,
    'JobMake' => JobMakeCommand::class,
    'JobMiddlewareMake' => JobMiddlewareMakeCommand::class,
    'ListenerMake' => ListenerMakeCommand::class,
    'MailMake' => MailMakeCommand::class,
    'MiddlewareMake' => MiddlewareMakeCommand::class,
    'ModelMake' => ModelMakeCommand::class,
    'NotificationMake' => NotificationMakeCommand::class,
    'ObserverMake' => ObserverMakeCommand::class,
    'PolicyMake' => PolicyMakeCommand::class,
    'ProviderMake' => ProviderMakeCommand::class,
    'RequestMake' => RequestMakeCommand::class,
    'ResourceMake' => ResourceMakeCommand::class,
    'RuleMake' => RuleMakeCommand::class,
    'ScopeMake' => ScopeMakeCommand::class,
    'SeederMake' => SeederMakeCommand::class,
    'TestMake' => TestMakeCommand::class,
    'TraitMake' => TraitMakeCommand::class,
    'ViewMake' => ViewMakeCommand::class,
  ];

  /**
   * Register the service provider.
   */
  public function register(): void
  {
    // Override parent's devCommands with Genx versions
    $this->devCommands = array_merge($this->devCommands, $this->genxCommands);

    parent::register();
  }
}
