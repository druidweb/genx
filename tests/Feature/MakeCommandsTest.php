<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

beforeEach(function (): void {
  // Clean up generated directories
  $dirs = [
    app_path('Casts'),
    app_path('Broadcasting'),
    app_path('Console/Commands'),
    app_path('View/Components'),
    app_path('Enums'),
    app_path('Events'),
    app_path('Exceptions'),
    app_path('Contracts'),
    app_path('Jobs'),
    app_path('Listeners'),
    app_path('Mail'),
    app_path('Models'),
    app_path('Notifications'),
    app_path('Observers'),
    app_path('Policies'),
    app_path('Providers'),
    app_path('Rules'),
    app_path('Traits'),
    base_path('database/factories'),
    base_path('database/seeders'),
    base_path('tests/Feature'),
    base_path('tests/Unit'),
    resource_path('views'),
  ];

  foreach ($dirs as $dir) {
    if (File::isDirectory($dir)) {
      File::deleteDirectory($dir);
    }
  }
});

afterEach(function (): void {
  $dirs = [
    app_path('Casts'),
    app_path('Broadcasting'),
    app_path('Console/Commands'),
    app_path('View/Components'),
    app_path('Enums'),
    app_path('Events'),
    app_path('Exceptions'),
    app_path('Contracts'),
    app_path('Jobs'),
    app_path('Listeners'),
    app_path('Mail'),
    app_path('Models'),
    app_path('Notifications'),
    app_path('Observers'),
    app_path('Policies'),
    app_path('Providers'),
    app_path('Rules'),
    app_path('Traits'),
    base_path('database/factories'),
    base_path('database/seeders'),
    base_path('tests/Feature'),
    base_path('tests/Unit'),
    resource_path('views'),
  ];

  foreach ($dirs as $dir) {
    if (File::isDirectory($dir)) {
      File::deleteDirectory($dir);
    }
  }
});

describe('CastMakeCommand', function (): void {
  it('generates cast with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:cast', ['name' => 'TestCast'])->assertSuccessful();

    $content = File::get(app_path('Casts/TestCast.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestCast');
  });
});

describe('ChannelMakeCommand', function (): void {
  it('generates channel with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:channel', ['name' => 'TestChannel'])->assertSuccessful();

    $content = File::get(app_path('Broadcasting/TestChannel.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestChannel');
  });
});

describe('ClassMakeCommand', function (): void {
  it('generates class with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:class', ['name' => 'TestClass'])->assertSuccessful();

    $content = File::get(app_path('TestClass.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestClass');
  });

  it('generates invokable class with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:class', ['name' => 'TestInvokableClass', '--invokable' => true])->assertSuccessful();

    $content = File::get(app_path('TestInvokableClass.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestInvokableClass');
    expect($content)->toContain('__invoke');
  });
});

describe('ComponentMakeCommand', function (): void {
  it('generates component with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:component', ['name' => 'TestComponent'])->assertSuccessful();

    $content = File::get(app_path('View/Components/TestComponent.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestComponent');
  });
});

describe('ConsoleMakeCommand', function (): void {
  it('generates command with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:command', ['name' => 'TestCommand'])->assertSuccessful();

    $content = File::get(app_path('Console/Commands/TestCommand.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestCommand');
  });
});

describe('EnumMakeCommand', function (): void {
  it('generates enum with strict types', function (): void {
    Config::set('genx.strict_types', true);

    $this->artisan('make:enum', ['name' => 'TestEnum'])->assertSuccessful();

    $content = File::get(app_path('Enums/TestEnum.php'));
    expect($content)->toContain('declare(strict_types=1);');
  });
});

describe('EventMakeCommand', function (): void {
  it('generates event with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:event', ['name' => 'TestEvent'])->assertSuccessful();

    $content = File::get(app_path('Events/TestEvent.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestEvent');
  });
});

describe('ExceptionMakeCommand', function (): void {
  it('generates exception with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:exception', ['name' => 'TestException'])->assertSuccessful();

    $content = File::get(app_path('Exceptions/TestException.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestException');
  });

  it('generates exception with render method', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:exception', ['name' => 'RenderException', '--render' => true])->assertSuccessful();

    $content = File::get(app_path('Exceptions/RenderException.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class RenderException');
    expect($content)->toContain('public function render');
  });

  it('generates exception with report method', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:exception', ['name' => 'ReportException', '--report' => true])->assertSuccessful();

    $content = File::get(app_path('Exceptions/ReportException.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class ReportException');
    expect($content)->toContain('public function report');
  });

  it('generates exception with render and report methods', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:exception', ['name' => 'RenderReportException', '--render' => true, '--report' => true])->assertSuccessful();

    $content = File::get(app_path('Exceptions/RenderReportException.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class RenderReportException');
    expect($content)->toContain('public function render');
    expect($content)->toContain('public function report');
  });
});

describe('FactoryMakeCommand', function (): void {
  it('generates factory with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    // Create a model first for the factory
    File::ensureDirectoryExists(app_path('Models'));
    File::put(app_path('Models/TestModel.php'), '<?php namespace App\Models; class TestModel {}');

    $this->artisan('make:factory', ['name' => 'TestModelFactory'])->assertSuccessful();

    $content = File::get(base_path('database/factories/TestModelFactory.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestModelFactory');
  });
});

