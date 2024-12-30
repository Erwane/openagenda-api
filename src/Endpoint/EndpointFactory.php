<?php
declare(strict_types=1);

namespace OpenAgenda\Endpoint;

class EndpointFactory
{
    /**
     * Create endpoint.
     *
     * @param string $path Relative endpoint path.
     * @param array $params Endpoint params.
     * @return \OpenAgenda\Endpoint\Endpoint
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     */
    public static function make(string $path, array $params = []): Endpoint
    {
        switch ($path) {
            case '/agendas':
            case '/agenda':
                return new Agendas($params);
            default:
                throw new UnknownEndpointException($path);
        }
    }
}
