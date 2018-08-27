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
$container = (new ContainerBuilder)(
    new Path('container.yml'),
    (new Map('string', 'mixed'))
        ->put('gateways', new Map('string', Gateway::class))
        ->put('files', Set::of('string', '/path/to/resources/mapping.yml'))
);

$container->get('routes'); // provides all the routes available for the deinfitions you provided

// action controllers
$container->get('create');
$container->get('index');
$container->get('get');
$container->get('remove');
$container->get('update');
$container->get('link');
$container->get('unlink');
// controller to output the resource definition
$container->get('options');
// controller to expose links to all the resources definitions
$container->get('capabilities');
```

The gateways are the bridges between this component and your domain. The definition handling which resource is handled by which gateway is done in the resources mapping where a resource can only be managed by one gateway. Take a look at [`fixtures/mapping.yml`](fixtures/mapping.yml) to understand how to define your resources.

## Structure

Container definition:

![](graph.png)
