# OpenAgenda API SDK for PHP

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![codecov](https://codecov.io/gh/Erwane/openagenda-api/branch/3.x/graph/badge.svg?token=hF5HhETnkg)](https://codecov.io/gh/Erwane/openagenda-api)
[![Build Status](https://github.com/Erwane/openagenda-api/actions/workflows/ci.yml/badge.svg?branch=3.x)](https://github.com/Erwane/cakephp-contact/actions)
[![Packagist Downloads](https://img.shields.io/packagist/dt/Erwane/openagenda-api)](https://packagist.org/packages/Erwane/openagenda-api)
[![Packagist Version](https://img.shields.io/packagist/v/Erwane/openagenda-api)](https://packagist.org/packages/Erwane/openagenda-api)


## Installation

The sdk is not directly usable.  
You need to use one client wrapper, compatible with your PSR-18 http client.

Available wrappers are:
* Guzzle: `erwane/openagenda-wrapper-guzzle`

example:
```php
composer require erwane/openagenda-wrapper-guzzle:"1.0.*"
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

$wrapper = new GuzzleWrapper();
$oa = new OpenAgenda([
    'public_key' => 'my public key',
    'secret_key' => 'my secret key',
    'wrapper' => $wrapper,
 ]);
```

See [wrappers](docs/wrapper.md) for more details and options.

## Contributing
