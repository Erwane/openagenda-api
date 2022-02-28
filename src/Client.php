<?php
declare(strict_types=1);

namespace OpenAgenda;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class Client extends GuzzleClient
{
    /**
     * api base url
     *
     * @var string
     */
    protected $_url = 'https://api.openagenda.com/v2';

    /**
     * public key
     *
     * @var null
     */
    protected $_public = null;

    /**
     * api access token
     *
     * @var string|null
     */
    private $_accessToken = null;

    private $_userAgent = 'Openagenda-api/2.1.0';

    /**
     * set public key
     *
     * @param string $key public key
     */
    public function setPublicKey(string $key)
    {
        $this->_public = $key;
    }

    /**
     * set access token
     *
     * @param string $token access token
     * @return $this
     */
    public function setAccessToken(string $token)
    {
        $this->_accessToken = $token;

        return $this;
    }

    /**
     * do a post request and return object from json
     *
     * @param string $uri Openagenda endpoint
     * @param array $options Client options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\OpenAgendaException
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function get($uri, array $options = []): ResponseInterface
    {
        return $this->doRequest(function ($u, $o) {
            $query = $o['query'] ?? [];

            $query += ['key' => $this->_public];

            $o['query'] = $query;

            return $this->request('GET', $u, $o);
        }, $uri, $options);
    }

    /**
     * @param callable $callable
     * @param $uri
     * @param $options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\OpenAgendaException
     * @noinspection PhpMultipleClassDeclarationsInspection
     */
    protected function doRequest(callable $callable, $uri, $options): ResponseInterface
    {
        try {
            if (!($uri instanceof UriInterface)) {
                $uri = new Uri($this->_url . $uri);
            }

            if (!isset($options['headers'])) {
                $options['headers'] = [];
            }

            $found = false;
            foreach (array_keys($options['headers']) as $name) {
                if (strtolower($name) === 'user-agent') {
                    $options['headers'][$name] = $this->_userAgent;
                    $found = true;

                    break;
                }
            }
            if (!$found) {
                $options['headers']['User-Agent'] = $this->_userAgent;
            }

            return $callable($uri, $options);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $json = json_decode((string)$response->getBody(), true);
                $code = $response->getStatusCode();
                $message = $json['message'];
            } else {
                $code = $e->getCode();
                $message = $e->getMessage();
            }

            throw new OpenAgendaException($message, $code);
        } catch (GuzzleException $e) {
            throw new OpenAgendaException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * do a post request and return object from json
     *
     * @param string $uri Openagenda endpoint
     * @param array $options Client options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\OpenAgendaException
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function post($uri, array $options = []): ResponseInterface
    {
        return $this->doRequest(function ($u, $o) {
            $formData = $this->_optionsToMultipart($o['data'] ?? []);
            unset($o['data']);

            if ($this->_accessToken) {
                $formData[] = [
                    'name' => 'access_token',
                    'contents' => $this->_accessToken,
                ];
                $formData[] = [
                    'name' => 'nonce',
                    'contents' => $this->nonce(),
                ];
            }

            $o['multipart'] = $formData;

            return $this->request('POST', $u, $o);
        }, $uri, $options);
    }

    /**
     * transform a $array options to multipart array
     *
     * @param string|array $data options and datas
     * @return array
     */
    private function _optionsToMultipart($data)
    {
        $return = [];

        if (!empty($data['image'])) {
            $return[] = ['name' => 'image', 'contents' => $data['image'], 'Content-type' => 'multipart/form-data'];
            unset($data['image']);
        }

        $return[] = ['name' => 'data', 'contents' => is_array($data) ? json_encode($data) : $data];

        return $return;
    }

    /**
     * Generate random int
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function nonce()
    {
        try {
            return random_int(1000000, 9999999);
        } catch (Exception $e) {
            return mt_rand(1000000, 9999999);
        }
    }

    /**
     * do a delete request and return object from json
     *
     * @param string $uri Openagenda endpoint
     * @param array $options Client options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\OpenAgendaException
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function delete($uri, array $options = []): ResponseInterface
    {
        return $this->doRequest(function ($u, $o) {
            $headers = $o['headers'] ?? [];

            $headers += [
                'nonce' => $this->nonce(),
                'access-token' => $this->_accessToken,
            ];

            $o['headers'] = $headers;

            return $this->request('DELETE', $u, $o);
        }, $uri, $options);
    }
}
