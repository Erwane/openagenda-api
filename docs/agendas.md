# Agendas

An agenda is an OpenAgenda existing agenda.

_You must create an OpenAgenda client object before querying agendas._  
_See [basics](basics.md) of how to do this._

## Search or get my agendas

```php
// using endpoint
$agendas = $oa->get('/agendas/me', $options);
// using myAgendas() method
$agendas = $oa->myAgendas($options);
```

### Options

The `/agendas/me` endpoint or `myAgendas()` method accept an options array with this possible keys:

* int `limit`: How many results you want by request. Default `10`
* int `page`: Pagination. Require a PSR-16 configured cache.  
  You can only ask for next or previous page.  
  **Not implemented yet**.

### Example

```php
$agenda = $oa->myAgendas(['limit' => 1])
    ->first();
```

## Search or get another agendas

```php
// using endpoint
$agendas = $oa->get('/agendas', $options);
// using agendas() method
$agendas = $oa->agendas($options);
```

### Options

The `/agendas` endpoint or `agendas()` method accept an options array with this possible keys:

* int `size`: How many results by request. Default `10`
* int `page`: Pagination. Require a PSR-16 configured cache.  
You can only ask for next or previous page.  
**Not implemented yet**.
* array `fields`: Optional extra fields to get for agendas.  
Possible values are `['summary', 'schema']`.
* string `search`: Search terms in title, locations and agenda keywords.
* bool `official`: Only officials agendas. Default `false`.
* string|string[] `slug`: Get agendas with this slug(s).  
Can be a string or an array of slugs.
* int|int[] `id`: Get agendas with this id(s).  
Can be an integer or an array of integers.
* int `network`: Get only agendas in this network id.
* string `sort`: Sort results.  
Possible values are:
  * `created_desc`: New agendas first.
  * `recent_events`: Agendas with recent added event first.

### Example

```php
$agendaId = $oa->agendas(['slug' => 'agenda-slug'])
    ->first()
    ->id;
```

## Results

The `agendas()` method return a collection of `Agenda` objects.  
See [Collection](collections.md) for more detail.

## `Agenda` object
Items in collection are `Agenda` object and have those methods.

**toJson()**: `string`
Return the object as json string.
```php
$agenda->toJson();
```
Todo: define json schema.

**toArray()**: `array`
Return the object as array.
```php
$agenda->toArray();
```
Todo: define array schema.

**id**: `int`
The agenda id property
```php
$agenda->id;
```
