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
use OpenAgenda\Collection;
use OpenAgenda\Entity\Agenda;
use OpenAgenda\OpenAgenda;

/**
 * Agendas endpoint
 */
class Agendas extends Endpoint
{
    protected array $_schema = [
        'limit' => ['type' => 'int'],
        'size' => ['type' => 'int'],
        'fields' => ['type' => 'array'],
        'search' => ['type' => 'string'],
        'official' => ['type' => 'bool'],
        'slug' => ['type' => 'array'],
        'uid' => ['type' => 'array'],
        'network' => ['type' => 'int'],
        'sort' => ['type' => 'string'],
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
            ->allowEmptyString('limit')
            ->integer('limit')
            // size
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
            ->array('slug')
            // id
            ->allowEmptyArray('uid')
            ->array('uid')
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
    protected function uriPath(string $method, bool $validate = true): string
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
     * @return \OpenAgenda\Collection
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function get(): Collection
    {
        $url = $this->getUrl(__FUNCTION__);
        $response = OpenAgenda::getClient()->get($url);

        $target = 'agendas';
        if (parse_url($url, PHP_URL_PATH) === '/v2/me/agendas') {
            $target = 'items';
        }

        $items = [];
        if ($response['_success'] && !empty($response[$target])) {
            foreach ($response[$target] as $item) {
                $agenda = new Agenda($item, ['markClean' => true]);
                $items[] = $agenda;
            }
        }

        return new Collection($items);
    }
}
