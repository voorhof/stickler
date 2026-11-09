# Stickler

A Laravel application starter kit.

[![🚀 Stickler tests](https://github.com/voorhof/stickler/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/voorhof/stickler/actions/workflows/tests.yml)
[![Plumb](https://plumbphp.dev/badges/voorhof/stickler/composite.svg)](https://plumbphp.dev/voorhof/stickler)
[![Packagist](https://img.shields.io/packagist/v/voorhof/stickler.svg)](https://packagist.org/packages/voorhof/stickler)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://opensource.org/licenses/MIT)

## Documentation

| File | Description |
|------|-------------|
| [STICKLER.md](STICKLER.md) | Starter kit overview, project structure, installation, authentication, payment and checkout, and the production deployment checklist. |
| [FILAMENT.md](FILAMENT.md) | Filament admin panel implementation guide, including resources, plugins, store management, and page views. |
| [COMPOSER_PACKAGES.md](COMPOSER_PACKAGES.md) | Composer package dependencies and their roles in the application. |
| [NODE_MODULES.md](NODE_MODULES.md) | Node.js package dependencies for frontend asset building. |

## Quick Install

```bash
laravel new my-app --using=voorhof/stickler:v1.9.0 --phpunit --no-boost
```

Configure `.env` with your database, mail driver, and application settings. For detailed installation steps and production deployment, see [STICKLER.md](STICKLER.md).

## Security Vulnerabilities

If you discover a security vulnerability within Stickler, please send an e-mail to David Carton via [david@notrac.be](mailto:david@notrac.be).
All security vulnerabilities will be promptly addressed.

## License

Stickler is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
