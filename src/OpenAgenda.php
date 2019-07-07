<?php
namespace OpenAgenda;

use DateTime;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use OpenAgenda\Entity\Agenda;
use OpenAgenda\Entity\Event;
use OpenAgenda\Entity\Location;

class OpenAgenda
{
    /**
     * api secret token
     * @var string|null
     */
    protected $_public = null;

    /**
     * api secret token
     * @var string|null
     */
    protected $_secret = null;

    /**
     * http client
     * @var OpenAgenda\Client|null
     */
    public $client = null;

    /**
     * locations instances
     * @var array
     */
    protected $_locations = [];

    protected $_baseUrl = null;

    /**
     * openagenda uid to publish
     * @var int|null
     */
    protected $_uid = null;

    /**
     * constuctor
     * @param string $apiSecret openagenda api secret
     */
    public function __construct($apiPublic, $apiSecret)
    {
        $this->_public = $apiPublic;

        $this->_secret = $apiSecret;

        $this->client = new Client();
        $this->client->setPublicKey($this->_public);

        $this->_initToken();
    }

    /**
     * get access token from API or local cache
     * @return string api access_token
     */
    protected function _initToken()
    {
        $accessToken = Cache::read('openagenda-token');

        if (empty($accessToken)) {
            $datas = [
                'grant_type' => 'authorization_code',
                'code' => $this->_secret,
            ];

            try {
                // use raw request to v1
                $response = $this->client->request(
                    'post',
                    'https://api.openagenda.com/v1/requestAccessToken',
                    ['form_params' => $datas]
                );
                $json = json_decode($response->getBody());

                Cache::write('openagenda-token', $json->access_token, $json->expires_in);

                $accessToken = $json->access_token;
            } catch (RequestException $e) {
                $request = $e->getRequest();
                $response = $e->getResponse();
                if ($e->hasResponse()) {
                    throw new Exception($response->getBody()->getContents());
                }
            }
        }

        $this->client->setAccessToken($accessToken);
    }

    /**
     * base url for relative links
     * @param string $url base url
     */
    public function setBaseUrl($url)
    {
        if (!substr($url, -1, 1) !== '/') {
            $url .= '/';
        }

        $this->_baseUrl = $url;

        return $this;
    }

    /**
     * set agenda uid
     * @param   int $uid agenda uid
     * @return  self
     */
    public function setAgendaUid($uid)
    {
        $this->_uid = (int)$uid;

        return $this;
    }

    public function newEvent()
    {
        $event = new Event;

        $event->baseUrl = $this->_baseUrl;

        return $event;
    }

    /**
     * get Location object with uid
     * @param  array|int $datas location id or datas
     * @param int|string|null $agendaSlug   location agenda slug or id
     * @return Location object
     */
    public function getLocation($datas, $agendaSlug = null)
    {
        // create location
        $location = new Location;

        if (is_numeric($datas)) {
            $datas = ['id' => $datas];
        } elseif (!is_array($datas)) {
            throw new Exception("invalid location data", 1);
        }

        if (!isset($datas['id'])) {
            $datas['id'] = $this->createLocation($datas, $agendaSlug);
            $location->isNew(true);
        }

        // set Id
        $location->id = $datas['id'];

        // set latitude if exists
        if (isset($datas['latitude'])) {
            $location->latitude = $datas['latitude'];
        }
        // set longitude if exists
        if (isset($datas['longitude'])) {
            $location->longitude = $datas['longitude'];
        }
        // mark as not dirty
        $location->markAsNotDirty();

        return $location;
    }

    /**
     * create location
     * @param  array $options               location options
     * @param int|string|null $agendaSlug   location agenda slug or id
     * @return int                          location id
     */
    public function createLocation($options, $agendaSlug = null)
    {
        if (!isset($options['placename'])) {
            throw new Exception("missing placename field", 1);
        }
        if (!isset($options['latitude'])) {
            throw new Exception("missing latitude field", 1);
        }
        if (!isset($options['longitude'])) {
            throw new Exception("missing longitude field", 1);
        }
        if (!isset($options['address'])) {
            throw new Exception("missing address field", 1);
        }

        // format
        $options['latitude'] = (float)$options['latitude'];
        $options['longitude'] = (float)$options['longitude'];

        // Agenda uid
        if (!is_null($agendaSlug)) {
            $agenda = $this->getAgenda($agendaSlug);
            $options['agenda_uid'] = $agenda->uid;
        }

        try {
            $response = $this->client->post('/locations', ['data' => json_encode($options)]);

            return (int)$response->uid;
        } catch (ClientException $e) {
            return false;
        }
    }

