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

use Cake\Validation\Validator;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use OpenAgenda\Endpoint\Location;
use OpenAgenda\Test\EndpointTestCase;
use OpenAgenda\Test\Utility\FileResource;

class LocationTest extends EndpointTestCase
{
    public function testValidationUriPath()
    {
        $endpoint = new Location([]);

        $v = $endpoint->validationUriPath(new Validator());

        // agenda_id
        $this->assertTrue($v->hasField('agenda_id'));
        $field = $v->field('agenda_id');
        $this->assertTrue($field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);

        // id
        $this->assertTrue($v->hasField('id'));
        $field = $v->field('id');
        $this->assertIsCallable($field->isPresenceRequired());
        $this->assertEquals('checkIdOrExtId', $field->isPresenceRequired()[1]);
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);

        // ext_id
        $this->assertTrue($v->hasField('ext_id'));
        $field = $v->field('ext_id');
        $this->assertIsCallable($field->isPresenceRequired());
        $this->assertEquals('checkIdOrExtId', $field->isPresenceRequired()[1]);
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);
    }

    public static function dataGetUriErrors(): array
    {
        return [
            [
                [],
                [
                    'agenda_id' => [
                        '_required' => 'This field is required',
                    ],
                    'id' => [
                        '_required' => 'One of `id` or `ext_id` is required',
                    ],
                    'ext_id' => [
                        '_required' => 'One of `id` or `ext_id` is required',
                    ],
                ],
            ],
            [
                ['agenda_id' => 123],
                [
                    'id' => [
                        '_required' => 'One of `id` or `ext_id` is required',
                    ],
                    'ext_id' => [
                        '_required' => 'One of `id` or `ext_id` is required',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataGetUriErrors
     */
    public function testGetUriErrors($params, $expected)
    {
        $endpoint = new Location($params);
        $message = [
            'message' => 'OpenAgenda\\Endpoint\\Location has errors.',
            'errors' => $expected,
        ];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $endpoint->getUri();
    }

    public static function dataGetUriSuccess(): array
    {
        return [
            [
                ['agenda_id' => 123, 'id' => 456, 'ext_id' => 'my-internal-id'],
                [
                    'path' => '/v2/agendas/123/locations/456',
                    'query' => [],
                ],
            ],
            [
                ['agenda_id' => 123, 'ext_id' => 'my-internal-id'],
                [
                    'path' => '/v2/agendas/123/locations/ext/my-internal-id',
                    'query' => [],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataGetUriSuccess
     */
    public function testGetUriSuccess($params, $expected)
    {
        $endpoint = new Location($params);
        $uri = $endpoint->getUri();
        $this->assertEquals($expected['path'], $uri->getPath());
        parse_str((string)$uri->getQuery(), $query);
        $this->assertEquals($expected['query'], $query);
    }

    public function testGet()
    {
        $payload = FileResource::instance($this)->getContent('Response/locations/location.json');

        $this->wrapper->expects($this->once())
            ->method('get')
            ->with(
                'https://api.openagenda.com/v2/agendas/123/locations/456',
                ['headers' => ['key' => 'testing']]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], $payload));

        $endpoint = new Location(['agenda_id' => 123, 'id' => 456]);

        $entity = $endpoint->get();

        $this->assertInstanceOf(\OpenAgenda\Entity\Location::class, $entity);
    }
}
