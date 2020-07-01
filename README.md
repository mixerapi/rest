# CakePHP REST Baker

The missing RESTful API component to CakePHP's bake console. Create RESTful API skeletions in seconds. This is very 
similiar to https://github.com/cakephp/bake but it removes redirects, flash messages, and other code that is not 
applicable to API only projects. Deletes by default will return an HTTP 204.

## Installation

```bash
composer require cnizzardini/cakephp-rest-baker
bin/cake plugin load RestBaker
```

Alternative after composer installing you can manually load the plugin in your Application:

```php
# src/Application.php
public function bootstrap(): void
{
    // other logic...
    $this->addPlugin('RestBaker');
}
```

## Usage

```bash
bin/cake bake controller {Name} --theme RestBaker
```