    public function getAgendaUid($slug)
    {
        if (is_numeric($slug)) {
            return new Agenda(['uid' => $slug]);
        }

        $agendaIds = Cache::read('openagenda-id');

        if (empty($agendaIds)) {
            $agendaIds = [];
        }

        if (empty($agendaIds[$slug])) {
            try {
                $response = $this->client->get('/agendas/uid/' . $slug);

                $agendaIds[$slug] = $response->data->uid;

                Cache::write('openagenda-id', $agendaIds, 86400 * 365);
            } catch (RequestException $e) {
                $request = $e->getRequest();
                if ($e->hasResponse()) {
                    $response = $e->getResponse();
                    throw new Exception($response->getBody()->getContents());
                } else {
                    throw new Exception($e->getMessage());
                }
            }
        }

        return new Agenda(['uid' => $agendaIds[$slug]]);
    }

    public function getAgendaSettings()
    {
        try {
            $response = $this->client->get('/agendas/' . $this->_uid . '/settings');

            return $response->form;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * publish event to openagenda and set uid to entity
     * @param  Event  $event entity
     * @return void|bool
     */
    public function publishEvent(Event $event)
    {
        try {
            $response = $this->client->post('/agendas/' . $this->_uid . '/events', $event->toDatas());

            $event->id = $event->uid = $response->event->uid;
        } catch (RequestException $e) {
            $request = $e->getRequest();
            $rawResponse = $e->getResponse();
            if ($e->hasResponse()) {
                return false;
            }
        } catch (ClientException $e) {
            return false;
        }
    }

    /**
     * update event to openagenda
     * @param  Event  $event entity
     * @return bool
     */
    public function updateEvent(Event $event)
    {
        if (is_null($event->uid) || $event->uid <= 0) {
            throw new Exception("event has no uid");
        }

        try {
            if (empty($event->getDirty())) {
                return true;
            }

            $response = $this->client->post('/events/' . $event->uid, $event->toDatas());

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * attach event to agenda
     * @param  Event  $event  entity
     * @param  Agenda $agenda entity
     * @return Object|false
     */
    public function attachEventToAgenda(Event $event, Agenda $agenda)
    {
        $datas = [
            'data' => json_encode(['event_uid' => $event->uid]),
        ];

        try {
            $response = $this->client->post('/agendas/' . $agenda->uid . '/events', $datas);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * detach event from agenda
     * @param  Event|int $event         id or object
     * @param  Agenda|string $agenda    name or object
     * @return bool
     */
    public function detachEventFromAgenda($event, $agenda)
    {
        if (is_numeric($event)) {
            $event = $this->getEvent((int)$event);
        }

        if (is_numeric($agenda) || is_string($agenda)) {
            $agenda = $this->getAgenda($agenda);
        }

        // not an event
        if (is_null($event->uid) || $event->uid <= 0) {
            throw new Exception("require valid event");
        }

        // not an agenda
        if (is_null($agenda->uid) || $agenda->uid <= 0) {
            throw new Exception("require valid agenda");
        }

        try {
            $response = $this->client->delete('/agendas/' . $agenda->uid . '/events/' . $event->uid);

            if ($response->code === 200) {
                return $response;
            }

            switch ($response->error) {
                case 'TO DEFINE':
                    throw new Exception("can't detach", 1);

                default:
                    return $response;
            }

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * delete event from open agenda.
     * Detach from agenda if attached
     * @param  int|Event $event entity or uid
     * @return Object           json response
     */
    public function deleteEvent($event)
    {
        if (is_numeric($event)) {
            $event = $this->getEvent((int)$event);
        }

        // not an event
        if (is_null($event->uid) || $event->uid <= 0) {
            throw new Exception("require valid event");
        }

        try {
            if (is_numeric($event->agendaUid)) {
                $this->detachEventFromAgenda($event, $event->agendaUid);
            }

            $response = $this->client->delete('/events/' . $event->uid);

            if ($response->code === 200) {
                return $response;
            }

            switch ($response->error) {
                case 'TO DEFINE':
                    throw new Exception("can't detach", 1);

                default:
                    return $response;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * get event from openagenda and return Entity
     * @param  int $eventId openagenda event id
     * @return \OpenAgenda\Entity\Event
     */
    public function getEvent($eventId)
    {
        if (!is_numeric($eventId)) {
            throw new Exception("event id should be integer", 1);
        }

        $result = $this->client->get('/events/' . (int)$eventId);

        if ($result->data === false) {
            throw new Exception("event don't exists", 1);
        }

        // transform object to array
        $arrayDatas = json_decode(json_encode($result->data), true);

        // tags as keywords
        $arrayDatas['keywords'] = $arrayDatas['tags'];

        // location
        $location = new Location;
        if (!empty($arrayDatas['locations'][0])) {
            $location->import($arrayDatas['locations'][0]);
        }

        // create event entity
        $event = new Event($arrayDatas, ['useSetters' => false, 'markClean' => true]);
        $event->id = $arrayDatas['uid'];
        $event->setLocation($location)->setDirty('location', false);

        return $event;
    }
}
