# Agenda

An agenda is an OpenAgenda existing agenda.

_You must create an OpenAgenda client object before querying agendas._  
_See [basics](basics.md) of how to do this._

## Summary

* [my agendas](#my-agendas)
* [search](#search)
* [get](#get)
* [Agenda object](#schema)

## My agendas

```php
$agendas = $oa->myAgendas(['limit' => 2]);
```

### Params

| field    | type               | description                                                                                                                 |
|----------|--------------------|-----------------------------------------------------------------------------------------------------------------------------|
| limit    | integer            | How many results by request. Default `100`                                                                                  |
| page     | integer            | Pagination. Require a PSR-16 configured cache.<br/>You can only ask for next or previous page.<br/>**Not implemented yet**. |


## Search

```php
// Using OpenAgenda::agenda() method
$agendas = $oa->agendas([
    'limit' => 5,
    'id' => [12, 34, 56],
    'sort' => 'recent_events',
]);
// one agenda by slug
$agenda = $oa->agendas(['limit' => 1, 'slug' => 'my-agenda-slug'])->first();
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

The `agendas()` method return a [Collection](collection.md) of `OpenAgenda\Entity\Agenda` objects.

## Get

Get one agenda.

```php
// Using OpenAgenda::agenda() method
$agenda = $oa->agenda(['id' => 12345, 'detailed' => true])->get();
```

**Params**:

| field    | type    | Required | description                             |
|----------|---------|:--------:|-----------------------------------------|
| id       | integer |    Y     | Agenda id                               |
| detailed | boolean |    n     | Return detailed Agenda schema if `true` |

## Schema

|    Field     |   Type   | Description         |
|:------------:|:--------:|:--------------------|
|      id      |   int    | Agenda id           |
|    title     |  string  | Title               |
|     slug     |  string  | Slug                |
| description  |  string  | Description         |
|     url      |  string  | External URL        |
|   official   | boolean  | Is official         |
|    image     |  string  | Image URL           |
|   private    | boolean  | Is private          |
|   indexed    | boolean  | Is indexed          |
|   settings   |  array   | Agenda settings     |
|   summary    |  array   | Summary             |
|   network    | integer  | Network id          |
| location_set | integer  | Location set id     |
|  created_at  | DateTime | Created at datetime |
|  updated_at  | DateTime | Updated at datetime |

See [Entity](entity.md) for all entity methods.
