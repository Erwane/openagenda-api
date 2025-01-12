# Location

A location is a physical "rendez-vous" place.

_You must create an OpenAgenda client object before querying locations._  
_See [basics](basics.md) of how to do this._

## Summary

* [search](#search)
* [get or exists](#get)
* [create](#create)
* [update](#update)
* [delete](#delete)
* [Location object](#schema)
* [Latitude and Longitude precision](#latitude-and-longitude-precision)

## Methods and endpoints

### Search

Search locations for an agenda.

Return a [Collection](collection.md) of Location items.

```php
// Using OpenAgenda::locations() method
$locations = $oa->locations(['agendaUid' => 123, 'name' => 'My Location']);

// Using previous fetched OpenAgenda\Entity\Agenda object
$locations = $agenda->locations(['name' => 'My Location']);
```
**note** for `$agenda->locations()` way, an OpenAgenda object should be created before. See [basics](basics.md).


**Params**:

| field       | type                         | Required | description                                                                                                                 |
|-------------|------------------------------|:--------:|-----------------------------------------------------------------------------------------------------------------------------|
| agendaUid   | integer                      |    Y     | Agenda id. Required if using endpoint.                                                                                      |
| limit       | integer                      |    n     | How many results by request. Default `10`                                                                                   |
| page        | integer                      |    n     | Pagination. Require a PSR-16 configured cache.<br/>You can only ask for next or previous page.<br/>**Not implemented yet**. |
| detailed    | boolean                      |    n     | When `true`, get all locations fields. Default `false`                                                                      |
| search      | string                       |    n     | Search terms in title, locations and agenda keywords.                                                                       |
| state       | boolean                      |    n     | When `true`, only verified locations. Default `null`.                                                                       |
| createdAt[lte] | DateTimeInterface or string* |    n     | Only locations created before or at.                                                                                        |
| createdAt[gte] | DateTimeInterface or string* |    n     | Only locations created at or after.                                                                                         |
| updated_lte | DateTimeInterface or string* |    n     | Only locations updated before or at.                                                                                        |n
| updated_gte | DateTimeInterface or string* |    n     | Only locations updated at or after.                                                                                         |
| sort        | string                       |    n     | Sort results.<br/>Allowed values are `name.asc`, `name.desc`, `createdAt.asc`, `createdAt.desc`                                 |

**note**: For DateTime as string, could be atom string (`2024-12-23T12:34:56+00:00`) or any valid datetime format like `2023-06-02` or `2023-06-02 12:34:56`

### Get

Get one location.  
If you want to check a location exists, you can use `head()` method instead of `get()`.

```php
// Using OpenAgenda::location()
$location = $oa->location(['uid' => 456, 'agendaUid' => 123])->get();
$exists = $oa->location(['uid' => 456, 'agendaUid' => 123])->exists();

// Using previous fetched OpenAgenda\Entity\Agenda object
$location = $agenda->location(['extId' => 'my-location-id'])->get();
$exists = $agenda->location(['uid' => 456])->exists();
```

**Params**:

| field     | type    | Required | description                            |
|-----------|---------|:--------:|----------------------------------------|
| agendaUid | integer |    Y     | Agenda id. Required if using endpoint. |
| id        | integer |    n     | Location id.                           |
| extId    | mixed   |    n     | Your internal location id.             |

### Create

Create a location in an agenda.  
Return a Location object with the new id.

```php
// Using OpenAgenda::location() method
$location = $oa->location($data)->create();

// Using previous fetched OpenAgenda\Entity\Agenda object
$location = $agenda->location($data)->create();
```

#### Data

Check [Location object](#schema) for more details.

### Update

Update a location in an agenda.  
Return the updated Location object.

You can use `post` or `patch` method.  
If using `post`, all required update fields should be sets in `$data`.  
If using `patch` only passed (or changed) fields will be updated.

You can update a location with `extId` instead of `id`.  
In this case, `id` should not exist in `$data`.

```php
// Using previous fetched OpenAgenda\Entity\Location object
$location = $oa->location(['uid' => 456, 'agendaUid' => 123])->get();
$location->state = true;
$location = $location->update(); // Only changed fields. Recommended
$location = $location->update(true); // Full update

// Or pushing data directly to OpenAgenda::location() method
$location = $oa->location(['agendaUid' => 123, 'uid' => 456, 'state' => true])->update();
```

#### Data

Check [Location object](#schema) for more details.

### Delete

Delete a location in an agenda.  
Return the deleted Location object.

You can delete a location with `extId` instead of `id`.  
In this case, `id` should not exist in `$data`.

```php
// Using previous fetched OpenAgenda\Entity\Location object
$location = $oa->location(['uid' => 456, 'agendaUid' => 123])
    ->get()
    ->delete();

// Or through OpenAgenda::location() method
$location = $oa->location(['agendaUid' => 123, 'uid' => 456])->delete();
```

## Schema

|     Field     |       Type        | Required | Description                                                                         |
|:-------------:|:-----------------:|:--------:|:------------------------------------------------------------------------------------|
|      id       |        int        |    I     | OpenAgenda unique id. Can't be sets                                                 |
|     slug      |      string       |    I     | Unique string id                                                                    |
|    setUid     |        int        |    I     | Associated location sets id                                                         |
|  createdAt   | DateTimeInterface |    I     | Creation date                                                                       |
|  updatedAt   | DateTimeInterface |    I     | Update date                                                                         |
|    extId     |   string \| int   |    O     | External id (typically, your database location id)                                  |
|     name      |      string       |  **R**   | Location name. Max 100 characters                                                   |
|    address    |      string       |  **R**   | Full address. Max 255 characters                                                    |
|    countryCode    |      string       |  **R**   | ISO 3166-1 Alpha 2 country code. ex: FR                                             |
|     state     |       bool        |    O     | `true`: location is verified  <br/>`false`: location need to be verified            |
|  description  |   multilingual    |    O     | Location description. Max 5000 characters                                           |
|    access     |   multilingual    |    O     | Location access instruction. Max 1000 characters                                    |
|    website    |      string       |    O     | Location website url                                                                |
|     email     |      string       |    O     | Location email                                                                      |
|     phone     |      string       |    O     | Location principal contact phone                                                    |
|     links     |       array       |    O     | Others location links (socials, ...)                                                |
|     image     |      string       |    O     | _todo_                                                                              |
| imageCredits |      string       |    O     | _todo_                                                                              |
|    region     |      string       |    G     | Administrative area level 1                                                         |
|  department   |      string       |    G     | Administrative area level 2                                                         |
|   district    |      string       |    G     | Administrative area level 3                                                         |
|     city      |      string       |    G     | Locality                                                                            |
|  postalCode  |      string       |    G     | Postal code                                                                         |
|     insee     |      string       |    G     | Insee code                                                                          |
|   latitude    |      decimal      |    G     | Latitude. 7 digits precision                                                        |
|   longitude   |      decimal      |    G     | Longitude. 7 digits precision                                                       |
|   timezone    |      string       |    G     | Timezone [identifier](https://en.wikipedia.org/wiki/List_of_tz_database_time_zones) |

**Required**

* **I**: Internal data. Can't be sets.
* **R**: Required at creation or update with `post`.
* **O**: Optional at creation or update
* **G**: If the field is empty, the geocoding query from the `address` field will be used.

**Multilingual text**  
The `multilingual` type is an array of text where key is the language
in [ISO 639-1](https://fr.wikipedia.org/wiki/Liste_des_codes_ISO_639-1) format.

```php
[
    'fr' => "Le Château de Villeneuve La Comtesse est un château fort de plaine, situé au nord de la Saintonge",
]
```

See [Entity](entity.md) for all entity methods.

### Latitude and Longitude precision

Don't forget the precision of coordinates when you set latitude and longitude decimals.  
According to wikipedia [Decimal degrees precision](https://en.wikipedia.org/wiki/Decimal_degrees#Precision) page,
5 decimals is a precision of 1 meter. Maybe you don't need more.
