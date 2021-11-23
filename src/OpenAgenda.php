<?php
declare(strict_types=1);

namespace OpenAgenda;

use Exception;
use OpenAgenda\Entity\Agenda;
use OpenAgenda\Entity\Event;
use OpenAgenda\Entity\Location;

class OpenAgenda
{
    /**
     * http client
     *
     * @var \OpenAgenda\Client|null
     */
    protected $client = null;

    /**
     * Api public key
     */
    protected $public;

    /**
     * api secret token
     *
     * @var string|null
     */
    protected $secret = null;

    protected $baseUrl = null;

    /**
     * openagenda uid to publish
     *
     * @var int|null
     */
    protected $_uid = null;

    /**
     * constuctor
     *
     * @param string $apiPublic
     * @param string $apiSecret openagenda api secret
     */
    public function __construct(string $apiPublic, string $apiSecret)
    {
        $this->public = $apiPublic;
        $this->secret = $apiSecret;
    }

    /**
     * base url for relative links
     *
     * @param string $url base url
     */
    public function setBaseUrl(string $url)
    {
        $last = substr($url, -1, 1);
        if ($last !== '/') {
            $url .= '/';
        }

        $this->baseUrl = $url;

        return $this;
    }

    /**
     * set agenda uid
     *
     * @param int $uid agenda uid
     * @return $this
     */
    public function setAgendaUid(int $uid)
    {
        $this->_uid = $uid;

        return $this;
    }

    /**
     * get agenda uid
     *
     * @return int|null
     */
    public function getAgendaUid(): ?int
    {
        return $this->_uid;
    }

    public function newEvent()
    {
        $event = new Event();

        $event->set('baseUrl', $this->baseUrl);

        return $event;
    }

    /**
     * get access token from API or local cache
     *
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        $accessToken = Cache::get('openagenda-token');

        if (empty($accessToken)) {
            try {
                $response = $this->getClient()->post('/v2/requestAccessToken', [
                    'json' => [
                        'grant-type' => 'authorization_code',
                        'code' => $this->secret,
                    ],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                if ($response->getStatusCode() !== 200 || empty($data['access_token'])) {
                    return null;
                }

                $accessToken = $data['access_token'];

                Cache::set('openagenda-token', $accessToken, $data['expires_in']);
            } catch (OpenAgendaException $e) {
                return null;
            }
        }

        return $accessToken;
    }

    /**
     * get Location object with uid
     *
     * @param array|int $datas location id or datas
     * @return Location object
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
                    sprintf('/v2/agendas/%d/locations', $this->_uid),
                    ['data' => json_encode($options)]
                );

            if ($response->getStatusCode() !== 200) {
                throw new OpenAgendaException('Location creation failed');
            }

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['location']['uid'] ?? null;
        } catch (OpenAgendaException $e) {
            return null;
        }
    }

    /**
     * @return \OpenAgenda\Client
     */
    public function getClient(): Client
    {
        if (!$this->client) {
            $this->client = new Client();

            $this->client->setPublicKey($this->public);
        }

        return $this->client;
    }

    /**
     * @param \OpenAgenda\Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        $this->client->setPublicKey($this->public);

        return $this;
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

            $response = $this->getClient()->get('/v2/agendas', $options);
            $data = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() !== 200 || empty($data['agendas'][0]['uid'])) {
                return null;
            }

            $uid = $data['agendas'][0]['uid'];
            $agendaIds[$slug] = $uid;

            Cache::set('openagenda-id', $agendaIds, 86400 * 365);
        } else {
            $uid = $agendaIds[$slug];
        }

        return $uid ? new Agenda(['uid' => $uid]) : null;
    }

    /**
     * @return array|null
     */
    public function getAgendaSettings(): ?array
    {
        try {
            $response = $this->getClient()->get('/v2/agendas/' . $this->_uid);

            $return = json_decode($response->getBody()->getContents(), true);

            return $return ?? null;
        } catch (OpenAgendaException $e) {
            return null;
        }
    }

    /**
     * publish event to openagenda and set uid to entity
     *
     * @param Event $event entity
     * @return int
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function publishEvent(Event $event): int
    {
        $response = $this->getClient()
            ->setAccessToken($this->getAccessToken())
            ->post('/v2/agendas/' . $this->_uid . '/events', $event->toDatas());

        $data = json_decode($response->getBody()->getContents(), true);
        if (!$data || empty($data['event']['uid'])) {
            throw new OpenAgendaException('Publish event failed');
        }

        $event->id = $event->uid = $data['event']['uid'];

        return $event->id;
    }

    /**
     * update event to openagenda
     *
     * @param Event $event entity
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
                ->post('/v2/agendas/' . $this->_uid . '/events/' . $event->uid, $event->toDatas());
            $data = json_decode($response->getBody()->getContents(), true);

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
            $response = $this->getClient()->get(sprintf('/v2/agendas/%d/events/%d', $this->_uid, $eventId));

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $decoded = json_decode($response->getBody()->getContents(), true);

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
            ->delete('/v2/agendas/' . $this->_uid . '/events/' . $event->uid);

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['success'] ?? false;
    }
}
