<?php
namespace OpenAgenda;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

class Client extends GuzzleClient
{
    /**
     * api base url
     * @var url
     */
    protected $_url = 'https://api.openagenda.com/v1';

    /**
     * api access token
     * @var string|null
     */
    private $_accessToken = null;

    /**
     * set access token
     * @param string $token access token
     */
    public function setAccessToken($token)
    {
        $this->_accessToken = $token;
    }

    /**
     * do a post request and return object from json
     * @param  string  $url         api url ex : /accessToken
     * @param  array  $datas      data
     * @param  bool $accessToken    add access token to options
     * @return StdClass
     */
    public function post($url, $datas, $accessToken = true)
    {
        try {
            $params = [
                'multipart' => $this->_optionsToMultipart($datas),
            ];

            if ($accessToken) {
                $params['multipart'][] = [
                    'name' => 'access_token',
                    'contents' => $this->_accessToken,
                ];
            }

            $rawResponse = $this->request('post', $this->_url . $url, $params);
            $response = json_decode((string)$rawResponse->getBody()->getContents());

            return $response;
        } catch (ClientException $e) {
            $response = json_decode((string)$e->getResponse()->getBody()->getContents());
            throw new \Exception($response->message, $response->code);
        }
    }

    /**
     * transform a $array options to multipard array
     * @param  array  $array options and datas
     * @return array
     */
    private function _optionsToMultipart(array $array)
    {
        $return = [];
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                $return[] = [
                    'name' => $key,
                    'contents' => $value,
                ];
            }
        }

        return $return;
    }
}
