<?php
namespace OpenAgenda;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

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
    public function __construct(string $apiSecret)
    {
        $this->_secret = $apiSecret;

        $this->guzzleClient = new \GuzzleHttp\Client;
    }
}