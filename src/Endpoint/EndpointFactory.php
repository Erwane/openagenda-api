<?php
declare(strict_types=1);

namespace OpenAgenda\Endpoint;

use OpenAgenda\Client;

class EndpointFactory
{
    /**
     * Create endpoint.
     *
     * @param \OpenAgenda\Client $client OpenAgenda client.
     * @param string $path Relative endpoint path.
     * @param array $params Endpoint params.
     * @return \OpenAgenda\Endpoint\Endpoint
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     */
    public static function make(Client $client, string $path, array $params = []): Endpoint
    {
        switch ($path) {
            case '/agendas':
            case '/agenda':
                return new Agendas($client, $params);
            default:
                throw new UnknownEndpointException($path);
        }
    }
}
