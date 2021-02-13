# REST Server

[![Build Status](https://github.com/Innmind/rest-server/workflows/CI/badge.svg?branch=master)](https://github.com/Innmind/rest-server/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/Innmind/rest-server/branch/develop/graph/badge.svg)](https://codecov.io/gh/Innmind/rest-server)
[![Type Coverage](https://shepherd.dev/github/Innmind/rest-server/coverage.svg)](https://shepherd.dev/github/Innmind/rest-server)

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

$services['routes']; // provides all the routes available for the definitions you provided

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
