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
    public function testValidationDefault()
    {
        $endpoint = new Agenda([]);

        $v = $endpoint->validationDefault(new Validator());

        // detailed
        $this->assertTrue($v->hasField('detailed'));
        $field = $v->field('detailed');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('boolean', $rules);
    }

    public function testMissingAgendaId(): void
    {
        $endpoint = new Agenda([]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing valid `id` param.');
        $endpoint->getUri();
    }

    public static function dataGetUriSuccess(): array
    {
        return [
            [
                ['id' => 12345],
                [
                    'path' => '/v2/agendas/12345',
                    'query' => [],
                ],
            ],
            [
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
    public function testGetUriSuccess($params, $expected)
    {
        $endpoint = new Agenda($params);
        $uri = $endpoint->getUri();
        $this->assertEquals($expected['path'], $uri->getPath());
        parse_str((string)$uri->getQuery(), $query);
        $this->assertEquals($expected['query'], $query);
    }

    public function testGet()
    {
        $payload = FileResource::instance($this)->getContent('Response/agendas/agenda.json');

        $this->wrapper->expects($this->once())
            ->method('get')
            ->with(
                'https://api.openagenda.com/v2/agendas/12345',
                ['headers' => ['key' => 'testing']]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], $payload));

        $endpoint = new Agenda(['id' => 12345]);

        $agenda = $endpoint->get();

        $this->assertInstanceOf(\OpenAgenda\Entity\Agenda::class, $agenda);
    }
}
