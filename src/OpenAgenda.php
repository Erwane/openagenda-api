<?php
namespace OpenAgenda;

use OpenAgenda\Cache;

class OpenAgenda
{
    /**
     * api secret token
     * @var string|null
     */
    protected $_secret = null;

    /**
     * guzzleClient
     * @var GuzzleHttp\Client|null
     */
    public $guzzleClient = null;

    /**
     * constuctor
     * @param string $apiSecret openagenda api secret
     */
    public function __construct($apiSecret)
    {
        $this->_secret = $apiSecret;

        $this->guzzleClient = new \GuzzleHttp\Client;

        $this->_initToken();
    }

    /**
     * get access token from API or local cache
     * @return string api access_token
     */
    protected function _initToken()
    {
        $cache = Cache::read('openagenda-token');
    }
}