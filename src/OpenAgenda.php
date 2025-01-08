<?php
declare(strict_types=1);

namespace OpenAgenda;

use Exception;
use OpenAgenda\Endpoint\EndpointFactory;
use OpenAgenda\Entity\Agenda;
use OpenAgenda\Entity\Event;
use OpenAgenda\Entity\Location;
use OpenAgenda\Wrapper\HttpWrapper;
use Psr\SimpleCache\CacheInterface;
use Ramsey\Collection\Collection;

/**
 * OpenAgenda
 */
class OpenAgenda
{
    /**
     * OpenAgenda client
     *
     * @var \OpenAgenda\Client
     */
    protected static $client;

    /**
     * OpenAgenda.
     *
     * @param array $params OpenAgenda params.
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function __construct(array $params = [])
    {
        $params += [
            'public_key' => null,
            'private_key' => null,
            'wrapper' => null,
            'cache' => null,
        ];

        if (!$params['public_key']) {
            throw new OpenAgendaException('Missing `public_key`.');
        }

        if (!($params['wrapper'] instanceof HttpWrapper)) {
            throw new OpenAgendaException('Invalid or missing `wrapper`.');
        }

        if ($params['cache'] && !($params['cache'] instanceof CacheInterface)) {
            throw new OpenAgendaException('Cache should implement \Psr\SimpleCache\CacheInterface.');
        }

        self::$client = new Client($params);
    }

    /**
     * Set static client.
     *
     * @param \OpenAgenda\Client $client OpenAgenda client.
     * @return void
     */
    public static function setClient(Client $client)
    {
        self::$client = $client;
    }

    /**
     * Get OpenAgenda client.
     *
     * @return \OpenAgenda\Client
     */
    public static function getClient(): Client
    {
        return self::$client;
    }

    /**
     * Do a GET request on $path.
     *
     * @param string $path Endpoint path. Relative, not real OpenAgenda endpoint.
     * @param array $params Client options
     * @return \Ramsey\Collection\Collection|\OpenAgenda\Entity\Entity|\Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     */
    public function get(string $path, array $params = [])
    {
        // todo: allow passing raw OpenAgenda endpoint url and return ResponseInterface.
        // todo: return response or json payload
        return EndpointFactory::make($path, $params)->get();
    }

    /**
     * Do a POST request on $path.
     *
     * @param string $path Endpoint path. Relative, not real OpenAgenda endpoint.
     * @param array $params Client options
     * @return \Ramsey\Collection\Collection|\OpenAgenda\Entity\Entity|\Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     */
    public function post(string $path, array $params = [])
    {
        // todo: allow passing raw OpenAgenda endpoint url and return ResponseInterface.
        // todo: return response or json payload
        return EndpointFactory::make($path, $params)->post();
    }

    /**
     * Do a PATCH request on $path.
     *
     * @param string $path Endpoint path. Relative, not real OpenAgenda endpoint.
     * @param array $params Client options
     * @return \Ramsey\Collection\Collection|\OpenAgenda\Entity\Entity|\Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     */
    public function patch(string $path, array $params = [])
    {
        // todo: allow passing raw OpenAgenda endpoint url and return ResponseInterface.
        // todo: return response or json payload
        return EndpointFactory::make($path, $params)->patch();
    }

    /**
     * Do a DELETE request on $path.
     *
     * @param string $path Endpoint path. Relative, not real OpenAgenda endpoint.
     * @param array $params Client options
     * @return \Ramsey\Collection\Collection|\OpenAgenda\Entity\Entity|\Psr\Http\Message\ResponseInterface
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     */
    public function delete(string $path, array $params = [])
    {
        // todo: allow passing raw OpenAgenda endpoint url and return ResponseInterface.
        // todo: return response or json payload
        return EndpointFactory::make($path, $params)->delete();
    }

