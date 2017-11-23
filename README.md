# OpenAgenda API SDK for PHP

## Installation
use composer
```
composer require openagenda/api-sdk
```

## Usage

### Creation and Authentication
```php
use OpenAgenda\OpenAgenda;

/*
 * create OpenAgenda instance
 * @param $publicKey from your account settings page
 * @param $privateKey from your account settings page
 */
$openAgenda = new OpenAgenda('public-key', 'private-key');

/*
 * you can set baseUrl of your website, this will
 * transform all relatives links in longDescription
 * as absolute links to your website
 * You can also use $event->baseUrl before $event->setLongDescription()
 */
$openAgenda->setBaseUrl('example.com');
```

### Publish event
```php
/*
 * create Location object
 */
// locationDatas can be an integer (previous location stored in database)
$locationDatas = 123;

// Or a data array
$locationDatas = [
    'placename' => 'Elementary',
    'address' => '221B Baker Street, London, England',
    'latitude' => 51.523797,
    'longitude' => -0.158320,
];

// Create location object with uid property
$location = $openAgenda->getLocation($locationDatas);

/*
 * create event object and set informations
 */
$event = $openAgenda->newEvent()
    ->setLanguage('en')  // global language
    ->setTitle('My title')
    ->setDescription('My event description')
    ->setLongDescription('My event free text, can be text or MD format')
    ->setKeywords(['array', 'of', 'keywords'])
    ->setConditions('6€ / 8€')
    ->setLocation($location)
    ->setTimings([
        'date' => '2017-10-20 08:00:00',    // auto converted to 2017-10-20
        'begin' => '2017-10-20 08:00:00',   // auto converted to 08:00
        'end' => '2017-10-20 23:00:00',     // auto converted to 23:00
    ])
    ->setAge(0, 110) // (min, max)
    ->setState(1) // 1 is published, 0 is not
    ->setPicture('/absolute/path/to/picture')
;

// You can specify language for fields
// title, description, longDescription, keywords,  conditions
$event->setKeywords(['tableau', 'de', 'motclé'], 'fr');

// publish event to openagenda and set uid in $event->uid
$openAgenda->publishEvent($event);

// get agenda object from slug
$agenda = $openAgenda->getAgenda('agenda-slug');

// Attach event to agenda
$openAgenda->attachEventToAgenda($event, $agenda);
```

### Update event
```php
$event = $openAgenda->getEvent(0123456789);

$event->setTitle('Mon titre', 'fr');

$openAgenda->updateEvent($event);
```

### Delete event
```php
$openAgenda->deleteEvent(0123456789, 'openagenda-slug-or-id');
```


## Performance
A small cache is used for accessToken and agenda slugs id. OpenAgenda API is not requested when not necessary

For event update, only the "dirty" fields are sents to API
