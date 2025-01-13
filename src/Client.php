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

use Cake\Chronos\Chronos;
use OpenAgenda\Endpoint\Auth;
use OpenAgenda\Wrapper\HttpWrapper;
use OpenAgenda\Wrapper\HttpWrapperInterface;
use Psr\Http\Message\ResponseInterface;

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
    protected $url = 'https://api.openagenda.com/v2';

    /**
     * @var \OpenAgenda\Wrapper\HttpWrapper|null
     */
    protected $http;

    /**
     * @var \Psr\SimpleCache\CacheInterface|null
     */
    protected $cache;

    /**
     * @var mixed|null
     */
    private $publicKey;

    /**
     * @var mixed|null
     */
    private $secretKey;

    /**
     * Construct OpenAgenda Client.
     *
     * @param array $config OpenAgenda client config.
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function __construct(array $config = [])
    {
        $this->publicKey = $config['public_key'] ?? null;
        $this->secretKey = $config['secret_key'] ?? null;
        $this->http = $config['wrapper'] ?? null;
        $this->cache = $config['cache'] ?? null;

        if (!$this->publicKey) {
            throw new OpenAgendaException('Missing `public_key`.');
        }

        if (!($this->http instanceof HttpWrapperInterface)) {
            throw new OpenAgendaException('Invalid or missing `wrapper`.');
        }
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
     * Query OpenAgenda endpoint with a HEAD request and return Response status code.
     *
     * @param \League\Uri\Uri|string $uri OpenAgenda uri
     * @param array $params Request params
     * @return int
     * @throws \OpenAgenda\Wrapper\HttpWrapperException
     */
    public function head($uri, array $params = []): int
    {
        $params['headers']['key'] = $this->publicKey;

        // todo this could throw exception
        $response = $this->http->head((string)$uri, $params);

        return $response->getStatusCode();
    }

    /**
     * Query OpenAgenda endpoint and return Collection or Entity
     *
     * @param \League\Uri\Uri|string $uri OpenAgenda uri
     * @param array $params Request params
     * @return array
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function get($uri, array $params = []): array
    {
        // Add key
        $params['headers']['key'] = $this->publicKey;

        // todo this could throw exception
        $response = $this->http->get((string)$uri, $params);

        return $this->payload($response);
    }

    /**
     * POST to OpenAgenda endpoint and return Entity or payload.
     *
     * @param \League\Uri\Uri|string $uri OpenAgenda uri
     * @param array $data POST data.
     * @param array $params Request params.
     * @return array
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function post($uri, array $data = [], array $params = []): array
    {
        $params = $this->_addAuthenticationHeaders($params);

        // todo this could throw exception
        $response = $this->http->post((string)$uri, $data, $params);

        return $this->payload($response);
    }

    /**
     * PATCH to OpenAgenda endpoint and return Entity or payload.
     *
     * @param \League\Uri\Uri|string $uri OpenAgenda uri
     * @param array $data PATCH data.
     * @param array $params Request params.
     * @return array
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function patch($uri, array $data = [], array $params = []): array
    {
        $params = $this->_addAuthenticationHeaders($params);

        // todo this could throw exception
        $response = $this->http->patch((string)$uri, $data, $params);

        return $this->payload($response);
    }

    /**
     * DELETE something in OpenAgenda endpoint and return Entity or payload.
     *
     * @param \League\Uri\Uri|string $uri OpenAgenda uri
     * @param array $params Request params.
     * @return array
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function delete($uri, array $params = []): array
    {
        $params = $this->_addAuthenticationHeaders($params);

        // todo this could throw exception
        $response = $this->http->delete((string)$uri, $params);

        return $this->payload($response);
    }

    /**
     * Add write authentication headers.
     *
     * @param array $params Request params
     * @return array
     * @throws \OpenAgenda\OpenAgendaException
     */
    protected function _addAuthenticationHeaders(array $params)
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
            $uri = $endpoint->getUri('post');

            // todo this could throw exception
            $response = $this->http->post((string)$uri, [
                'grant_type' => 'authorization_code',
                'code' => $this->secretKey,
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
        $time = Chronos::now();

        return intval($time->timestamp . $time->microsecond);
    }
}