describe('InterfaceMakeCommand', function (): void {
  it('generates interface with strict types', function (): void {
    Config::set('genx.strict_types', true);

    $this->artisan('make:interface', ['name' => 'TestInterface'])->assertSuccessful();

    $content = File::get(app_path('Contracts/TestInterface.php'));
    expect($content)->toContain('declare(strict_types=1);');
  });
});

describe('JobMakeCommand', function (): void {
  it('generates job with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:job', ['name' => 'TestJob'])->assertSuccessful();

    $content = File::get(app_path('Jobs/TestJob.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestJob');
  });
});

describe('JobMiddlewareMakeCommand', function (): void {
  it('generates job middleware with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:job-middleware', ['name' => 'TestJobMiddleware'])->assertSuccessful();

    $content = File::get(app_path('Jobs/Middleware/TestJobMiddleware.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestJobMiddleware');
  });
});

describe('ListenerMakeCommand', function (): void {
  it('generates listener with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:listener', ['name' => 'TestListener'])->assertSuccessful();

    $content = File::get(app_path('Listeners/TestListener.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestListener');
  });
});

describe('MailMakeCommand', function (): void {
  it('generates mail with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:mail', ['name' => 'TestMail'])->assertSuccessful();

    $content = File::get(app_path('Mail/TestMail.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestMail');
  });
});

describe('ModelMakeCommand', function (): void {
  it('generates model with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:model', ['name' => 'TestModel'])->assertSuccessful();

    $content = File::get(app_path('Models/TestModel.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestModel');
  });
});

describe('NotificationMakeCommand', function (): void {
  it('generates notification with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:notification', ['name' => 'TestNotification'])->assertSuccessful();

    $content = File::get(app_path('Notifications/TestNotification.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestNotification');
  });
});

describe('ObserverMakeCommand', function (): void {
  it('generates observer with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:observer', ['name' => 'TestObserver'])->assertSuccessful();

    $content = File::get(app_path('Observers/TestObserver.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestObserver');
  });

  it('qualifies the model when --model is provided', function (): void {
    File::ensureDirectoryExists(app_path('Models'));

    $this->artisan('make:observer', ['name' => 'TestObserver', '--model' => 'TestModel'])->assertSuccessful();

    $content = File::get(app_path('Observers/TestObserver.php'));
    expect($content)->toContain('use App\Models\TestModel;');
  });
});

describe('PolicyMakeCommand', function (): void {
  it('generates policy with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:policy', ['name' => 'TestPolicy'])->assertSuccessful();

    $content = File::get(app_path('Policies/TestPolicy.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestPolicy');
  });

  it('qualifies the model when --model is provided', function (): void {
    File::ensureDirectoryExists(app_path('Models'));

    $this->artisan('make:policy', ['name' => 'TestPolicy', '--model' => 'TestModel'])->assertSuccessful();

    $content = File::get(app_path('Policies/TestPolicy.php'));
    expect($content)->toContain('use App\Models\TestModel;');
  });
});

describe('ProviderMakeCommand', function (): void {
  it('generates provider with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:provider', ['name' => 'TestProvider'])->assertSuccessful();

    $content = File::get(app_path('Providers/TestProvider.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestProvider');
  });
});

describe('RuleMakeCommand', function (): void {
  it('generates rule with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:rule', ['name' => 'TestRule'])->assertSuccessful();

    $content = File::get(app_path('Rules/TestRule.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestRule');
  });

  it('generates implicit rule with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:rule', ['name' => 'TestImplicitRule', '--implicit' => true])->assertSuccessful();

    $content = File::get(app_path('Rules/TestImplicitRule.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestImplicitRule');
    expect($content)->toContain('$implicit = true');
  });
});

describe('ScopeMakeCommand', function (): void {
  it('generates scope with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:scope', ['name' => 'TestScope'])->assertSuccessful();

    $content = File::get(app_path('Models/Scopes/TestScope.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestScope');
  });
});

describe('SeederMakeCommand', function (): void {
  it('generates seeder with strict types and final class', function (): void {
    Config::set('genx.strict_types', true);
    Config::set('genx.final_classes', true);

    $this->artisan('make:seeder', ['name' => 'TestSeeder'])->assertSuccessful();

    $content = File::get(base_path('database/seeders/TestSeeder.php'));
    expect($content)->toContain('declare(strict_types=1);');
    expect($content)->toContain('final class TestSeeder');
  });
});

describe('TestMakeCommand', function (): void {
  it('generates test with strict types', function (): void {
    Config::set('genx.strict_types', true);

    $this->artisan('make:test', ['name' => 'TestTest'])->assertSuccessful();

    $content = File::get(base_path('tests/Feature/TestTest.php'));
    expect($content)->toContain('declare(strict_types=1);');
  });
});

describe('TraitMakeCommand', function (): void {
  it('generates trait with strict types', function (): void {
    Config::set('genx.strict_types', true);

    $this->artisan('make:trait', ['name' => 'TestTrait'])->assertSuccessful();

    $content = File::get(app_path('Traits/TestTrait.php'));
    expect($content)->toContain('declare(strict_types=1);');
  });
});

describe('ViewMakeCommand', function (): void {
  it('generates view file', function (): void {
    $this->artisan('make:view', ['name' => 'test-view'])->assertSuccessful();

    expect(File::exists(resource_path('views/test-view.blade.php')))->toBeTrue();
  });
});
