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
     * constuctor
     * @param string $apiSecret openagenda api secret
     */
    public function __construct($apiPublic, $apiSecret)
    {
        $this->_public = $apiPublic;

        $this->_secret = $apiSecret;

        $this->client = new Client;
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
            $options = [
                'grant_type' => 'authorization_code',
                'code' => $this->_secret,
            ];

            try {
                $response = $this->client->post('/requestAccessToken', $options);

                Cache::write('openagenda-token', $response->access_token, $response->expires_in);

                $accessToken = $response->access_token;
            } catch (\GuzzleHttp\Exception\RequestException $e) {
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

    public function newEvent()
    {
        $event = new Event;

        $event->baseUrl = $this->_baseUrl;

        return $event;
    }

    /**
     * get Location object with uid
     * @param  array|int $datas location id or datas
     * @return Location object
     */
    public function newLocation($datas)
    {
        // create location
        $location = new Location;

        if (is_numeric($datas)) {
            $datas = ['id' => $datas];
        } elseif (!is_array($datas)) {
            throw new Exception("invalid location data", 1);
        }

        if (!isset($datas['id'])) {
            $datas['id'] = $this->createLocation($datas);
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
     * @param  array $options   location options
     * @return int              location id
     */
    public function createLocation($options)
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

        try {
            $response = $this->client->post('/locations', ['data' => json_encode($options)]);

            return (int)$response->uid;
        } catch (ClientException $e) {
            return false;
        }
    }

    public function getAgenda($slug)
    {
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

    /**
     * public event to openagenda and set uid to entity
     * @param  Event  $event entity
     * @return void|bool
     */
    public function publish(Event $event)
    {
        try {
            $response = $this->client->post('/events', $event->toArray());

            $event->setId($response->uid);
        } catch (RequestException $e) {
            var_dump($e);
            exit;
            $request = $e->getRequest();
            $rawResponse = $e->getResponse();
            if ($e->hasResponse()) {
                return false;
            }
        } catch (ClientException $e) {
            var_dump($e);
            exit;
            return false;
        }
    }


    /**
     * public event to openagenda and set uid to entity
     * @param  Event  $event entity
     * @return void|bool
     */
    public function update(Event $event)
    {
        if (is_null($event->uid) || $event->uid <= 0) {
            throw new Exception("event has no uid");
        }

        try {
            // get
            $datas = $event->getDirtyArray();

            if (empty($event->getDirtyArray())) {
                return true;
            }

            debug($event->toArray());
            exit;
            $response = $this->client->post('/events/' . $event->uid, $event->toArray());

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function attachEventToAgenda(Event $event, Agenda $agenda)
    {
        $datas = [
            'data' => json_encode([
                'event_uid' => $event->uid,
                'category' => $agenda->category,
            ]),
        ];

        try {
            $response = $this->client->post('/agendas/' . $agenda->uid . '/events', $datas);
        } catch (RequestException $e) {
            return false;
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

        // transform object to array
        $arrayDatas = json_decode(json_encode($result->data), true);

        // tags as keywords
        $arrayDatas['keywords'] = $arrayDatas['tags'];

        // location
        $location = new Location;
        $location->import($arrayDatas['locations'][0]);

        // create event entity
        $event = new Event($arrayDatas, ['useSetters' => false, 'markClean' => true]);
        $event->setUid($arrayDatas['uid']);
        $event->setLocation($location);
        $event->setDirty('location', false);

        return $event;
    }
}
