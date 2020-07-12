# MixerApi REST

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mixerapi/cakephp-rest.svg?style=flat-square)](https://packagist.org/packages/mixerapi/cakephp-rest)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE.md)
[![Build Status](https://travis-ci.org/mixerapi/cakephp-rest.svg?branch=master)](https://travis-ci.org/mixerapi/cakephp-rest)
[![Coverage Status](https://coveralls.io/repos/github/mixerapi/cakephp-rest/badge.svg?branch=master)](https://coveralls.io/github/mixerapi/cakephp-rest?branch=master)

The missing RESTful API toolkit for CakePHP. 

- Bake RESTful API skeletons in seconds with an enhanced bake template.
- Generate route resources with a single command.

## Installation

```bash
composer require mixerapi/cakephp-rest
bin/cake plugin load MixerApiRest
```

Alternatively after composer installing you can manually load the plugin in your Application:

```php
# src/Application.php
public function bootstrap(): void
{
    // other logic...
    $this->addPlugin('MixerApiRest');
}
```

## Bake Usage

Add `--theme MixerApiRest` to your bake commands.

Examples:
 
```bash
# bake all your controllers
bin/cake bake controller all --theme MixerApiRest

# bake a single controller
bin/cake bake controller {ControllerName} --theme MixerApiRest

# you can even bake the entire application still, MixerApiRest only deals with controller files
bin/cake bake all --everything --theme MixerApiRest
```

## Route Commands

Generate route resources with a single command.

```bash
# scans your controllers and writes CRUD routes to `config/routes.php`
bin/cake mixerapi:rest create

# add the `--plugin` switch to write to `plugins/{YourPlugin}/config/routes.php`
bin/cake mixerapi:rest create --plugin {MyPlugin}

# Use `--display` to show the routes that will be created
bin/cake mixerapi:rest create --display 
```

#### List Routes

This works similar to `bin/cake routes` but shows only RESTful routes and improves some formatting of information.

```bash
bin/cake mixerapi:rest list
```

## Unit Tests

```bash
vendor/bin/phpunit
```

## Code Standards

```bash
composer check
```
