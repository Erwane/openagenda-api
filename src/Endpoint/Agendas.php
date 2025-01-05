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
namespace OpenAgenda\Endpoint;

use Cake\Validation\Validator;
use League\Uri\Uri;
use OpenAgenda\Entity\Agenda;
use OpenAgenda\OpenAgenda;
use Ramsey\Collection\Collection;

/**
 * Agendas endpoint
 */
class Agendas extends Endpoint
{
    protected $fields = [
        'limit' => ['name' => 'size'],
        'fields' => ['name' => 'fields', 'type' => 'array'],
        'search' => ['name' => 'search'],
        'official' => ['name' => 'official'],
        'slug' => ['name' => 'slug', 'type' => 'array'],
        'id' => ['name' => 'uid', 'type' => 'array'],
        'network' => ['name' => 'network'],
        'sort' => [
            'name' => 'sort',
            'matching' => [
                'created_desc' => 'createdAt.desc',
                'recent_events' => 'recentlyAddedEvents.desc',
            ],
        ],
    ];

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator)
    {
        return parent::validationDefault($validator);
        // 'limit' => v::nullable(v::intVal()),
            // 'fields' => v::nullable(v::arrayType()
            //     ->subset(['summary', 'schema'])),
            // 'search' => v::nullable(v::stringVal()),
            // 'official' => v::nullable(v::boolType()),
            // 'slug' => v::nullable(v::each(v::stringType())),
            // 'id' => v::nullable(v::each(v::intVal())),
            // 'network' => v::nullable(v::intVal()),
            // 'sort' => v::nullable(v::stringVal()
            //     ->in(['created_desc', 'recent_events'])),
    }

    /**
     * @inheritDoc
     */
    public function getUri(): Uri
    {
        $path = '/agendas';

        if (isset($this->params['_path']) && $this->params['_path'] === '/agendas/mines') {
            $path = '/me/agendas';
        }

        $components = parse_url($this->baseUrl . $path);
        $query = $this->uriQuery();
        if ($query) {
            $components['query'] = http_build_query($query);
        }

        return Uri::createFromComponents($components);
    }

    /**
     * Get agendas.
     *
     * @return \OpenAgenda\Entity\Agenda[]|\Ramsey\Collection\Collection
     */
    public function get(): Collection
    {
        $collection = new Collection(Agenda::class);
        $uri = $this->getUri();

        $response = OpenAgenda::getClient()->get($uri);

        $target = 'agendas';
        if ($uri->getPath() === '/v2/me/agendas') {
            $target = 'items';
        }

        if ($response['_success'] && !empty($response[$target])) {
            foreach ($response[$target] as $item) {
                $agenda = new Agenda($item, ['markClean' => true]);
                $collection->add($agenda);
            }
        }

        return $collection;
    }
}
