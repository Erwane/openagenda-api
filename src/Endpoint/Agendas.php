<?php
declare(strict_types=1);

namespace OpenAgenda\Endpoint;

use League\Uri\Uri;
use OpenAgenda\Entity\Agenda;
use Ramsey\Collection\Collection;
use Respect\Validation\Validator as v;

class Agendas extends Endpoint
{
    protected $queryMap = [
        'limit' => ['name' => 'size'],
        'fields' => ['name' => 'fields'],
        'search' => ['name' => 'search'],
        'official' => ['name' => 'official'],
        'slug' => ['name' => 'slug'],
        'id' => ['name' => 'uid'],
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
    protected function validateParams(array $params): array
    {
        $params += array_fill_keys(array_keys($this->queryMap), null);

        $validators = [
            'limit' => v::nullable(v::intVal()),
            'fields' => v::nullable(v::arrayType()
                ->subset(['summary', 'schema'])),
            'search' => v::nullable(v::stringVal()),
            'official' => v::nullable(v::boolType()),
            'slug' => v::nullable(v::each(v::stringType())),
            'id' => v::nullable(v::each(v::intVal())),
            'network' => v::nullable(v::intVal()),
            'sort' => v::nullable(v::stringVal()
                ->in(['created_desc', 'recent_events'])),
        ];

        $params['fields'] = $this->paramAsArray($params['fields']);
        $params['slug'] = $this->paramAsArray($params['slug']);
        $params['id'] = $this->paramAsArray($params['id']);

        foreach ($validators as $param => $validator) {
            $validator->assert($params[$param]);
        }

        return $params;
    }

    /**
     * @inheritDoc
     */
    protected function uriQuery(): array
    {
        $query = [];

        $params = $this->validateParams($this->params);

        foreach ($params as $param => $value) {
            if (!isset($this->queryMap[$param])) {
                continue;
            }

            $map = $this->queryMap[$param];
            $query[$map['name']] = $this->convertQueryValue($map, $value);
        }

        // filter
        $query = array_filter($query, function ($value) {
            return $value !== null;
        });

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function getUri(): Uri
    {
        $path = '/agendas';

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

        $response = $this->client->get($this->getUri());

        if ($response['_success'] && !empty($response['agendas'])) {
            foreach ($response['agendas'] as $item) {
                $agenda = new Agenda($item);
                $collection->add($agenda);
            }
        }

        return $collection;
    }
}
