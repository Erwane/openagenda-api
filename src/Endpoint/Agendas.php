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
use OpenAgenda\Entity\Agenda;
use OpenAgenda\OpenAgenda;
use Ramsey\Collection\Collection;

/**
 * Agendas endpoint
 */
class Agendas extends Endpoint
{
    protected $_schema = [
        'size' => ['type' => 'int'],
        'fields' => ['type' => 'array'],
        'search' => ['type' => 'string'],
        'official' => ['type' => 'bool'],
        'slug' => ['type' => 'array'],
        'uid' => ['type' => 'array'],
        'network' => ['type' => 'int'],
        'sort' => [
            'type' => 'string',
        ],
    ];

    /**
     * Validation rules for Uri path GET.
     *
     * @param \Cake\Validation\Validator $validator Validator.
     * @return \Cake\Validation\Validator
     */
    public function validationUriQueryGet(Validator $validator): Validator
    {
        return $validator
            // limit
            ->allowEmptyString('size')
            ->integer('size')
            // page
            ->allowEmptyString('page')
            ->integer('page')
            // fields
            ->allowEmptyArray('fields')
            ->multipleOptions('fields', ['summary', 'schema'])
            // search
            ->allowEmptyString('search')
            ->scalar('search')
            // official
            ->allowEmptyString('official')
            ->boolean('official')
            // slug
            ->allowEmptyArray('slug')
            ->isArray('slug')
            // id
            ->allowEmptyArray('uid')
            ->isArray('uid')
            // network_id
            ->allowEmptyString('network')
            ->integer('network')
            // sort
            ->allowEmptyArray('sort')
            ->inList('sort', ['createdAt.desc', 'recentlyAddedEvents.desc']);
    }

    /**
     * @inheritDoc
     */
    public function uriPath(string $method, bool $validate = true): string
    {
        parent::uriPath($method);

        $path = '/agendas';

        if (isset($this->params['_path']) && $this->params['_path'] === '/agendas/mines') {
            $path = '/me/agendas';
        }

        return $path;
    }

    /**
     * Get agendas.
     *
     * @return \OpenAgenda\Entity\Agenda[]|\Ramsey\Collection\Collection
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function get(): Collection
    {
        $collection = new Collection(Agenda::class);

        $uri = $this->getUri(__FUNCTION__);
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
