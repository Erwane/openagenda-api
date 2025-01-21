<?php
declare(strict_types=1);

/**
 * OpenAgenda API client.
 * Copyright (c) Erwane BRETON
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Erwane BRETON
 * @see         https://github.com/Erwane/openagenda-api
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace OpenAgenda;

use OpenAgenda\Endpoint\Auth;
use OpenAgenda\Wrapper\HttpWrapper;
use OpenAgenda\Wrapper\HttpWrapperInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * OpenAgenda client
 */
class Client
{
    /**
     * OpenAgenda api base url
     *
     * @var string
     */
    protected string $url = 'https://api.openagenda.com/v2';

    /**
     * @var \OpenAgenda\Wrapper\HttpWrapper
     */
    protected HttpWrapper $http;

    /**
     * @var \Psr\SimpleCache\CacheInterface|null
     */
    protected ?CacheInterface $cache = null;

    /**
     * @var string
     */
    private string $publicKey;

    /**
     * @var mixed|null
     */
    private ?string $secretKey;

    /**
     * Construct OpenAgenda Client.
     *
     * @param array $config OpenAgenda client config.
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function __construct(array $config = [])
    {
        $this->secretKey = $config['secret_key'] ?? null;
        $this->cache = $config['cache'] ?? null;

        if (empty($config['public_key'])) {
            throw new OpenAgendaException('Missing `public_key`.');
        }

        if (!isset($config['wrapper']) || !($config['wrapper'] instanceof HttpWrapperInterface)) {
            throw new OpenAgendaException('Invalid or missing `wrapper`.');
        }

        $this->publicKey = $config['public_key'];
        $this->http = $config['wrapper'];
    }

    /**
     * Get PSR-18 Http client wrapper.
     *
     * @return \OpenAgenda\Wrapper\HttpWrapper
     */
    public function getWrapper(): HttpWrapper
    {
        return $this->http;
    }

    /**
     * Return response as an array.
     *
     * @param \Psr\Http\Message\ResponseInterface $response Http client response.
     * @return array
     * @throws \OpenAgenda\OpenAgendaException
     */
    protected function payload(ResponseInterface $response): array
    {
        $status = $response->getStatusCode();
        $payload = [
            '_status' => $status,
            '_success' => $status >= 200 && $status < 300,
        ];

        $body = (string)$response->getBody();
        $json = json_decode($body, true);
        if ($json) {
            $payload += $json;
        }

        if (!$payload['_success'] || (isset($payload['success']) && !$payload['success'])) {
            $exception = new OpenAgendaException($payload['message'] ?? 'Request error', $status);
            $exception->setResponse($response);
            $exception->setPayload($payload);
            throw $exception;
        }

        return $payload;
    }

    /**
     * Send request to HttpWrapper.
     * Catch HttpWrapperException.
     *
     * @param string $method Wrapper method
     * @param array $args Wrapper args
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\OpenAgendaException
     */
    protected function _doRequest(string $method, array $args = [])
    {
        try {
            return $this->http->$method(...$args);
        } catch (Wrapper\HttpWrapperException $e) {
            $new = new OpenAgendaException($e->getMessage(), $e->getCode(), $e);
            if ($e->getResponse()) {
                $new->setResponse($e->getResponse());
            }
            throw $new;
        }
    }

    /**
     * Query OpenAgenda endpoint with a HEAD request and return Response status code.
     *
     * @param string $url OpenAgenda url
     * @param array $params Request params
     * @return \Psr\Http\Message\ResponseInterface|int
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function head(string $url, array $params = [])
    {
        $raw = $params['_raw'] ?? null;
        unset($params['_raw']);

        $params['headers']['key'] = $this->publicKey;

        $response = $this->_doRequest('head', [$url, $params]);

        if ($raw) {
            return $response;
        }

        return $response->getStatusCode();
    }

    /**
     * Query OpenAgenda endpoint and return Collection or Entity
     *
     * @param string $url OpenAgenda url
     * @param array $params Request params
     * @return \Psr\Http\Message\ResponseInterface|array
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function get(string $url, array $params = [])
    {
        $raw = $params['_raw'] ?? null;
        unset($params['_raw']);

        // Add key
        $params['headers']['key'] = $this->publicKey;

        $response = $this->_doRequest('get', [$url, $params]);

        if ($raw) {
            return $response;
        }

        return $this->payload($response);
    }

    /**
     * POST to OpenAgenda endpoint and return Entity or payload.
     *
     * @param string $url OpenAgenda url
     * @param array $data POST data.
     * @param array $params Request params.
     * @return \Psr\Http\Message\ResponseInterface|array
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function post(string $url, array $data = [], array $params = [])
    {
        $raw = $params['_raw'] ?? null;
        unset($params['_raw']);

        $params = $this->_addAuthenticationHeaders($params);

        $response = $this->_doRequest('post', [$url, $data, $params]);

        if ($raw) {
            return $response;
        }

        return $this->payload($response);
    }

    /**
     * PATCH to OpenAgenda endpoint and return Entity or payload.
     *
     * @param string $url OpenAgenda uri
     * @param array $data PATCH data.
     * @param array $params Request params.
     * @return \Psr\Http\Message\ResponseInterface|array
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function patch(string $url, array $data = [], array $params = [])
    {
        $raw = $params['_raw'] ?? null;
        unset($params['_raw']);

        $params = $this->_addAuthenticationHeaders($params);

        $response = $this->_doRequest('patch', [$url, $data, $params]);

        if ($raw) {
            return $response;
        }

        return $this->payload($response);
    }

    /**
     * DELETE something in OpenAgenda endpoint and return Entity or payload.
     *
     * @param string $url OpenAgenda uri
     * @param array $params Request params.
     * @return \Psr\Http\Message\ResponseInterface|array
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function delete(string $url, array $params = [])
    {
        $raw = $params['_raw'] ?? null;
        unset($params['_raw']);

        $params = $this->_addAuthenticationHeaders($params);

        $response = $this->_doRequest('delete', [$url, $params]);

        if ($raw) {
            return $response;
        }

        return $this->payload($response);
    }

    /**
     * Add write authentication headers.
     *
     * @param array $params Request params
     * @return array
     * @throws \OpenAgenda\OpenAgendaException
     */
    protected function _addAuthenticationHeaders(array $params): array
    {
        $params['headers']['access-token'] = $this->getAccessToken();
        $params['headers']['nonce'] = $this->nonce();

        return $params;
    }

    /**
     * Get access token from cache or a fresh one.
     *
     * @return string|null
     * @throws \OpenAgenda\OpenAgendaException
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function getAccessToken(): ?string
    {
        $token = null;
        $cacheKey = 'openagenda_api_access_token';
        if ($this->cache) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $token = $this->cache->get($cacheKey);
        }
        if (!$token) {
            if (!$this->secretKey) {
                throw new OpenAgendaException('Missing secret_key');
            }
            $endpoint = new Auth();
            $url = $endpoint->getUrl('post');

            $response = $this->_doRequest('post', [
                $url,
                [
                    'grant_type' => 'authorization_code',
                    'code' => $this->secretKey,
                ],
            ]);

            $payload = $this->payload($response);

            $token = $payload['access_token'] ?? null;
            if ($this->cache && !empty($payload['expires_in'])) {
                /** @noinspection PhpUnhandledExceptionInspection */
                $this->cache->set($cacheKey, $token, $payload['expires_in']);
            }
        }

        return $token;
    }

    /**
     * Generate nonce random int
     *
     * @return int
     */
    public function nonce(): int
    {
        return intval(microtime(true) * 100000);
    }
}
