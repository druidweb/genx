<?php

declare(strict_types=1);

namespace Druid\Genx\Database;

use Illuminate\Database\Migrations\MigrationCreator;

class GenxMigrationCreator extends MigrationCreator
{
  /**
   * Get the migration stub file.
   *
   * Always use genx stubs (not customStubPath) and apply config options dynamically.
   *
   * @param  string|null  $table
   * @param  bool  $create
   */
  protected function getStub($table, $create): string // @pest-ignore-type
  {
    $stubPath = $this->stubPath();

    if (is_null($table)) {
      $stub = $stubPath.'/migration.stub';
    } elseif ($create) {
      $stub = $stubPath.'/migration.create.stub';
    } else {
      $stub = $stubPath.'/migration.update.stub';
    }

    $content = $this->files->get($stub);

    return $this->applyGenxOptions($content);
  }

  /**
   * Apply genx options to the stub content.
   */
  protected function applyGenxOptions(string $stub): string
  {
    $strictTypes = $this->useStrictTypes() ? "declare(strict_types=1);\n" : '';

    return str_replace('{{ strictTypes }}', $strictTypes, $stub);
  }

  /**
   * Check if strict types are enabled in config.
   */
  protected function useStrictTypes(): bool
  {
    return (bool) config('genx.strict_types', true);
  }

  /**
   * Get the path to the stubs.
   */
  public function stubPath(): string
  {
    return dirname(__DIR__, 2).'/stubs/shared';
  }
}
