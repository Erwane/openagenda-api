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
        $params['_path'] = $path;
        switch ($path) {
            case '/agendas':
            case '/agendas/mines':
                return new Agendas($params);
            case '/locations':
                return new Locations($params);
            default:
                throw new UnknownEndpointException($path);
        }
    }
}
