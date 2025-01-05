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
namespace OpenAgenda\Test;

use OpenAgenda\Client;
use OpenAgenda\OpenAgenda;
use OpenAgenda\Wrapper\HttpWrapper;
use PHPUnit\Framework\TestCase;

class EndpointTestCase extends TestCase
{
    /**
     * @var (\object&\PHPUnit\Framework\MockObject\MockObject)|\OpenAgenda\Client|(\OpenAgenda\Client&\object&\PHPUnit\Framework\MockObject\MockObject)|(\OpenAgenda\Client&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wrapper = $this->getMockForAbstractClass(
            HttpWrapper::class,
            [],
            '',
            false,
            true,
            true,
            ['head', 'get', 'post', 'patch', 'delete']
        );

        $this->client = new Client([
            'public_key' => 'testing',
            'wrapper' => $this->wrapper,
        ]);

        OpenAgenda::setClient($this->client);
    }
}
