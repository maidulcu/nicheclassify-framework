# NicheClassify Framework

A modern, modular WordPress plugin framework for building niche-specific classified listing solutions.

## Overview

The NicheClassify Framework provides a powerful and flexible way to create classified listing solutions tailored to specific niches. With a focus on modularity and extensibility, this plugin allows developers to build and customize their applications with ease.

## Features

- Class-based architecture with PSR-4 autoloading
- Namespaced and extensible components
- Frontend submission forms with media support
- Dynamic field schema and filtering
- LeafletJS map integration
- User dashboard and contact form
- Composer and PHPUnit support
- GitHub Actions CI for testing and ZIP builds

## Setup

1. Clone the repository or download the ZIP.
2. Place the folder inside `wp-content/plugins/`.
3. Run `composer install` to install dependencies.
4. Activate the plugin via the WordPress admin panel.

## Usage

After activation, navigate to the plugin settings to configure options and start building your classified listings. Utilize the provided components to create custom forms and dashboards as needed.

## Testing

This plugin uses PHPUnit for testing. To run the tests, execute the following commands:

```bash
composer install
vendor/bin/phpunit
```

## Branching

- `main`: Stable releases
- `develop`: Active development branch

## GitHub Actions

- On `develop`: Runs PHPUnit
- On `main`: Builds plugin ZIP

## License

This project is licensed under the MIT License.
