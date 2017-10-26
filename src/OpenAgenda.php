<?php
namespace OpenAgenda;

use \DateTime;
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
                    throw new \Exception($response->getBody()->getContents());
                }
            }
        }

        $this->client->setAccessToken($accessToken);
    }

    public function newEvent()
    {
        return new Event;
    }

    /**
     * get Location object with uid
     * @param  array|int $options location id or params
     * @return Location object
     */
    public function newLocation($options)
    {
        if (is_numeric($options) || !empty($options['id'])) {
            // have location id, create from it
            $locationId = is_numeric($options) ? (int)$options : (int)$options['id'];
        } elseif (is_array($options)) {
            $locationId = $this->getLocationId($options);
        }

        if (empty($this->_locations[$locationId])) {
            $this->_locations[$locationId] = new Location(['id' => $locationId]);
        }

        return $this->_locations[$locationId];
    }

    /**
     * create and get location id from API
     * @param  array $options   location options
     * @return int              location id
     */
    public function getLocationId($options)
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
                debug($response);

                $agendaIds[$slug] = $response->data->uid;

                Cache::write('openagenda-id', $agendaIds, 86400 * 365);
            } catch (RequestException $e) {
                $request = $e->getRequest();
                if ($e->hasResponse()) {
                    $response = $e->getResponse();
                    throw new \Exception($response->getBody()->getContents());
                } else {
                    throw new \Exception($e->getMessage());
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

            debug($response);
        } catch (RequestException $e) {
            debug($e);
            return false;
        }
    }
}