    /**
     * Get agendas from OpenAgenda.
     *
     * @param array $params Query params.
     * @return \OpenAgenda\Entity\Agenda[]|\Ramsey\Collection\Collection
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     * @uses \OpenAgenda\Endpoint\Agendas::get()
     */
    public function agendas(array $params = []): Collection
    {
        return EndpointFactory::make('/agendas', $params)->get();
    }

    /**
     * Get agendas from OpenAgenda.
     *
     * @param array $params Query params.
     * @return \OpenAgenda\Entity\Agenda[]|\Ramsey\Collection\Collection
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     * @uses \OpenAgenda\Endpoint\Agendas
     */
    public function myAgendas(array $params = []): Collection
    {
        return EndpointFactory::make('/agendas/mines', $params)->get();
    }

    /**
     * Get one agenda from OpenAgenda.
     *
     * @param array $params Query params.
     * @return \OpenAgenda\Entity\Agenda|null
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     * @uses \OpenAgenda\Endpoint\Agendas
     */
    public function agenda(array $params): ?Agenda
    {
        return EndpointFactory::make('/agenda', $params)->get();
    }

    /**
     * Get OpenAgenda locations for an agenda.
     *
     * @param array $params Query params.
     * @return \OpenAgenda\Entity\Location[]|\Ramsey\Collection\Collection
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     */
    public function locations(array $params = []): Collection
    {
        return EndpointFactory::make('/locations', $params)->get();
    }

    public function newEvent()
    {
        $event = new Event();

        $event->set('baseUrl', $this->baseUrl);

        return $event;
    }

    /**
     * get Location object with uid
     *
     * @param array|int $datas location id or datas
     * @return \OpenAgenda\Entity\Location object
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function getLocation($datas)
    {
        // create location
        $location = new Location();

        if (is_numeric($datas)) {
            $datas = ['id' => $datas];
        } elseif (!is_array($datas)) {
            throw new OpenAgendaException('invalid location data', 1);
        }

        if (!isset($datas['id'])) {
            $datas['id'] = $this->createLocation($datas);
            $location->isNew(true);
        } else {
            $location->isNew(false);
        }

        // set Id
        $location->id = $datas['id'];

        // set latitude if exists
        if (isset($datas['latitude'])) {
            $location->latitude = $datas['latitude'];
        }
        // set longitude if exists
        if (isset($datas['longitude'])) {
            $location->longitude = $datas['longitude'];
        }
        // mark as not dirty
        $location->markAsNotDirty();

        return $location;
    }

    /**
     * create location
     *
     * @param array $options location options
     * @return int|null Location id
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function createLocation(array $options): ?int
    {
        if (!isset($options['name'])) {
            throw new OpenAgendaException('missing name field', 1);
        }
        if (!isset($options['latitude'])) {
            throw new OpenAgendaException('missing latitude field', 1);
        }
        if (!isset($options['longitude'])) {
            throw new OpenAgendaException('missing longitude field', 1);
        }
        if (!isset($options['address'])) {
            throw new OpenAgendaException('missing address field', 1);
        }
        if (!isset($options['countryCode'])) {
            throw new OpenAgendaException('missing countryCode field', 1);
        }

        // format
        $options['latitude'] = (float)$options['latitude'];
        $options['longitude'] = (float)$options['longitude'];

        try {
            $response = $this->getClient()
                ->setAccessToken($this->getAccessToken())
                ->post(
                    sprintf('/agendas/%d/locations', $this->_uid),
                    ['data' => json_encode($options)]
                );

            if ($response->getStatusCode() !== 200) {
                throw new OpenAgendaException('Location creation failed');
            }

            $data = json_decode((string)$response->getBody(), true);

            return $data['location']['uid'] ?? null;
        } catch (OpenAgendaException $e) {
            return null;
        }
    }

    /**
     * @param $slug
     * @return \OpenAgenda\Entity\Agenda
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function getUidFromSlug($slug): ?Agenda
    {
        if (is_numeric($slug)) {
            return new Agenda(['uid' => $slug]);
        }

        $agendaIds = Cache::get('openagenda-id');

        if (empty($agendaIds)) {
            $agendaIds = [];
        }

        if (empty($agendaIds[$slug])) {
            $options = [
                'query' => [
                    'limit' => 1,
                    'slug[]' => $slug,
                ],
            ];

            $response = $this->getClient()->get('/agendas', $options);
            $data = json_decode((string)$response->getBody(), true);

            if ($response->getStatusCode() !== 200 || empty($data['agendas'][0]['uid'])) {
                return null;
            }

            $uid = $data['agendas'][0]['uid'];
            $agendaIds[$slug] = $uid;

            Cache::set('openagenda-id', $agendaIds, 86400 * 365);
        } else {
            $uid = $agendaIds[$slug];
        }

        return new Agenda(['uid' => $uid]);
    }

    /**
     * @return array|null
     */
    public function getAgendaSettings(): ?array
    {
        try {
            $response = $this->getClient()->get('/agendas/' . $this->_uid);

            $return = json_decode((string)$response->getBody(), true);

            return $return ?? null;
        } catch (OpenAgendaException $e) {
            return null;
        }
    }

