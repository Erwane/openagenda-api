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
namespace OpenAgenda\Test\TestCase\Endpoint;

use OpenAgenda\Endpoint\Auth;
use OpenAgenda\Test\EndpointTestCase;

/**
 * Endpoint\Auth tests
 *
 * @uses   \OpenAgenda\Endpoint\Auth
 * @covers \OpenAgenda\Endpoint\Auth
 */
class AuthTest extends EndpointTestCase
{
    public function testGetUriSuccess()
    {
        $endpoint = new Auth();
        $uri = $endpoint->getUri('post');
        $this->assertEquals('/v2/requestAccessToken', $uri->getPath());
        $this->assertNull($uri->getQuery());
    }
}
