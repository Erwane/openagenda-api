<?php
declare(strict_types=1);

namespace OpenAgenda\Entity;

use Cake\Chronos\Chronos;
use HTMLPurifier;
use HTMLPurifier_Config;
use HTMLPurifier_TagTransform_Simple;
use League\HTMLToMarkdown\HtmlConverter;
use OpenAgenda\Endpoint\EndpointFactory;
use OpenAgenda\OpenAgenda;
use OpenAgenda\OpenAgendaException;

/**
 * @property int|null $id
 * @property string|null $slug
 * @property int|null $state
 * @property int|null $status
 * @property bool|null $featured
 * @property string|null $agenda
 * @property int|null $agenda_id
 * @property string|null $location
 * @property int|null $location_id
 * @property string|null $type
 * @property string|null $image
 * @property string|null $image_credits
 * @property array<string, string>|null $title
 * @property array<string, string>|null $description
 * @property array<string, string>|null $long_description
 * @property array<string, string>|null $keywords
 * @property array<string, string>|null $conditions
 * @property array<string, int|null>|null $age
 * @property array<string, string>|null $registration
 * @property array<string, bool>|null $accessibility
 * @property string[]|null $links
 * @property int|null $attendance_mode
 * @property string|null $online_access_link
 * @property array|null $timings
 * @property string|null $timezone
 * @property \Cake\Chronos\Chronos|null $created_at
 * @property \Cake\Chronos\Chronos|null $updated_at
 */
class Event extends Entity
{
    public const STATE_REFUSED = -1; // Refused.
    public const STATE_MODERATION = 0; // To moderate.
    public const STATE_READY = 1; // Ready to published.
    public const STATE_PUBLISHED = 2; // Published. Event has public visibility.

    public const STATUS_SCHEDULED = 1; // Event scheduled (default).
    public const STATUS_RESCHEDULED = 2; // The time slots changed and event is re-scheduled.
    public const STATUS_ONLINE = 3; // The face-to-face event switched to an online event.
    public const STATUS_DEFERRED = 4; // Event deferred, new timings unknowns.
    public const STATUS_FULL = 5; // Event is full.
    public const STATUS_CANCELED = 6; // Event canceled and not re-scheduled.

    public const ACCESS_HI = 'hi'; // Hearing impairment.
    public const ACCESS_II = 'ii'; // Visual impairment.
    public const ACCESS_VI = 'vi'; // Psychic impairment.
    public const ACCESS_MI = 'mi'; // Motor impairment.
    public const ACCESS_PI = 'pi'; // Intellectual impairment.

    public const ATTENDANCE_OFFLINE = 1; // (default): Offline, face-to-face.
    public const ATTENDANCE_ONLINE = 2; // Online event, `onlineAccessLink` is required.
    public const ATTENDANCE_MIXED = 3; // Mixed.

    protected $_schema = [
        'uid' => [],
        'agendaUid' => [],
        'locationUid' => [],
        'slug' => [],
        'title' => ['required' => true],
        'description' => ['type' => 'multilingual', 'required' => true],
        'longDescription' => ['type' => 'multilingual'],
        'conditions' => ['type' => 'multilingual'],
        'keywords' => ['type' => 'multilingual'],
        'image' => [],
        'imageCredits' => [],
        'registration' => [],
        'accessibility' => [],
        'timings' => ['required' => true],
        'type' => [],
        'age' => [],
        'attendanceMode' => [],
        'onlineAccessLink' => [],
        'links' => [],
        'timezone' => [],
        'status' => [],
        'state' => [],
        'featured' => ['type' => 'bool'],
        'createdAt' => ['type' => 'datetime'],
        'updatedAt' => ['type' => 'datetime'],
        'originAgenda' => ['type' => Agenda::class],
        'location' => ['type' => Location::class],
    ];

    /**
     * A method require client sets.
     *
     * @return void
     * @throws \OpenAgenda\OpenAgendaException
     */
    protected function _requireClient(): void
    {
        if (!OpenAgenda::getClient()) {
            throw new OpenAgendaException('OpenAgenda object was not previously created or Client not set.');
        }
    }

