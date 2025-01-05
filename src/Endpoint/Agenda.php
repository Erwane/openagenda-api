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

use Cake\Validation\Validation;
use Cake\Validation\Validator;
use InvalidArgumentException;
use League\Uri\Uri;
use OpenAgenda\Entity\Agenda as AgendaEntity;
use OpenAgenda\OpenAgenda;

/**
 * Agenda endpoint
 */
class Agenda extends Endpoint
{
    protected $fields = [
        'detailed' => ['name' => 'detailed'],
    ];

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator)
    {
        return parent::validationDefault($validator)

            // detailed
            ->allowEmptyString('detailed')
            ->boolean('detailed');
    }

    /**
     * @inheritDoc
     */
    public function getUri(): Uri
    {
        if (!Validation::numeric($this->params['id'] ?? null)) {
            throw new InvalidArgumentException('Missing valid `id` param.');
        }

        $path = sprintf('/agendas/%s', $this->params['id']);

        $components = parse_url($this->baseUrl . $path);
        $query = $this->uriQuery();
        if ($query) {
            $components['query'] = http_build_query($query);
        }

        return Uri::createFromComponents($components);
    }

    /**
     * Get locations.
     *
     * @return \OpenAgenda\Entity\Agenda|null
     */
    public function get(): ?AgendaEntity
    {
        $agenda = null;

        $response = OpenAgenda::getClient()->get($this->getUri());

        if ($response['_success'] && !empty($response['uid'])) {
            $agenda = new AgendaEntity($response, ['markClean' => true]);
        }

        return $agenda;
    }
}
