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
use OpenAgenda\Endpoint\Agendas;
use OpenAgenda\Entity\Agenda as AgendaEntity;
use OpenAgenda\Test\EndpointTestCase;
use OpenAgenda\Test\Utility\FileResource;
use Ramsey\Collection\Collection;

/**
 * Endpoint\Agendas tests
 *
 * @uses   \OpenAgenda\Endpoint\Agendas
 * @covers \OpenAgenda\Endpoint\Agendas
 */
class AgendasTest extends EndpointTestCase
{
    public function testValidationUriPathGet()
    {
        $endpoint = new Agendas([]);

        $v = $endpoint->validationUriPathGet(new Validator());

        // limit
        $field = $v->field('limit');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);

        // page
        $field = $v->field('page');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);

        // fields
        $field = $v->field('fields');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('multipleOptions', $rules);
        $this->assertEquals(['summary', 'schema'], $rules['multipleOptions']->get('pass')[0]);

        // search
        $field = $v->field('search');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('scalar', $rules);

        // official
        $field = $v->field('official');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('boolean', $rules);

        // slug
        $field = $v->field('slug');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('isArray', $rules);

        // id
        $field = $v->field('id');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('isArray', $rules);

        // network
        $field = $v->field('network');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);

        // sort
        $field = $v->field('sort');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('inList', $rules);
        $this->assertEquals(['created_desc', 'recent_events'], $rules['inList']->get('pass')[0]);
    }

    public static function dataGetUriSuccess(): array
    {
        return [
            [
                'GET',
                [],
                [
                    'path' => '/v2/agendas',
                    'query' => [],
                ],
            ],
            [
                'GET',
                [
                    'limit' => 2,
                    'fields' => ['summary', 'schema'],
                    'search' => 'Agenda',
                    'official' => true,
                    'slug' => 'agenda',
                    'id' => 12,
                    'network' => 34,
                    'sort' => 'created_desc',
                ],
                [
                    'path' => '/v2/agendas',
                    'query' => [
                        'size' => '2',
                        'fields' => ['summary', 'schema'],
                        'search' => 'Agenda',
                        'official' => '1',
                        'slug' => ['agenda'],
                        'uid' => ['12'],
                        'network' => '34',
                        'sort' => 'createdAt.desc',
                    ],
                ],
            ],

            // My agendas. _path is set by EndpointFactory::make()
            [
                'GET',
                [
                    '_path' => '/agendas/mines',
                ],
                [
                    'path' => '/v2/me/agendas',
                    'query' => [],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataGetUriSuccess
     */
    public function testGetUriSuccess($method, $params, $expected)
    {
        $endpoint = new Agendas($params);
        $uri = $endpoint->getUri($method);
        $this->assertEquals($expected['path'], $uri->getPath());
        parse_str((string)$uri->getQuery(), $query);
        $this->assertEquals($expected['query'], $query);
    }

    public function testGet()
    {
        $payload = FileResource::instance($this)->getContent('Response/agendas/agendas.json');

        $this->wrapper->expects($this->once())
            ->method('get')
            ->with(
                'https://api.openagenda.com/v2/agendas?size=2',
                [
                    'headers' => [
                        'key' => 'testing',
                    ],
                ]
            )
            ->willReturn(new Response(200, ['Content-Type' => 'application/json'], $payload));

        $endpoint = new Agendas(['limit' => 2]);

        $agendas = $endpoint->get();

        $this->assertInstanceOf(Collection::class, $agendas);
        $this->assertEquals(AgendaEntity::class, $agendas->getType());
        $this->assertCount(2, $agendas);
    }
}