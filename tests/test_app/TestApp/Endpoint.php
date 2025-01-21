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
namespace TestApp;

/**
 * @coversNothing
 */
class Endpoint extends \OpenAgenda\Endpoint\Endpoint
{
    protected array $_schema = [
        'datetime' => ['type' => 'datetime'],
        'bool' => ['type' => 'bool'],
        'int' => ['type' => 'int'],
        'array' => ['type' => 'array'],
        'json' => ['type' => 'json'],
    ];

    protected function uriPath(string $method): string
    {
        return '/testingEndpoint';
    }
}
