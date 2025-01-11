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

use Cake\Chronos\Chronos;
use Cake\Validation\Validator;
use OpenAgenda\OpenAgendaException;
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
                ['datetime' => Chronos::parse('2024-12-23 09:34:56')],
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

    public function testValidationUriPath(): void
    {
        $endpoint = new Endpoint();
        $validator = new Validator();
        $result = $endpoint->validationUriPath($validator);

        $this->assertSame($validator, $result);
    }

    public function testValidationUriQuery(): void
    {
        $endpoint = new Endpoint();
        $validator = new Validator();
        $result = $endpoint->validationUriQuery($validator);

        $this->assertSame($validator, $result);
    }

    public static function dataUriPath(): array
    {
        return [
            ['get', 'uriPathGet', 'uriQueryGet'],
            ['create', 'uriPath', 'uriQuery'],
        ];
    }

    /** @dataProvider dataUriPath */
    public function testUriPath($method, $first, $second): void
    {
        $validator = new Validator();
        $endpoint = $this->getMockForAbstractClass(
            \OpenAgenda\Endpoint\Endpoint::class,
            [],
            '',
            false,
            true,
            true,
            ['getValidator', 'validationUriPathGet', 'validationUriQueryGet']
        );
        $endpoint->expects($this->exactly(2))
            ->method('getValidator')
            ->withConsecutive([$first], [$second])
            ->willReturn($validator);
        $endpoint->getUri($method);
    }

    public function testUriPathException(): void
    {
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage(json_encode([
            'message' => 'TestApp\\Endpoint has errors.',
            'errors' => [
                'path' => ['inList' => 'The provided value is invalid'],
            ],
        ]));
        $endpoint = new Endpoint(['path' => 'those']);
        $endpoint->getUri('get');
    }

    public function testUriQueryException(): void
    {
        $this->expectException(OpenAgendaException::class);
        $this->expectExceptionMessage(json_encode([
            'message' => 'TestApp\\Endpoint has errors.',
            'errors' => [
                'query' => ['inList' => 'The provided value is invalid'],
            ],
        ]));
        $endpoint = new Endpoint(['query' => 'those']);
        $endpoint->getUri('get');
    }

    public function testConvertQueryValue(): void
    {
        $endpoint = new Endpoint(['datetime' => Chronos::parse('2024-12-23T12:34:56+02:00')]);
        $uri = $endpoint->getUri('get');
        $this->assertEquals('datetime=2024-12-23T10%3A34%3A56', $uri->getQuery());
    }

    public function testFieldNotInSchema(): void
    {
        $endpoint = new Endpoint(['unkown' => ['a' => 1]]);
        $uri = $endpoint->getUri('get');
        $this->assertNull($uri->getQuery());
    }

    /** @covers \OpenAgenda\Endpoint\Endpoint::toArray */
    public function testToArray(): void
    {
        $endpoint = new Endpoint([
            'array' => 'value',
            'bool' => true,
        ]);

        $this->assertSame([
            'exists' => 'https://api.openagenda.com/v2?array%5B0%5D=value&bool=1',
            'get' => 'https://api.openagenda.com/v2?array%5B0%5D=value&bool=1',
            'create' => 'https://api.openagenda.com/v2?array%5B0%5D=value&bool=1',
            'update' => 'https://api.openagenda.com/v2?array%5B0%5D=value&bool=1',
            'delete' => 'https://api.openagenda.com/v2?array%5B0%5D=value&bool=1',
            'params' => [
                'array' => ['value'],
                'bool' => true,
            ],
        ], $endpoint->toArray());
    }
}