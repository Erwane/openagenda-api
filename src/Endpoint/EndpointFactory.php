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
namespace OpenAgenda\Endpoint;

/**
 * Endpoint factory
 */
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

        return match ($path) {
            '/agendas', '/agendas/mines' => new Agendas($params),
            '/agenda' => new Agenda($params),
            '/locations' => new Locations($params),
            '/location' => new Location($params),
            '/events' => new Events($params),
            '/event' => new Event($params),
            default => throw new UnknownEndpointException($path),
        };
    }
}
