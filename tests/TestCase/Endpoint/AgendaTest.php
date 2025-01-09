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
use InvalidArgumentException;
use OpenAgenda\Endpoint\Agenda;
use OpenAgenda\Test\EndpointTestCase;
use OpenAgenda\Test\Utility\FileResource;

/**
 * Endpoint\Agenda tests
 *
 * @uses   \OpenAgenda\Endpoint\Agenda
 * @covers \OpenAgenda\Endpoint\Agenda
 */
class AgendaTest extends EndpointTestCase
{
    public function testValidationUriPath()
    {
        $endpoint = new Agenda();

        $v = $endpoint->validationUriPath(new Validator());

        // id
        $field = $v->field('id');
        $this->assertTrue($field->isPresenceRequired());
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);
    }

    public function testValidationUriPathGet()
    {
        $endpoint = new Agenda();

        $v = $endpoint->validationUriPathGet(new Validator());

        // id
        $this->assertTrue($v->hasField('id'));

        // detailed
        $field = $v->field('detailed');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('boolean', $rules);
    }

    public static function dataGetUriErrors(): array
    {
        return [
            [
                'GET',
                [],
                [
                    'id' => [
                        '_required' => 'This field is required',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataGetUriErrors
     */
    public function testGetUriErrors($method, $params, $expected)
    {
        $endpoint = new Agenda($params);
        $message = [
            'message' => 'OpenAgenda\\Endpoint\\Agenda has errors.',
            'errors' => $expected,
        ];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $endpoint->getUri($method);
    }

    public static function dataGetUriSuccess(): array
    {
        return [
            [
                'GET',
                ['id' => 12345],
                [
                    'path' => '/v2/agendas/12345',
                    'query' => [],
                ],
            ],
            [
                'GET',
                [
                    'id' => 12345,
                    'detailed' => true,
                ],
                [
                    'path' => '/v2/agendas/12345',
                    'query' => [
                        'detailed' => '1',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataGetUriSuccess
     */
    public function testGetUriSuccess($method, $params, $expected)
    {
        $endpoint = new Agenda($params);
        $uri = $endpoint->getUri($method);
        $this->assertEquals($expected['path'], $uri->getPath());
        parse_str((string)$uri->getQuery(), $query);
        $this->assertEquals($expected['query'], $query);
    }

    public function testGet()
    {
        $payload = FileResource::instance($this)->getContent('Response/agendas/agenda.json');
        $this->mockRequest(false, 'get', [
                'https://api.openagenda.com/v2/agendas/12345',
                ['headers' => ['key' => 'publicKey']],
        ], [200, $payload]);

        $endpoint = new Agenda(['id' => 12345]);

        $agenda = $endpoint->get();

        $this->assertInstanceOf(\OpenAgenda\Entity\Agenda::class, $agenda);
    }
}
