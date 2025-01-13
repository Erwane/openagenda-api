# Event

_You must create an OpenAgenda client object before querying Events._  
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
// Using OpenAgenda::events() method
$events = $oa->events(['agendaUid' => 123, 'title' => 'My event']);

// Using previous fetched OpenAgenda\Entity\Agenda object
$events = $agenda->events(['name' => 'My event']);
```

**note** for `agenda->events()` method, an OpenAgenda object should be created before. See [basics](basics.md).

**Params**:

|         field         |             type             | description                                                                                                                                                                                                                                                                                                                                                                                                                                                                |
|:---------------------:|:----------------------------:|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
|       agendaUid       |           integer            | Agenda id. **Required** if using $oa->events().                                                                                                                                                                                                                                                                                                                                                                                                                            |
|       detailed        |           boolean            | When `true`, get all events fields. Default `false`.                                                                                                                                                                                                                                                                                                                                                                                                                       |
| longDescriptionFormat |            string            | Possible values are:<br/>`markdown`: Default. As markdown<br/>`HTML`: As html.<br/>`HTMLWithEmbeds`: As html and all medias platforms links are replaced by integration content.                                                                                                                                                                                                                                                                                           |
|         size          |           integer            | How many results by request. Default `20`. Max `300`                                                                                                                                                                                                                                                                                                                                                                                                                       |
|     includeLabels     |           boolean            | Include labels in choices additional fields                                                                                                                                                                                                                                                                                                                                                                                                                                |
|     includeFields     |           string[]           | Expected fields. Default: `id` and `title`.<br/>Check [Schema](#schema) for possible fields.<br/>You can use dot notation to query location parts (ex: `location.city`).<br/>**Too much fields could slow down you requests.**                                                                                                                                                                                                                                             |
|      monolingual      |            string            | Return only this text languages. If text not found, use an existing language.                                                                                                                                                                                                                                                                                                                                                                                              |
|        removed        |           ?boolean           | `null`: include removed events.<br/>`false`: exclude removed events. (Default)<br/>`true`: only removed events.                                                                                                                                                                                                                                                                                                                                                            |
|         city          |           string[]           | Events in those cities                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
|      department       |           string[]           | Events in those departments                                                                                                                                                                                                                                                                                                                                                                                                                                                |
|        region         |            string            | Events in this region                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
|     timings[gte]      | DateTimeInterface or string* | Events with time slots after.                                                                                                                                                                                                                                                                                                                                                                                                                                              |
|     timings[lte]      | DateTimeInterface or string* | Events with time slots before.                                                                                                                                                                                                                                                                                                                                                                                                                                             |
|    updatedAt[gte]     | DateTimeInterface or string* | Events updated after.                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
|    updatedAt[lte]     | DateTimeInterface or string* | Events updated before.                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
|        search         |            string            | Search terms in event                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
|          uid          |            int[]             | Only events with those UIDs                                                                                                                                                                                                                                                                                                                                                                                                                                                |
|         slug          |           string[]           | Only events with those slugs                                                                                                                                                                                                                                                                                                                                                                                                                                               |
|       featured        |           ?boolean           | `null`: include featured events. (Default)<br/>`true`: only featured events.<br/>`false`: exclude featured events.                                                                                                                                                                                                                                                                                                                                                         |
|       relative        |           string[]           | `passed`: Passed events.<br/>`current`: Events with time slot in current time.<br/>`upcoming`: Upcoming events.                                                                                                                                                                                                                                                                                                                                                            |
|         state         |             int              | Check [event state](#state) for detail                                                                                                                                                                                                                                                                                                                                                                                                                                     |
|        keyword        |           string[]           | Events with those keywords. This is **AND** logical filter.                                                                                                                                                                                                                                                                                                                                                                                                                |
|          geo          |            array             | Events in this square.<br/>`[`<br/>`'northEast' => ['lat' => 48.9527, 'lng' => 2.4484], `<br/>`'southWest' => ['lat' => 48.8560, 'lng' => 2.1801],`<br/>`]`                                                                                                                                                                                                                                                                                                                |
|      locationUid      |            int[]             | Event in those location's id.                                                                                                                                                                                                                                                                                                                                                                                                                                              |
|     accessibility     |           string[]           | Check [accessibility list](#accessibility-list) for detail                                                                                                                                                                                                                                                                                                                                                                                                                 |
|        status         |             int              | Check [event status](#status) for detail                                                                                                                                                                                                                                                                                                                                                                                                                                   |
|         sort          |            string            | `timings.asc`: Chronological sorting according to next time slot. Futures events first, passed after.<br/>`lastTiming.asc`: Chronological sorting according to last time slot. Futures events first, passed after.<br/>`timingsWithFeatured.asc`: Featured events first, then like `timings.asc`.<br/>`lastTimingWithFeatured.asc`: Featured events first, then like `lastTiming.asc`.<br/>`updatedAt.asc`: Last updated at end.<br/>`updatedAt.desc`: Last updated first. |

**note**: For DateTime as string, could be atom string (`2024-12-23T12:34:56+00:00`) or any valid datetime format like
`2023-06-02` or `2023-06-02 12:34:56`

### Get

Get one event.  
If you want to check an event exists, you can use `exists()` method instead of `get()`.

```php
// Using OpenAgenda::event()
$exists = $oa->event(['uid' => 456, 'agendaUid' => 123])->exists();
$event = $oa->event(['uid' => 456, 'agendaUid' => 123])->get();

