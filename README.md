# REST Server

| `master` | `develop` |
|----------|-----------|
| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/rest-server/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Innmind/rest-server/?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/rest-server/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/rest-server/?branch=develop) |
| [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/rest-server/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Innmind/rest-server/?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/rest-server/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/rest-server/?branch=develop) |
| [![Build Status](https://scrutinizer-ci.com/g/Innmind/rest-server/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Innmind/rest-server/build-status/master) | [![Build Status](https://scrutinizer-ci.com/g/Innmind/rest-server/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/rest-server/build-status/develop) |

This library contains a set of tools to define, validate, extract and expose resources through http in a REST manner.

## Installation

Via composer:

```sh
composer require innmind/rest-server
```

## Usage

```php
use function Innmind\Rest\Server\bootstrap;
use Innmind\Rest\Server\Gateway;
use Innmind\Immutable\Map;

$services = bootstrap(
    new Map('string', Gateway::class),
    require '/path/to/resources/mapping.php'
);

$services['routes']; // provides all the routes available for the deinfitions you provided

// action controllers
$services['controller']['create'];
$services['controller']['index'];
$services['controller']['get'];
$services['controller']['remove'];
$services['controller']['update'];
$services['controller']['link'];
$services['controller']['unlink'];
// controller to output the resource definition
$services['controller']['options'];
// controller to expose links to all the resources definitions
$services['controller']['capabilities'];
```

The gateways are the bridges between this component and your domain. The definition handling which resource is handled by which gateway is done in the resources mapping where a resource can only be managed by one gateway. Take a look at [`fixtures/mapping.php`](fixtures/mapping.php) to understand how to define your resources.
