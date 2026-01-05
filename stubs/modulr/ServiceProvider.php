<?php

{{ strictTypes }}
namespace StubModuleNamespace\StubClassNamePrefix\Providers;

use Illuminate\Support\ServiceProvider;

{{ finalClass }} StubClassNamePrefixServiceProvider extends ServiceProvider
{
  public function register(): void {}

  public function boot(): void {}
}

