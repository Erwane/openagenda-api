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

use OpenAgenda\Collection;
use OpenAgenda\Entity\Agenda;
use OpenAgenda\OpenAgenda;

/**
 * Agendas endpoint
 */
class Agendas extends Endpoint
{
    protected $_schema = [
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
     * @inheritDoc
     */
    protected function uriPath(string $method): string
    {
        $path = '/agendas';

        if (isset($this->params['_path']) && $this->params['_path'] === '/agendas/mines') {
            $path = '/me/agendas';
        }

        return $path;
    }

    /**
     * Get agendas.
     *
     * @return \OpenAgenda\Entity\Agenda[]|\OpenAgenda\Collection
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
