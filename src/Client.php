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
     * OpenAgenda api access token
     *
     * @var string|null
     */
    private $_accessToken = null;

    /**
     * @var mixed|null
     */
    private $publicKey;

    /**
     * @var mixed|null
     */
    private $secretKey;

    public const USER_AGENT = 'OpenAgenda-ESdk/2.2';

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

        if (!$this->publicKey) {
            throw new OpenAgendaException('Missing `public_key`.');
        }

        if (!($this->http instanceof HttpWrapperInterface)) {
            throw new OpenAgendaException('Invalid or missing `wrapper`.');
        }
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

        if (!$payload['_success']) {
            $exception = new OpenAgendaException($payload['message'] ?? 'Request error', $status);
            $exception->setResponse($response);
            $exception->setPayload($payload);
            throw $exception;
        }

        return $payload;
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

        $response = $this->http->get((string)$uri, $params);

        return $this->payload($response);
    }

    /**
     * POST to OpenAgenda endpoint and return Entity or payload.
     *
     * @param \League\Uri\Uri|string $uri OpenAgenda uri
     * @param array $data Post data.
     * @param array $params Request params.
     * @return array
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function post($uri, array $data = [], array $params = []): array
    {
        // Add Access-Token
        $params['headers']['access-token'] = $this->getAccessToken();
        $params['headers']['nonce'] = $this->nonce();

        $response = $this->http->post((string)$uri, $data, $params);

        return $this->payload($response);
    }

    /**
     * PATCH to OpenAgenda endpoint and return Entity or payload.
     *
     * @param \League\Uri\Uri|string $uri OpenAgenda uri
     * @param array $data Post data.
     * @param array $params Request params.
     * @return array
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function patch($uri, array $data = [], array $params = []): array
    {
        // Add Access-Token
        $params['headers']['access-token'] = $this->getAccessToken();
        $params['headers']['nonce'] = $this->nonce();

        $response = $this->http->patch((string)$uri, $data, $params);

        return $this->payload($response);
    }

    /**
     * DELETE something in OpenAgenda endpoint and return Entity or payload.
     *
     * @param \League\Uri\Uri|string $uri OpenAgenda uri
     * @param array $params Post data.
     * @return array
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function delete($uri, array $params = []): array
    {
        // Add Access-Token
        $params['headers']['access-token'] = $this->getAccessToken();
        $params['headers']['nonce'] = $this->nonce();

        $response = $this->http->delete((string)$uri, $params);

        return $this->payload($response);
    }

    /**
     * Get access token from cache or a fresh one.
     *
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        // todo
        return 'accesstoken';
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
