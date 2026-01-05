<?php

declare(strict_types=1);

namespace Druid\Genx\Listeners;

use Zen\Modulr\Events\ControllerPromptsCollecting;

use function Laravel\Prompts\multiselect;

/**
 * @codeCoverageIgnore
 */
class ControllerPromptsListener
{
  /**
   * Handle the event.
   */
  public function handle(ControllerPromptsCollecting $event): void
  {
    // Only add middleware prompt if route discovery is enabled
    if (! config('genx.route_discovery', false)) {
      return;
    }

    $middleware = $this->collectMiddlewareSelection();

    if ($middleware !== '') {
      $event->addOption('--middleware', $middleware);
    }
  }

  /**
   * Collect middleware selection from the user.
   */
  protected function collectMiddlewareSelection(): string
  {
    $available = $this->getAvailableMiddleware();

    /** @var list<string> $selected */
    $selected = multiselect(
      label: 'Which middleware should be applied?',
      options: $available,
      default: ['auth'],
      hint: 'Use space to select, enter to confirm'
    );

    if ($selected === []) {
      return '';
    }

    return collect($selected)
      ->map(fn (string $m): string => "'{$m}'")
      ->implode(', ');
  }

  /**
   * Get available middleware options.
   *
   * @return array<string, string>
   */
  protected function getAvailableMiddleware(): array
  {
    return [
      'auth' => 'auth - Require authentication',
      'auth.basic' => 'auth.basic - HTTP Basic authentication',
      'guest' => 'guest - Guests only (not authenticated)',
      'password.confirm' => 'password.confirm - Require password confirmation',
      'signed' => 'signed - Validate signed URLs',
      'throttle' => 'throttle - Rate limiting',
      'verified' => 'verified - Require verified email',
    ];
  }
}