// Using previous fetched OpenAgenda\Entity\Agenda object
$exists = $agenda->event(['uid' => 456])->exists();
$event = $agenda->event(['uid' => 456])->get();
```

**Params**:

| field                 | type    | Required | description                                                                                                                                                                      |
|-----------------------|---------|:--------:|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| agendaUid             | integer |    Y     | Agenda uid. Required if using `$oa->event()`.                                                                                                                                    |
| uid                   | integer |    n     | Event uid.                                                                                                                                                                       |
| longDescriptionFormat | string  |    n     | Possible values are:<br/>`markdown`: Default. As markdown<br/>`HTML`: As html.<br/>`HTMLWithEmbeds`: As html and all medias platforms links are replaced by integration content. |

### Create

Create an event in an agenda.  
Return an event object with the new id.

```php
// Using OpenAgenda::event() method
$event = $oa->event($data)->create();

// Using previous fetched OpenAgenda\Entity\Agenda object
$event = $agenda->event($data)->create();
```

#### Data

Check [Event object](#schema) for more details.

### Update

Update an event.  
Return the updated Event object.

```php
// Using previous fetched OpenAgenda\Entity\Event object
$event = $oa->event(['uid' => 456, 'agendaUid' => 123])->get();
$event->state = true;
$event = $event->update();

// Or pushing data directly to OpenAgenda::event() method
$event = $oa->event(['agendaUid' => 123, 'uid' => 456, 'state' => true])->update();
```

#### Data

Check [Event object](#schema) for more details.

### Delete

Delete an event.  
Return the deleted Event object.

```php
// Using previous fetched OpenAgenda\Entity\Event object
$event = $oa->event(['uid' => 456, 'agendaUid' => 123])
    ->get()
    ->delete();

// Or through OpenAgenda::event() method
$event = $oa->event(['agendaUid' => 123, 'uid' => 456])->delete();
```

## Schema

|      Field       |       Type        | Required | Description                                                                                                                                                                    |
|:----------------:|:-----------------:|:--------:|:-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
|       uid        |        int        |    I     | OpenAgenda unique id. Can't be sets                                                                                                                                            |
|       slug       |      string       |    I     | Unique string id                                                                                                                                                               |
|    createdAt     | DateTimeInterface |    I     | Creation date                                                                                                                                                                  |
|    updatedAt     | DateTimeInterface |    I     | Update date                                                                                                                                                                    |
|      links       |     string[]      |    I     |                                                                                                                                                                                |
|     timezone     |      string       |    I     | Timezone [identifier](https://en.wikipedia.org/wiki/List_of_tz_database_time_zones)                                                                                            |
|      title       |   multilingual    |  **R**   | Event title. Max 140 characters                                                                                                                                                |
|   description    |   multilingual    |  **R**   | Short description. Max 200 characters by lang                                                                                                                                  |
| longDescription  |   multilingual    |    O     | Long description. Max 10000 characters by lang                                                                                                                                 |
|    conditions    |   multilingual    |    O     | Event access conditions (price, reservation, ...). Max 255 characters by lang                                                                                                  |
|     keywords     |   multilingual    |    O     | Keywords. List of words in one array by lang. Max 255 characters by lang.<br/>ex: `['fr' => ['musique', 'concert', 'rock']]`                                                   |
|      image       |    file \| url    |    O     | Jpeg file or absolute url to image. Max 20Mo. _todo_                                                                                                                           |
|   imageCredits   |      string       |    O     | Image credits. Max 255 characters. _todo_                                                                                                                                      |
|   registration   |     string[]      |    O     | List of registration methods like email, phone, form url, ... Max 2000 characters total                                                                                        |
|  accessibility   |      bool[]       |    O     | List of event accessibility options. Check [accessibility list](#accessibility-list) for detail                                                                                |                                                                                
|     timings      |       array       |  **R**   | Event time slots. Max 800 timings. Accept DateTimeInterface or iso datetime string.<br/>ex: `[['begin' => '2024-12-23T19:00:00+0200', 'end' => new DateTimeImmutable('now')]]` |
|       age        |       int[]       |    O     | Min/max age range of targeted participants. ex `['min' => 13, 'max' => 120]`                                                                                                   |
|   locationUid    |        int        |  **R***  | Location id. Only required for face-to-face events                                                                                                                             |
|  attendanceMode  |        int        |    O     | Event participation type.<br/>`1` (default): Offline, face-to-face.<br/>`2`: Online event, `onlineAccessLink` is required.<br/>`3`: Mixed                                      |
| onlineAccessLink |        url        |  **R***  | Event online access link. Required if `attendanceMode` is 2 or 3.                                                                                                              |
|      status      |        int        |  **R**   | Event status. Check [event status](#status) for detail                                                                                                                         |
|      state       |        int        |          | Event state. Check [event state](#state) for detail                                                                                                                            |

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
