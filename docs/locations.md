# Locations

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

Return a [Collection](collections.md) of Location items.

```php
// using endpoint
$locations = $oa->get('/agendas/locations', $options);

// using previous fetched OpenAgenda\Agenda object
$locations = $agenda->locations($options);
```

**array $options**

The `/agendas/locations` endpoint or `$agenda->locations()` method accept an options array with this possible keys:

* int `agenda`: Agenda id. **required** if using endpoint.
* int `limit`: How many results by request. Default `10`
* int `page`: Pagination. Require a PSR-16 configured cache.  
  You can only ask for next or previous page.  
  **Not implemented yet**.
* bool `detailed`: When `true`, get all locations fields. Default `false`
* string `search`: Search terms in title, locations and agenda keywords.
* bool `state`: When `true`, only verified locations. Default `null`.
* string `sort`: Sort results.  
  Possible values are:
    * `name_asc`: Sort by title ascending.
    * `name_desc`: Sort by title descending.
    * `created_asc`: Sort by creation date ascending.
    * `created_desc`: Sort by creation date descending.
* array `filters`:
  * DateTimeInterface|string `created_lte`: Only locations created before or at.  
    String date format: `2023-06-02T12:40:00+0100` or `2023-06-02`
  * DateTimeInterface|string `created_gte`: Only locations created at or after.  
    String date format: `2023-06-02T12:40:00+0100` or `2023-06-02`
  * DateTimeInterface|string `updated_lte`: Only locations updated before or at.  
    String date format: `2023-06-02T12:40:00+0100` or `2023-06-02` 
  * DateTimeInterface|string `updated_gte`: Only locations updated at or after.  
    String date format: `2023-06-02T12:40:00+0100` or `2023-06-02`

### Get

Get a Location object

```php
// using endpoint
$location = $oa->get('/agendas/location', $options);

// using previous fetched OpenAgenda\Agenda object
$location = $agenda->location($id)->get();
```

You can check if a location **exists** with a head request or using `exists()` method.  
Return `true` if exists neither `false`.


```php
// using endpoint
$exists = $oa->head('/agendas/location', $options);

// using previous fetched OpenAgenda\Agenda object
$exists = $agenda->location($locationId)->exists();
```

**array $options** for endpoint method
* int `agenda` (**required**): Agenda id.
* int `id` (**required**): Location id

### Create

Create a location in an agenda.  
Return a Location object with the new id.

```php
// using endpoint
$location = $oa->post('/agendas/location', $data);

// using previous fetched OpenAgenda\Agenda object
$location = $agenda->location($data)->post();
```

#### Data
Check [Location object](#schema) for more details.

### Update

Update a location in an agenda.  
Return the updated Location object.

You can use `post` or `patch` method.  
If using `post`, all accessible update fields should be sets in `$data`.  
If using `patch` only passed (or changed) fields will be updated.

You can update a location with `ext_id` instead of `id`.  
In this case, `id` should not exist in `$data`.

```php
// using endpoint
$location = $oa->post('/agendas/location', $data);
$location = $oa->patch('/agendas/location', $data);

// using previous fetched OpenAgenda\Location object
$location = $location->post($data);
$location = $location->patch($data);
```

#### Data
Check [Location object](#schema) for more details.


### Delete

Delete a location in an agenda.  
Return the deleted Location object.

You can delete a location with `ext_id` instead of `id`.  
In this case, `id` should not exist in `$data`.

```php
// using endpoint
$location = $oa->delete('/agendas/location', $data);

// using previous fetched OpenAgenda\Location object
$location = $location->delete($id);
// you can chain from Agenda too
$location = $agenda->location($id)->delete();
```


## Schema

|     Field     |       Type        | Required | Description                                                                         |
|:-------------:|:-----------------:|:--------:|:------------------------------------------------------------------------------------|
|      id       |        int        |    I     | OpenAgenda unique id. Can't be sets                                                 |
|     slug      |      string       |    I     | Unique string id                                                                    |
|    set_id     |        int        |    I     | Associated location sets id                                                         |
|  created_at   | DateTimeInterface |    I     | Creation date                                                                       |
|  updated_at   | DateTimeInterface |    I     | Update date                                                                         |
|    ext_id     |   string \| int   |    O     | External id (typically, your database location id)                                  |
|     name      |      string       |  **R**   | Location name. Max 100 characters                                                   |
|    address    |      string       |  **R**   | Full address. Max 255 characters                                                    |
|    country    |      string       |  **R**   | ISO 3166-1 Alpha 2 country code. ex: FR                                             |
|     state     |       bool        |    O     | `true`: location is verified  <br/>`false`: location need to be verified            |
|  description  |   multilingual    |    O     | Location description. Max 5000 characters                                           |
|    access     |   multilingual    |    O     | Location access instruction. Max 1000 characters                                    |
|    website    |      string       |    O     | Location website url                                                                |
|     email     |      string       |    O     | Location email                                                                      |
|     phone     |      string       |    O     | Location principal contact phone                                                    |
|     links     |       array       |    O     | Others location links (socials, ...)                                                |
|     image     |      string       |    O     | _todo_                                                                              |
| image_credits |      string       |    O     | _todo_                                                                              |
|    region     |      string       |    G     | Administrative area level 1                                                         |
|  department   |      string       |    G     | Administrative area level 2                                                         |
|   district    |      string       |    G     | Administrative area level 3                                                         |
|     city      |      string       |    G     | Locality                                                                            |
|  postal_code  |      string       |    G     | Postal code                                                                         |
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
The `multilingual` type is an array of text where key is the language in [ISO 639-1](https://fr.wikipedia.org/wiki/Liste_des_codes_ISO_639-1) format.

```php
[
    'fr' => "Le Château de Villeneuve La Comtesse est un château fort de plaine, situé au nord de la Saintonge",
]
```

### Latitude and Longitude precision
Don't forget the precision of coordinates when you set latitude and longitude decimals.  
According to wikipedia [Decimal degrees precision](https://en.wikipedia.org/wiki/Decimal_degrees#Precision) page, 
5 decimals is a precision of 1 meter. Maybe you don't need more.
