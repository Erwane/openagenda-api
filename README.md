# OpenAgenda API SDK for PHP

## Installation

The sdk is not directly usable.  
You need to use one client wrapper, compatible with your PSR-18 http client.

Available wrappers are:
* Guzzle: `erwane/openagenda-wrapper-guzzle`

example:
```php
composer require erwane/openagenda-wrapper-guzzle:"^2.2"
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
