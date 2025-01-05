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
            case '/agenda':
                return new Agenda($params);
            case '/locations':
                return new Locations($params);
            default:
                throw new UnknownEndpointException($path);
        }
    }
}
