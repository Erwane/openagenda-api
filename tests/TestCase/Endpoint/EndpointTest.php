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

use DateTimeImmutable;
use OpenAgenda\DateTime;
use OpenAgenda\Test\EndpointTestCase;
use TestApp\Endpoint;

/**
 * @uses   \OpenAgenda\Endpoint\Endpoint
 * @covers \OpenAgenda\Endpoint\Endpoint
 */
class EndpointTest extends EndpointTestCase
{
    public static function dataFormatType(): array
    {
        return [
            [
                ['string' => 'testing sentence'],
                ['string' => 'testing sentence'],
            ],
            [
                ['datetime' => '2024-12-23T12:34:56+03:00'],
                ['datetime' => DateTime::parse('2024-12-23 09:34:56')],
            ],
            [
                ['array' => 'value'],
                ['array' => ['value']],
            ],
            [
                ['array' => ['value']],
                ['array' => ['value']],
            ],
        ];
    }

    /**
     * @dataProvider dataFormatType
     */
    public function testFormatType($params, $expected): void
    {
        $endpoint = new Endpoint($params);
        $result = array_intersect_key($endpoint->toArray()['params'], $params);
        $this->assertEquals($expected, $result);
    }

    public function testGetUrl(): void
    {
        $endpoint = new Endpoint(['int' => 1, 'bool' => false]);
        $this->assertEquals('https://api.openagenda.com/v2/testingEndpoint?int=1&bool=0', $endpoint->getUrl('get'));
    }

    public function testConvertQueryValue(): void
    {
        $endpoint = new Endpoint(['datetime' => new DateTimeImmutable('2024-12-23T12:34:56+02:00')]);
        $url = $endpoint->getUrl('get');
        $this->assertEquals('datetime=2024-12-23T10%3A34%3A56', parse_url($url, PHP_URL_QUERY));
    }

    public function testFieldNotInSchema(): void
    {
        $endpoint = new Endpoint(['unknown' => ['a' => 1]]);
        $url = $endpoint->getUrl('get');
        $this->assertNull(parse_url($url, PHP_URL_QUERY));
    }

    /** @covers \OpenAgenda\Endpoint\Endpoint::toArray */
    public function testToArray(): void
    {
        $endpoint = new Endpoint([
            'array' => 'value',
            'bool' => true,
        ]);

        $this->assertSame([
            'exists' => 'https://api.openagenda.com/v2/testingEndpoint?array%5B0%5D=value&bool=1',
            'get' => 'https://api.openagenda.com/v2/testingEndpoint?array%5B0%5D=value&bool=1',
            'create' => 'https://api.openagenda.com/v2/testingEndpoint?array%5B0%5D=value&bool=1',
            'update' => 'https://api.openagenda.com/v2/testingEndpoint?array%5B0%5D=value&bool=1',
            'delete' => 'https://api.openagenda.com/v2/testingEndpoint?array%5B0%5D=value&bool=1',
            'params' => [
                'array' => ['value'],
                'bool' => true,
            ],
        ], $endpoint->toArray());
    }
}
