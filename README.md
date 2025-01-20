# PHP lib for OpenAgenda API (all CRUD endpoints)

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![codecov](https://codecov.io/gh/Erwane/openagenda-api/branch/3.x/graph/badge.svg?token=hF5HhETnkg)](https://codecov.io/gh/Erwane/openagenda-api)
[![Build Status](https://github.com/Erwane/openagenda-api/actions/workflows/ci.yml/badge.svg?branch=3.x)](https://github.com/Erwane/openagenda-api/actions)
[![Packagist Downloads](https://img.shields.io/packagist/dt/Erwane/openagenda-api)](https://packagist.org/packages/Erwane/openagenda-api)
[![Packagist Version](https://img.shields.io/packagist/v/Erwane/openagenda-api)](https://packagist.org/packages/Erwane/openagenda-api)

This package will help you to query [OpenAgenda API](https://developers.openagenda.com/).
It supports all endpoints with all methods (HEAD, GET, POST, PATCH & DELETE).

## Version map

| branch | This package version | OpenAgenda API | PHP min |
|:------:|----------------------|:--------------:|:-------:|
|  3.x   | 3.0.*                |       v2       | PHP 7.2 |
|  3.x   | ^3.1                 |       v2       | PHP 8.0 |

## Installation

The sdk is not directly usable.  
You need to use one client wrapper, compatible with your PSR-18 http client or framework.

### Wrappers
_Please check version map in the `README` of your desired wrapper._

#### CakePHP 
[erwane/openagenda-wrapper-cakephp](https://github.com/Erwane/openagenda-wrapper-cakephp)
```php
composer require erwane/openagenda-wrapper-cakephp
```

#### Guzzle 
[erwane/openagenda-wrapper-guzzle](https://github.com/Erwane/openagenda-wrapper-guzzle)
```php
composer require erwane/openagenda-wrapper-guzzle
```

## Documentations and examples

* [Basics](docs/basics.md)
* [Agendas](docs/agenda.md)
* [Events](docs/event.md)
* [Locations](docs/location.md)
* [Cache](docs/cache.md)

## Quick start

```php
// Use Guzzle to request api.
composer require erwane/openagenda-wrapper-guzzle

use OpenAgenda\OpenAgenda;
use OpenAgenda\Wrapper\GuzzleWrapper

$wrapper = new GuzzleWrapper($clientOptions = []);
$oa = new OpenAgenda([
    'public_key' => 'my public key',
    'secret_key' => 'my secret key',
    'wrapper' => $wrapper,
 ]);
```
