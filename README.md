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
| 3.0.x  | 3.0.*                |       v2       | PHP 7.2 |
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

* [Agendas](docs/agendas.md)
* [Events](docs/events.md)
* [Locations](docs/locations.md)
* [Validations](docs/validation.md)

## Quick start

This package require wrapper compatible with your PSR-18 Http client (`psr/http-client`).

For performance and reduce queries in `post`, `patch` & `delete` (authenticated request), you can configure a PSR 16 cache (`psr/simple-cache`).

```php
use OpenAgenda\OpenAgenda;
use OpenAgenda\Wrapper\GuzzleWrapper

// PSR-18 Http client.
$guzzleOptions = ['timeout'  => 2.0];
$wrapper = new GuzzleWrapper($guzzleOptions);

// PSR-16 Simple cache. Optional
$cache = new Psr16Cache();

// Create the OpenAgenda client. The public key is required for reading data (GET)
// The private key is optional and only needed for writing data (POST, PUT, DELETE)
$oa = new OpenAgenda([
    'public_key' => 'my public key', // Required
    'secret_key' => 'my secret key', // Optional, only for create/update/delete
    'wrapper' => $wrapper, // Required
    'cache' => $cache, // Optional
    'defaultLang' => 'fr', // Optional
]);
```

### Usages

**Agendas**
```php
$agendas = $oa->myAgendas(['limit' => 2]);
$agenda = $oa->agendas(['slug' => 'agenda-slug'])->first();
```
See [agendas](docs/agendas.md) for more details.

**Locations**
```php
// Search
$locations = $oa->locations(['agendaUid' => 123, 'name' => 'My Location']);
// Exists and get
$exists = $oa->location(['uid' => 456, 'agendaUid' => 123])->exists();
$location = $oa->location(['uid' => 456, 'agendaUid' => 123])->get();
// Create
$location = $oa->location($data)->create();
```
See [locations](docs/locations.md) for more details.

**Events**
```php
// Search
$events = $oa->events(['agendaUid' => 123, 'title' => 'My event']);
// Exists and get
$exists = $oa->event(['uid' => 456, 'agendaUid' => 123])->exists();
$event = $oa->event(['uid' => 456, 'agendaUid' => 123])->get();
// Create
$event = $oa->event($data)->create();
```
See [events](docs/events.md) for more details.
