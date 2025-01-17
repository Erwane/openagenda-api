# Basics usage

This package need a PSR 18 HTTP Client (`psr/http-client`).

For performance, you can configure a PSR 16 cache (`psr/simple-cache`).

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

## Getting agendas

See [agendas](agenda.md) for more details.

```php
$agenda = $oa->agendas(['slug' => 'agenda-slug'])->first();
```
