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
namespace OpenAgenda\Test\TestCase\Wrapper;

use GuzzleHttp\Psr7\Request;
use OpenAgenda\Wrapper\HttpWrapperException;
use PHPUnit\Framework\TestCase;

class HttpWrapperExceptionTest extends TestCase
{
    public function testSetGetRequest(): void
    {
        $e = new HttpWrapperException();
        $this->assertNull($e->getRequest());

        $request = new Request('get', 'https://example.com');
        $e->setRequest($request);
        $this->assertSame($request, $e->getRequest());
    }
}