    /**
     * publish event to openagenda and set uid to entity
     *
     * @param \OpenAgenda\Entity\Event $event entity
     * @return int
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function publishEvent(Event $event): int
    {
        $response = $this->getClient()
            ->setAccessToken($this->getAccessToken())
            ->post('/agendas/' . $this->_uid . '/events', $event->toDatas());

        $data = json_decode((string)$response->getBody(), true);
        if (!$data || empty($data['event']['uid'])) {
            throw new OpenAgendaException('Publish event failed');
        }

        $event->id = $event->uid = $data['event']['uid'];

        return $event->id;
    }

    /**
     * update event to openagenda
     *
     * @param \OpenAgenda\Entity\Event $event entity
     * @return bool
     */
    public function updateEvent(Event $event): bool
    {
        if (!$event->uid) {
            return false;
        }

        try {
            if (empty($event->getDirty())) {
                return true;
            }

            $response = $this->getClient()
                ->setAccessToken($this->getAccessToken())
                ->post('/agendas/' . $this->_uid . '/events/' . $event->uid, $event->toDatas());
            $data = json_decode((string)$response->getBody(), true);

            return $response->getStatusCode() === 200 && !empty($data['success']);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * get event from openagenda and return Entity
     *
     * @param int $eventId openagenda event id
     * @return \OpenAgenda\Entity\Event
     */
    public function getEvent(int $eventId): ?Event
    {
        try {
            $response = $this->getClient()->get(sprintf('/agendas/%d/events/%d', $this->_uid, $eventId));

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $decoded = json_decode((string)$response->getBody(), true);

            $data = $decoded['event'];

            // location
            $location = new Location();
            if (!empty($data['location'])) {
                $location->import($data['location']);
            }

            // create event entity
            $event = new Event($data, ['useSetters' => false, 'markClean' => true]);
            $event->set(['id' => $data['uid'], 'baseUrl' => $this->baseUrl])
                ->setLocation($location)
                ->setDirty('location', false);
        } catch (OpenAgendaException $e) {
            return null;
        }

        return $event;
    }

    /**
     * delete event from open agenda.
     * Detach from agenda if attached
     *
     * @param \OpenAgenda\Entity\Event|int $event entity or uid
     * @return bool
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function deleteEvent($event): bool
    {
        if (is_numeric($event)) {
            $event = $this->getEvent((int)$event);
        }

        // not an event
        if (!$event || !$event->uid) {
            throw new OpenAgendaException('require valid event');
        }

        $response = $this->getClient()
            ->setAccessToken($this->getAccessToken())
            ->delete('/agendas/' . $this->_uid . '/events/' . $event->uid);

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $data = json_decode((string)$response->getBody(), true);

        return $data['success'] ?? false;
    }
}