    /**
     * Update this location.
     *
     * @return self
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function update(): self
    {
        $this->_requireClient();

        /** @uses \OpenAgenda\Endpoint\Event::update() */
        return EndpointFactory::make('/event', $this->toArray())->update();
    }

    /**
     * Delete this location.
     *
     * @return self
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function delete(): self
    {
        $this->_requireClient();

        /** @uses \OpenAgenda\Endpoint\Event::delete() */
        return EndpointFactory::make('/event', $this->toArray())->delete();
    }

    /**
     * Get Agenda endpoint with params.
     *
     * @param array $params Endpoint params
     * @return \OpenAgenda\Endpoint\Location|\OpenAgenda\Endpoint\Endpoint|
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function agenda(array $params = [])
    {
        $params['uid'] = $this->agendaUid;

        return EndpointFactory::make('/agenda', $params);
    }

    /**
     * Get Location endpoint with params.
     *
     * @param array $params Endpoint params
     * @return \OpenAgenda\Endpoint\Location|\OpenAgenda\Endpoint\Endpoint|
     * @throws \OpenAgenda\Endpoint\UnknownEndpointException
     * @throws \OpenAgenda\OpenAgendaException
     */
    public function location(array $params = [])
    {
        $params['agendaUid'] = $this->agendaUid;

        return EndpointFactory::make('/location', $params);
    }

    /**
     * Set timings.
     *
     * @param array $timings Array of event timings.
     * @return array
     */
    protected function _setTimings(array $timings)
    {
        foreach ($timings as $key => $timing) {
            if (isset($timing['begin']) && is_string($timing['begin'])) {
                $timing['begin'] = Chronos::parse($timing['begin']);
            }
            if (isset($timing['end']) && is_string($timing['end'])) {
                $timing['end'] = Chronos::parse($timing['end']);
            }
            $timings[$key] = $timing;
        }

        return $timings;
    }

    /**
     * @inheritDoc
     */
    public function toOpenAgenda(bool $onlyChanged = false): array
    {
        $data = parent::toOpenAgenda($onlyChanged);

        if (isset($data['location']) && $data['location'] instanceof Location) {
            $data['locationUid'] = $data['location']['uid'];
        }

        // Timings
        $timings = $data['timings'] ?? null;
        if (is_array($timings)) {
            foreach ($timings as &$timing) {
                if ($timing['begin'] instanceof Chronos) {
                    $timing['begin'] = $timing['begin']->toAtomString();
                }
                if ($timing['end'] instanceof Chronos) {
                    $timing['end'] = $timing['end']->toAtomString();
                }
            }
            $data['timings'] = $timings;
        }

        unset(
            $data['uid'],
            $data['agendaId'],
            $data['agendaUid'],
            $data['originAgenda'],
            $data['location']
        );

        return $data;
    }

    /**
     * Event titles is multilingual
     *
     * @param array|string|null $value Event title
     * @return array<string, string>|null
     */
    protected function _setTitle($value): ?array
    {
        if (is_string($value)) {
            $value = [OpenAgenda::getDefaultLang() => $value];
        }

        return $value;
    }

    /**
     * Event descriptions is multilingual
     *
     * @param array|string|null $value Event description
     * @return array<string, string>|null
     */
    protected function _setDescription($value): ?array
    {
        if (is_string($value)) {
            $value = [OpenAgenda::getDefaultLang() => $value];
        }

        if (is_array($value)) {
            foreach ($value as $lang => $text) {
                // remove tags
                $text = strip_tags($text);

                // decode html
                $text = html_entity_decode($text, ENT_QUOTES);

                // remove new lines
                $text = preg_replace(['/\\r?\\n/', '/^\\r?\\n$/', '/^$/'], ' ', $text);

                // remove unused white spaces
                $text = preg_replace('/[\pZ\pC]+/u', ' ', $text);

                if (mb_strlen($text) > 200) {
                    $text = mb_substr($text, 0, 196) . ' ...';
                }

                $value[$lang] = $text;
            }
        }

        return $value;
    }

    /**
     * Event long description is multilingual
     *
     * @param array|string|null $value Event long description
     * @return array<string, string>|null
     */
    protected function _setLongDescription($value): ?array
    {
        if (is_string($value)) {
            $value = [OpenAgenda::getDefaultLang() => $value];
        }

        if (is_array($value)) {
            foreach ($value as $lang => $text) {
                $text = $this->_cleanHtml($text);
                $text = $this->_toMarkDown($text);
                if (mb_strlen($text) > 10000) {
                    $text = mb_substr($text, 0, 9996) . ' ...';
                }

                $value[$lang] = $text;
            }
        }

        return $value;
    }

    /**
     * Event conditions is multilingual
     *
     * @param array|string|null $value Event conditions
     * @return array<string, string>|null
     */
    protected function _setConditions($value): ?array
    {
        if (is_string($value)) {
            $value = [OpenAgenda::getDefaultLang() => $value];
        }

        if (is_array($value)) {
            foreach ($value as $lang => $text) {
                if (mb_strlen($text) > 255) {
                    $text = mb_substr($text, 0, 251) . ' ...';
                }

                $value[$lang] = $text;
            }
        }

        return $value;
    }

    /**
     * Event keywords is multilingual
     *
     * @param array|string|null $value Event keywords
     * @return array<string, string>|null
     */
    protected function _setKeywords($value): ?array
    {
        if (is_string($value)) {
            $value = [OpenAgenda::getDefaultLang() => $value];
        }

        if (is_array($value)) {
            foreach ($value as $lang => $keywords) {
                $keywords = array_map('trim', $keywords);

                $value[$lang] = $keywords;
            }
        }

        return $value;
    }

    /**
     * set event image
     *
     * @param string|null $file Absolute path
     * @return $this
     * @throws \OpenAgenda\OpenAgendaException
     */
    protected function _setImage(?string $file)
    {
        if (empty($file)) {
            return $file;
        }

        if (!file_exists($file)) {
            throw new OpenAgendaException('image file does not exists', 1);
        }

        // set properties, not image to skip auto setDirty
        $this->_fields['image'] = fopen($file, 'r');

        return $file;
    }

    /**
     * clean description html tags
     *
     * @param string $value worse html ever
     * @return string
     */
    protected function _cleanHtml(string $value)
    {
        $config = HTMLPurifier_Config::createDefault();

        $config->set('Cache.DefinitionImpl', null);
        $config->set('HTML.AllowedElements', [
            'a', 'b', 'strong', 'i', 'em', 'u', 'p', 'img', 'hr', 'span',
            'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5',
        ]);
        $config->set('HTML.AllowedAttributes', ['a.href', 'a.target', 'img.src', 'img.alt', 'img.width', 'img.height']);
        $config->set('Attr.AllowedFrameTargets', ['_blank', '_self']);
        $config->set('Attr.AllowedRel', []);
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('AutoFormat.RemoveSpansWithoutAttributes', true);
        $config->set('URI.AllowedSchemes', ['http', 'https']);

        // tag transformation
        $def = $config->getHTMLDefinition(true);
        $def->info_tag_transform['h1'] = new HTMLPurifier_TagTransform_Simple('h3');
        $def->info_tag_transform['h2'] = new HTMLPurifier_TagTransform_Simple('h3');

        $purifier = new HTMLPurifier($config);
        $firstPass = trim($purifier->purify($value));

        if ($this->baseUrl === null) {
            return $firstPass;
        }

        // second pass with url
        $config = HTMLPurifier_Config::createDefault();
        $config->set('URI.Base', $this->baseUrl);
        $config->set('HTML.TargetBlank', true);
        $purifier = new HTMLPurifier($config);

        return trim($purifier->purify($firstPass));
    }

    /**
     * html to markdown converter
     *
     * @param string $html html input
     * @return string
     */
    protected function _toMarkDown(string $html)
    {
        if ($html === strip_tags($html)) {
            return $html;
        }

        $converter = new HtmlConverter(['strip_tags' => true]);

        return $converter->convert($html);
    }
}
