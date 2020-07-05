# CakePHP REST Baker

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mixerapi/cakephp-rest-baker.svg?style=flat-square)](https://packagist.org/packages/mixerapi/cakephp-rest-baker)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE.md)
[![Build Status](https://travis-ci.org/mixerapi/cakephp-rest-baker.svg?branch=master)](https://travis-ci.org/mixerapi/cakephp-rest-baker)
[![Coverage Status](https://coveralls.io/repos/github/mixerapi/cakephp-rest-baker/badge.svg?branch=master)](https://coveralls.io/github/mixerapi/cakephp-rest-baker?branch=master)

The missing RESTful API component to CakePHP's bake console. Create RESTful API skeletons in seconds. This is very 
similar to https://github.com/cakephp/bake but it removes redirects, flash messages, and other code that is not 
applicable to API only projects. Deletes by default will return an HTTP 204.

## Installation

```bash
composer require mixerapi/cakephp-rest-baker
bin/cake plugin load RestBaker
```

Alternatively after composer installing you can manually load the plugin in your Application:

```php
# src/Application.php
public function bootstrap(): void
{
    // other logic...
    $this->addPlugin('RestBaker');
}
```

## Usage

Add `--theme RestBaker` to your bake commands.

Examples:
 
```bash
bin/cake bake bake controller all --theme RestBaker
bin/cake bake controller {Name} --theme RestBaker
```

## Unit Tests

```bash
vendor/bin/phpunit
```
