# Agendas

An agenda is an OpenAgenda existing agenda.

_You must create an OpenAgenda client object before querying agendas._  
_See [basics](basics.md) of how to do this._

## Summary

* [my agendas](#my-agendas)
* [search](#search)
* [get](#get)
* [Location object](#schema)
* [Latitude and Longitude precision](#latitude-and-longitude-precision)

## My agendas

```php
// using endpoint
$agendas = $oa->get('/agendas/mines', $params);
// using myAgendas() method
$agendas = $oa->myAgendas($params);
```

### Params

The `/agendas/mines` endpoint or `myAgendas()` method accept a params array with this possible keys:

* int `limit`: How many results you want by request. Default `10`
* int `page`: Pagination. Require a PSR-16 configured cache.  
  You can only ask for next or previous page.  
  **Not implemented yet**.

### Example

```php
$agenda = $oa->myAgendas(['limit' => 1])
    ->first();
```

## Search

```php
// Endpoint params
$params = [
    'limit' => 5,
    'id' => [12, 34, 56],
    'sort' => 'recent_events',
];

// Using endpoint
$agendas = $oa->get('/agendas', $params);
// Using OpenAgenda::agenda() method
$agendas = $oa->agendas($params);
```

### Params

| field    | type               | description                                                                                                                 |
|----------|--------------------|-----------------------------------------------------------------------------------------------------------------------------|
| limit    | integer            | How many results by request. Default `100`                                                                                  |
| page     | integer            | Pagination. Require a PSR-16 configured cache.<br/>You can only ask for next or previous page.<br/>**Not implemented yet**. |
| fields   | string or string[] | Optional extra fields to get for agendas.<br/>Possible values are `['summary', 'schema']`.                                  |
| search   | string             | Search terms in title, locations and agenda keywords                                                                        |
| official | boolean            | Only officials agendas. Default `false`.                                                                                    |
| slug     | string or string[] | Get agendas with this slug(s).                                                                                              |
| id       | id or id[]         | Get agendas with this id(s)                                                                                                 |
| network  | int                | Get only agendas in this network id                                                                                         |
| sort     | string             | Sort results.<br/>Allowed values are `created_desc` and `recent_events`                                                     |

## Results

The `agendas()` method return a collection of `Agenda` objects.  
See [Collection](collections.md) for more detail.

## Get

Get only one agenda.

```php
// Endpoint params
$params = [
    'id' => 12345,
    'detailed' => true,
];

// Using endpoint
$agenda = $oa->get('/agenda', $params);
// Using OpenAgenda::agenda() method
$agenda = $oa->agenda($params);
```

**Params**:

| field    | type    | Required | description                             |
|----------|---------|:--------:|-----------------------------------------|
| id       | integer |    Y     | Agenda id                               |
| detailed | boolean |    n     | Return detailed Agenda schema if `true` |

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
