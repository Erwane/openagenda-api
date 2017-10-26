# OpenAgenda API SDK for PHP

## Warning
This package is Work In Progress.
Wait for this warning disappear from README ;)

## Installation
use composer
```
composer require openagenda/api-sdk
```

## Usage
```php
use OpenAgenda\OpenAgenda;

/*
 * create OpenAgenda instance
 * @param $publicKey from your account settings page
 * @param $privateKey from your account settings page
 */
$openAgenda = new OpenAgenda('public-key', 'private-key');

/*
 * create Location object
 */
// locationDatas can be an integer (previous location stored in database)
$locationDatas = 123;

// Or a data array
$locationDatas = [
    'address' => '221B Baker Street, London, England'
    'latitude' => 51.523797, 
    'longitude' => -0.158320,
    'placename' => 'Elementary'
];

// Create location object and add date/time to it
// You can add multiple dates
$location = $openAgenda->newLocation($locationDatas)
    ->setPricing('6€') 
    ->addDate([
        'date' => '2017-10-20',
        'start' => '08:00',
        'end' => '23:00',
    ]);

// Location is requested and $location->uid contain uid

/*
 * create event object and set informations
 */
$event = $openAgenda->newEvent()
    ->setLanguage('fr')  // global language
    ->setTitle('My title')
    ->setKeywords(['array', 'of', 'keywords'])
    ->setDescription('My event description')
    ->setFreeText('My event free text, can be text or MD format')
    ->setLocation($location)
    ->setState(1) // 1 is published, 0 is not
    ->setPicture('/absolute/path/to/picture')
;

// publish event to openagenda and set uid in $event->uid
$openAgenda->publish($event);

// get agenda object from slug
// and set category
$agenda = $openAgenda->getAgenda('agenda-slug')->setCategory('Fun');

// Attach event to agenda
$openAgenda->attachEventToAgenda($event, $agenda);
```

### known bugs
setPricing not working.

## Performance
A small cache is used for accessToken and agenda slugs id. OpenAgenda API is not requested when not necessary