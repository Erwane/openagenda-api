<?php
namespace OpenAgenda;

use \DateTime;

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
}