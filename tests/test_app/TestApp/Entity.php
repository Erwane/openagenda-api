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
namespace OpenAgenda\Test\test_app\TestApp;

use OpenAgenda\Entity\Agenda;
use OpenAgenda\Entity\Event;
use OpenAgenda\Entity\Location;

/**
 * Entity for tests
 *
 * @property int $id
 * @property int $uid
 * @property string $postalCode
 * @property \OpenAgenda\DateTime $createdAt
 * @property array $description
 * @property bool $state
 * @coversNothing
 */
class Entity extends \OpenAgenda\Entity\Entity
{
    protected array $_schema = [
        'uid' => ['required' => true],
        'postalCode' => ['type' => 'string'],
        'createdAt' => ['type' => 'datetime'],
        'description' => ['type' => 'json'],
        'state' => ['type' => 'bool'],
        'agenda' => ['type' => Agenda::class],
        'location' => ['type' => Location::class],
        'event' => ['type' => Event::class],
        'image' => ['type' => 'file'],
    ];

    protected function _getId()
    {
        return $this->uid;
    }
}
