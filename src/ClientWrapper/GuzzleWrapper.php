<?php
declare(strict_types=1);

namespace OpenAgenda\ClientWrapper;

use OpenAgenda\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleWrapper extends ClientWrapper
{
    /**
     * @var \GuzzleHttp\Client|\Psr\Http\Client\ClientInterface
     */
    protected $http;

    /**
     * @inheritDoc
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->http->sendRequest($request);
    }

    /**
     * Prepare request options.
     *
     * @param array $options Request options.
     * @param array $data Request data.
     * @return array
     */
    public function prepareOptions(array $options, array $data = []): array
    {
        $options['allow_redirects'] = false;
        $options['headers']['Accept'] = 'application/json';
        $options['headers']['User-Agent'] = Client::USER_AGENT;

        if ($data) {
            // Has resource (file)
            $hasResource = array_filter($data, function ($value) {
                return is_resource($value);
            });

            if ($hasResource) {
                $options['multipart'] = [];
                foreach ($data as $key => $value) {
                    $options['multipart'][] = [
                        'name' => $key,
                        'contents' => $value,
                    ];
                }
            } else {
                $options['json'] = $data;
            }
        }

        return $options;
    }

    /**
     * @inheritDoc
     */
    public function head($uri, array $options = []): ResponseInterface
    {
        $uri = $this->buildUri($uri);
        $options = $this->prepareOptions($options);

        return $this->http->request('HEAD', (string)$uri, $options);
    }

    /**
     * @inheritDoc
     */
    public function get($uri, array $data = [], array $options = []): ResponseInterface
    {
        $uri = $this->buildUri($uri);
        $options = $this->prepareOptions($options);

        return $this->http->request('GET', (string)$uri, $options);
    }

    /**
     * @inheritDoc
     */
    public function post($uri, array $data, array $options = []): ResponseInterface
    {
        $uri = $this->buildUri($uri);
        $options = $this->prepareOptions($options, $data);

        return $this->http->request('POST', (string)$uri, $options);
    }

    /**
     * @inheritDoc
     */
    public function patch($uri, array $data, array $options = []): ResponseInterface
    {
        $uri = $this->buildUri($uri);
        $options = $this->prepareOptions($options, $data);

        return $this->http->request('PATCH', (string)$uri, $options);
    }

    /**
     * @inheritDoc
     */
    public function delete($uri, array $data, array $options = []): ResponseInterface
    {
        $uri = $this->buildUri($uri);
        $options = $this->prepareOptions($options, $data);

        return $this->http->request('DELETE', (string)$uri, $options);
    }
}
