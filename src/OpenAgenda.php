<?php
namespace OpenAgenda;

use \DateTime;
use GuzzleHttp\Exception\ClientException;
use OpenAgenda\Entity\Event;
use OpenAgenda\Entity\Location;

class OpenAgenda
{
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
    public function __construct($apiSecret)
    {
        $this->_secret = $apiSecret;

        $this->client = new Client;

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
}