# Entity

`Agenda`, `Location` and `Event` are `Entity` object.
The entity object is a representation of the model and can be manipulated in your project.

Entity extends `ArrayAccess`.

## Accessor
Entity properties can be accessed directly or through the `get` method.
```php
$agenda->uid
$agenda->get('uid');
```
Entity could also be accessed like arrays.
```php
$name = $agenda['name'];
```

## Mutator
Entity properties could be sets directly, through `set` method or ArrayAccess.
```php
$event->uid = 123
$event->set('uid', 123)
    ->set('title', 'My event' );
// Multiple sets
$event->set(['uid' => 123, 'title' => 'My event']);
// ArrayAccess
$event['uid'] = 123;
```

## Setters
Setters are entity magic methods called when you set a data.  
They format data in the entity to match OpenAgenda or facilitate manipulation.

Example, a json fields is always translated as array then you can access it easily.

example with the `title` field of an event who is a multilingual field.
```php
$event['title']['fr'] = 'Mon évènement';
````

You can disable setters only when you create an entity, and only for the __construct.
```php
$event = new Event(['uid' => '123'], ['useSetters' => false]);
$event->uid; // '123' instead of 123
```

## Clean and dirty
When you create manually an entity, you can mark your data as clean.
```php
$event = new Event(['agendaUid' => 123, 'title' => 'My event'], ['markClean' => true]);
```
This mean your data are not considered as dirty (new) and will not be pushed in case or `update`.

Neither, you can mark a field as dirty.
```php
$event = new Event(['agendaUid' => 123, 'title' => 'My event'], ['markClean' => true]);
$event->setDirty('title');
```

## New
When an Entity is `new` (default), all fields are passed to request.
You can force an entity as not new with `setNew(false)` method.

## toArray
All entity could be exported as an array with `$entity->toArray()`
