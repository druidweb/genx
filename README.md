# Genx

<p align="center">
<a href="https://github.com/druidweb/genx/blob/main/coverage.xml"><img src="https://img.shields.io/badge/dynamic/xml?color=success&label=coverage&query=round%28%2F%2Fcoverage%2Fproject%2Fmetrics%2F%40coveredelements%20div%20%2F%2Fcoverage%2Fproject%2Fmetrics%2F%40elements%20%2A%20100%29&suffix=%25&url=https%3A%2F%2Fraw.githubusercontent.com%2Fdruidweb%2Fgenx%2Fmain%2Fcoverage.xml" alt="Coverage"></a>
<a href="https://github.com/druidweb/genx/actions"><img src="https://img.shields.io/github/actions/workflow/status/druidweb/genx/main.yml?branch=main" alt="Build Status"></a>
<a href="https://packagist.org/packages/druidweb/genx"><img src="https://img.shields.io/packagist/dt/druidweb/genx" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/druidweb/genx"><img src="https://img.shields.io/packagist/v/druidweb/genx" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/druidweb/genx"><img src="https://img.shields.io/packagist/l/druidweb/genx.svg" alt="License"></a>
</p>

## About

Genx overrides Laravel's Artisan `make:*` commands with Zen-formatted generators that produce clean, consistent PHP files. All generated code follows Zen coding standards with 2-space indentation, strict types, and optional final classes.

## Installation

```bash
composer require druid/genx
```

Publish the configuration file:

```bash
php artisan genx:install
```

## Configuration

After installation, configure Genx in `config/genx.php`:

### Code Style Options

```php
// Add declare(strict_types=1) to all generated files
'strict_types' => true,

// Make all generated classes final
'final_classes' => true,
```

### Package Integrations

```php
// Add Spatie Route Discovery attributes to controllers
'route_discovery' => false,

// Enable zenphp/modulr integration for modular architecture
'modulr' => false,
```

### Generator Paths

Customize where each file type is generated:

```php
'paths' => [
    'controller' => 'app/Http/Controllers',  // or 'app/Controllers' for flat structure
    'model' => 'app/Models',
    'middleware' => 'app/Http/Middleware',   // or 'app/Middleware' for flat structure
    // ... see config file for all options
],
```

## Usage

Use Laravel's standard `make:*` commands - Genx automatically overrides them:

```bash
php artisan make:controller UserController
php artisan make:model Post
php artisan make:middleware RateLimiter
php artisan make:request StoreUserRequest
```

### Route Discovery Integration

When `route_discovery` is enabled, generated controllers include Spatie Route Discovery attributes:

```php
#[Route(middleware: ['auth', 'verified'])]
final class UserController extends Controller
{
    // ...
}
```

You'll be prompted to select which middleware to apply to each controller.

### Modulr Integration

When `modulr` is enabled and you have [zenphp/modulr](https://github.com/zenphp/modulr) installed, you can generate files within modules:

```bash
php artisan make:controller UserController --module=billing
```

Or use the interactive module generator which integrates with Genx prompts:

```bash
php artisan modules:make billing
```

## Supported Generators

Genx overrides these Laravel generators:

- `make:cast`
- `make:channel`
- `make:class`
- `make:command`
- `make:component`
- `make:controller`
- `make:enum`
- `make:event`
- `make:exception`
- `make:factory`
- `make:interface`
- `make:job`
- `make:listener`
- `make:mail`
- `make:middleware`
- `make:migration`
- `make:model`
- `make:notification`
- `make:observer`
- `make:policy`
- `make:provider`
- `make:request`
- `make:resource`
- `make:rule`
- `make:scope`
- `make:seeder`
- `make:test`
- `make:trait`
- `make:view`

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](https://github.com/druidweb/genx/security/policy) on how to report security vulnerabilities.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

- Built by Jetstream Labs

## Support

- **Issues**: [GitHub Issues](https://github.com/druidweb/genx/issues)
