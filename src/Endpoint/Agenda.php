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
use OpenAgenda\Entity\Agenda as AgendaEntity;
use OpenAgenda\OpenAgenda;

/**
 * Agenda endpoint
 */
class Agenda extends Endpoint
{
    protected $queryFields = [
        'detailed' => ['name' => 'detailed'],
    ];

    /**
     * @inheritDoc
     */
    public function validationUriPath(Validator $validator): Validator
    {
        return parent::validationUriPath($validator)
            // id
            ->requirePresence('id')
            ->integer('id');
    }

    /**
     * Validation rules for Uri path GET.
     *
     * @param \Cake\Validation\Validator $validator Validator.
     * @return \Cake\Validation\Validator
     */
    public function validationUriPathGet(Validator $validator)
    {
        return $this->validationUriPath($validator)
            // detailed
            ->allowEmptyString('detailed')
            ->boolean('detailed');
    }

    /**
     * @inheritDoc
     */
    public function uriPath(string $method): string
    {
        parent::uriPath($method);

        return sprintf('/agendas/%s', $this->params['id']);
    }

    /**
     * Get agenda.
     *
     * @return \OpenAgenda\Entity\Agenda|null
     */
    public function get(): ?AgendaEntity
    {
        $agenda = null;

        $response = OpenAgenda::getClient()
            ->get($this->getUri(__FUNCTION__));

        if ($response['_success'] && !empty($response['uid'])) {
            $agenda = new AgendaEntity($response, ['markClean' => true]);
        }

        return $agenda;
    }
}