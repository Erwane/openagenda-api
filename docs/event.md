# Event

_You must create an OpenAgenda client object before querying locations._  
_See [basics](basics.md) of how to do this._

## Summary

* [index](#index)
* [get or exists](#get)
* [create](#create)
* [update](#update)
* [delete](#delete)
* [Event object](#schema)

## Methods and endpoints

### Index

List events for an agenda.

Return a [Collection](collection.md) of Event items.

```php
// Using previous fetched OpenAgenda\Entity\Agenda object
$events = $agenda->events($options);
```

**array $options**

The `/events` endpoint or `$agenda->events()` method accept an options array with this possible keys:

* int `agenda`: Agenda id. **required** if using endpoint.
* int `limit`: How many results by request. Default `20`. Max `300`
* int `page`: Pagination. Require a PSR-16 configured cache.  
  You can only ask for next or previous page.  
  **Not implemented yet**.
* bool `detailed`: When `true`, get all events fields. Default `false`.
* string `fields`: Expected fields. Default: `id` and `title`.  
  Check [Schema](#schema) for possible fields.  
  You can use dot notation to query location parts (ex: `location.city`).  
  **Too much fields could slow down you requests.**
* string `description_format`: `longDescription` rendering format.  
  Possible values are:
    * `markdown`: Default. As markdown
    * `html`: As html.
    * `html_embeds`: As html and all medias platforms links are replaced by integration content.
* string `monolingual`: Return only this text languages. If text not found, use an existing language.
* string `sort`: Sort results.  
  Possible values are:
    * `timings_first_asc`: Chronological sorting according to next time slot.  
      Futures events first, passed after.
    * `timings_last_asc`: Chronological sorting according to last time slot.  
      Futures events first, passed after.
    * `timings_first_featured_asc`: Featured events first, then like `timings_first_asc`.
    * `timings_last_featured_asc`: Featured events first, then like `timings_last_asc`.
    * `updated_asc`: Last updated at end.
    * `updated_desc`: Last updated first.
* array `filters`:
    * bool | null `featured`:  
      `null`: include featured events. (Default)
      `true`: only featured events.
      `false`: exclude featured events.
    * bool | null `removed`:  
      `false`: exclude removed events. (Default)
      `null`: include removed events.
      `true`: only removed events.
    * string `search`: Search terms in event.
    * string|array `keyword`: Events with those keywords. **AND** logical filter.
    * string|array `id`: Only events with those ids.
    * string|array `slug`: Only events with those slugs.
    * string|array `city`:
    * string|array `department`:
    * string|array `region`:
    * DateTimeInterface|string `timings_lte`: Events with time slots before.  
      String date format: `2023-06-02T12:40:00+0100` or `2023-06-02`
    * DateTimeInterface|string `timings_gte`: Events with time slots after.  
      String date format: `2023-06-02T12:40:00+0100` or `2023-06-02`
    * DateTimeInterface|string `updated_lte`: Events updated before.  
      String date format: `2023-06-02T12:40:00+0100` or `2023-06-02`
    * DateTimeInterface|string `updated_gte`: Events updated after.  
      String date format: `2023-06-02T12:40:00+0100` or `2023-06-02`
    * string|array `relative`:  
      `passed`: Passed events. (Default)
      `current`: Events with time slot in current time. (Default)
      `upcoming`: Upcoming events. (Default)
    * array `geo`: Events in this square.  
      `ne`: `['lat' => 48.9527, 'lng' => 2.4484]`  
      `sw`: `['lat' => 48.8560, 'lng' => 2.1801]`
    * string|array `locationUid`: Event in those location's id.
    * string|array `accessibility`: Check [accessibility list](#accessibility-list) for detail
    * string|array `status`: Check [event status](#status) for detail
    * int `state`: Check [event state](#state) for detail

### Get

Get an Event

```php
// Using previous fetched OpenAgenda\Entity\Agenda object
$event = $agenda->event($options)->get();
```

**array $options**

* int `agenda` (**required**): Agenda id. _Endpoint method only_.
* int `id` (**required**): Event id.
* string `description_format`: `longDescription` rendering format.    
  Possible values are:
    * `markdown`: Default.
    * `html`: As html.
    * `html_embeds`: As html and all medias platforms links are replaced by integration content.

### Create

Create an event in an agenda.  
Return an event object with the new id.

```php
// Using OpenAgenda todo
$event = $oa->post('/event', $data);

// Using previous fetched OpenAgenda\Entity\Agenda object
$event = $agenda->event($data)->post();
```

#### Data

Check [Event object](#schema) for more details.

### Update

Update an event.  
Return the updated Event object.

You can use `post` or `patch` method.  
If using `post`, all accessible update fields should be sets in `$data`.  
If using `patch` only passed (or changed) fields will be updated.

```php
// Using endpoint todo
$event = $oa->post('/event', $data);
$event = $oa->patch('/event', $data);

// Using previous fetched OpenAgenda\Entity\Event object
$event = $event->post($data);
$event = $event->patch($data);
```

#### Data

Check [Event object](#schema) for more details.

### Delete

Delete an event.  
Return the deleted Event object.

```php
// Using previous fetched OpenAgenda\Entity\Location object
$event = $event->delete($eventUid);
// you can chain from Agenda too
$event = $agenda->event($eventUid)->delete();
```

## Schema

|       Field        |       Type        | Required | Description                                                                                                                                                                    |
|:------------------:|:-----------------:|:--------:|:-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
|         id         |        int        |    I     | OpenAgenda unique id. Can't be sets                                                                                                                                            |
|        slug        |      string       |    I     | Unique string id                                                                                                                                                               |
|     createdAt     | DateTimeInterface |    I     | Creation date                                                                                                                                                                  |
|     updatedAt     | DateTimeInterface |    I     | Update date                                                                                                                                                                    |
|       links        |                   |    I     |                                                                                                                                                                                |
|      timezone      |      string       |    I     | Timezone [identifier](https://en.wikipedia.org/wiki/List_of_tz_database_time_zones)                                                                                            |
|       title        |   multilingual    |  **R**   | Event title. Max 140 characters                                                                                                                                                |
|    description     |   multilingual    |  **R**   | Short description. Max 200 characters by lang                                                                                                                                  |
|  longDescription  |   multilingual    |    O     | Long description. Max 10000 characters by lang                                                                                                                                 |
|     conditions     |   multilingual    |    O     | Event access conditions (price, reservation, ...). Max 255 characters by lang                                                                                                  |
|      keywords      |   multilingual    |    O     | Keywords. List of words in one array by lang. Max 255 characters by lang.<br/>ex: `['fr' => ['musique', 'concert', 'rock']]`                                                   |
|       image        |    file \| url    |    O     | Jpeg file or absolute url to image. Max 20Mo. _todo_                                                                                                                           |
|   imageCredits    |      string       |    O     | Image credits. Max 255 characters. _todo_                                                                                                                                      |
|    registration    |     string[]      |    O     | List of registration methods like email, phone, form url, ... Max 2000 characters total                                                                                        |
|   accessibility    |      bool[]       |    O     | List of event accessibility options. Check [accessibility list](#accessibility-list) for detail                                                                                |                                                                                
|      timings       |       array       |  **R**   | Event time slots. Max 800 timings. Accept DateTimeInterface or iso datetime string.<br/>ex: `[['begin' => '2024-12-23T19:00:00+0200', 'end' => new DateTimeImmutable('now')]]` |
|        age         |       int[]       |    O     | Min/max age range of targeted participants. ex `['min' => 13, 'max' => 120]`                                                                                                   |
|    locationUid     |        int        |  **R***  | Location id. Only required for face-to-face events                                                                                                                             |
|  attendanceMode   |        int        |    O     | Event participation type.<br/>`1` (default): Offline, face-to-face.<br/>`2`: Online event, `onlineAccessLink` is required.<br/>`3`: Mixed                                    |
| onlineAccessLink |        url        |  **R***  | Event online access link. Required if `attendanceMode` is 2 or 3.                                                                                                             |
|       status       |        int        |  **R**   | Event status. Check [event status](#status) for detail                                                                                                                         |
|       state        |        int        |          | Event state. Check [event state](#state) for detail                                                                                                                            |

### Status

Event status:

* `OpenAgenda\Entity\Event::STATUS_REFUSED` (`-1`): Refused.
* `OpenAgenda\Entity\Event::STATUS_MODERATION` (`0`): To moderate.
* `OpenAgenda\Entity\Event::STATUS_READY` (`1`): Ready to published.
* `OpenAgenda\Entity\Event::STATUS_PUBLISHED` (`2`): Published. Event has public visibility.

### State

Event state:

* `OpenAgenda\Entity\Event::STATE_SCHEDULED` (`1`): Event scheduled (default).
* `OpenAgenda\Entity\Event::STATE_RESCHEDULED` (`2`): The time slots changed and event is re-scheduled.
* `OpenAgenda\Entity\Event::STATE_ONLINE` (`3`): The face-to-face event switched to an online event.
* `OpenAgenda\Entity\Event::STATE_DEFERRED` (`4`): Event deferred, new timings unknowns.
* `OpenAgenda\Entity\Event::STATE_FULL` (`5`): Event is full.
* `OpenAgenda\Entity\Event::STATE_CANCELED` (`6`): Event canceled and not re-scheduled.

### Accessibility list

Event accessibility:

* `OpenAgenda\Entity\Event::ACCESS_HI` (`hi`): Hearing impairment.
* `OpenAgenda\Entity\Event::ACCESS_II` (`ii`): Visual impairment.
* `OpenAgenda\Entity\Event::ACCESS_VI` (`vi`): Psychic impairment.
* `OpenAgenda\Entity\Event::ACCESS_MI` (`mi`): Motor impairment.
* `OpenAgenda\Entity\Event::ACCESS_PI` (`pi`): Intellectual impairment.
