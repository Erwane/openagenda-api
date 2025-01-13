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
    public function testValidationUriQueryGet()
    {
        $endpoint = new Agendas([]);

        $v = $endpoint->validationUriQueryGet(new Validator());

        $this->assertCount(10, $v);

        // size
        $field = $v->field('size');
        $this->assertTrue($field->isEmptyAllowed());
        $rules = $field->rules();
        $this->assertArrayHasKey('integer', $rules);

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

        // uid
        $field = $v->field('uid');
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
        $this->assertEquals(['createdAt.desc', 'recentlyAddedEvents.desc'], $rules['inList']->get('pass')[0]);
    }

    public static function dataGetUriSuccess(): array
    {
        return [
            [
                'get',
                [],
                [
                    'path' => '/v2/agendas',
                    'query' => [],
                ],
            ],
            [
                'get',
                [
                    'size' => 2,
                    'fields' => ['summary', 'schema'],
                    'search' => 'Agenda',
                    'official' => true,
                    'slug' => 'agenda',
                    'uid' => 12,
                    'network' => 34,
                    'sort' => 'createdAt.desc',
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
                'get',
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
        $this->mockRequest(false, 'get', [
            'https://api.openagenda.com/v2/agendas?size=2',
            ['headers' => ['key' => 'publicKey']],
        ], [200, $payload]);

        $endpoint = new Agendas(['size' => 2]);

        $agendas = $endpoint->get();

        $this->assertInstanceOf(Collection::class, $agendas);
        $this->assertEquals(AgendaEntity::class, $agendas->getType());
        $this->assertCount(2, $agendas);
    }

    public function testGetMines()
    {
        $payload = FileResource::instance($this)->getContent('Response/agendas/mines.json');
        $this->mockRequest(false, 'get', [
            'https://api.openagenda.com/v2/me/agendas?size=1',
            ['headers' => ['key' => 'publicKey']],
        ], [200, $payload]);

        $endpoint = new Agendas(['size' => 1, '_path' => '/agendas/mines']);

        $agendas = $endpoint->get();

        $this->assertInstanceOf(Collection::class, $agendas);
        $this->assertEquals(AgendaEntity::class, $agendas->getType());
        $this->assertCount(1, $agendas);
    }
}
