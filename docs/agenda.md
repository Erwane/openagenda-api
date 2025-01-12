# Agenda

An agenda is an OpenAgenda existing agenda.

_You must create an OpenAgenda client object before querying agendas._  
_See [basics](basics.md) of how to do this._

## Summary

* [my agendas](#my-agendas)
* [search](#search)
* [get or exists](#get)
* [Agenda object](#schema)

## My agendas

```php
$agendas = $oa->myAgendas(['limit' => 2]);
```

### Params

| field | type    | description                                                                                                                 |
|-------|---------|-----------------------------------------------------------------------------------------------------------------------------|
| limit | integer | How many results by request. Default `100`                                                                                  |
| page  | integer | Pagination. Require a PSR-16 configured cache.<br/>You can only ask for next or previous page.<br/>**Not implemented yet**. |

## Search

```php
// Using OpenAgenda::agenda() method
$agendas = $oa->agendas([
    'size' => 5,
    'uid' => [12, 34, 56],
    'sort' => 'recentlyAddedEvents.desc',
]);
// one agenda by slug
$agenda = $oa->agendas(['size' => 1, 'slug' => 'my-agenda-slug'])->first();
```

### Params

| field    | type               | description                                                                                                                 |
|----------|--------------------|-----------------------------------------------------------------------------------------------------------------------------|
| size     | integer            | How many results by request. Default `100`                                                                                  |
| page     | integer            | Pagination. Require a PSR-16 configured cache.<br/>You can only ask for next or previous page.<br/>**Not implemented yet**. |
| fields   | string or string[] | Optional extra fields to get for agendas.<br/>Possible values are `['summary', 'schema']`.                                  |
| search   | string             | Search terms in title, locations and agenda keywords                                                                        |
| official | boolean            | Only officials agendas. Default `false`.                                                                                    |
| slug     | string or string[] | Get agendas with this slug(s).                                                                                              |
| uid      | int or int[]       | Get agendas with this uid(s)                                                                                                |
| network  | int                | Get only agendas in this network id                                                                                         |
| sort     | string             | Sort results.<br/>Allowed values are `createdAt.desc` and `recentlyAddedEvents.desc`                                        |

## Results

The `agendas()` method return a [Collection](collection.md) of `OpenAgenda\Entity\Agenda` objects.

## Get

Get one agenda.  
If you want to check an agenda exists, you can use `exists()` method instead of `get()`.
```php
$exists = $oa->agenda(['uid' => 12345])->exists();
$agenda = $oa->agenda(['uid' => 12345, 'detailed' => true])->get();
```

**Params**:

| field    | type    | Required | description                             |
|----------|---------|:--------:|-----------------------------------------|
| uid      | integer |    Y     | Agenda uid                              |
| detailed | boolean |    n     | Return detailed Agenda schema if `true` |

## Schema

|     Field      |   Type   | Description         |
|:--------------:|:--------:|:--------------------|
|      uid       |   int    | Agenda uid          |
|     title      |  string  | Title               |
|  description   |  string  | Description         |
|      slug      |  string  | Slug                |
|      url       |  string  | External URL        |
|     image      |  string  | Image URL           |
|    official    | boolean  | Is official         |
|    private     | boolean  | Is private          |
|    indexed     | boolean  | Is indexed          |
|    settings    |  array   | Agenda settings     |
|    summary     |  array   | Summary             |
|   networkUid   | integer  | Network id          |
| locationSetUid | integer  | Location set id     |
|   createdAt    | DateTime | Created at datetime |
|   updatedAt    | DateTime | Updated at datetime |

See [Entity](entity.md) for all entity methods.
