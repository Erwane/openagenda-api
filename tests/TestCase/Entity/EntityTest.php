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
namespace OpenAgenda\Test\TestCase\Entity;

use Cake\Chronos\Chronos;
use OpenAgenda\Test\test_app\TestApp\Entity as ent;
use PHPUnit\Framework\TestCase;

/**
 * Entity\Entity tests
 *
 * @uses   \OpenAgenda\Entity\Entity
 * @covers \OpenAgenda\Entity\Entity
 */
class EntityTest extends TestCase
{
    /**
     * @covers \OpenAgenda\Entity\Entity::fromOpenAgenda
     */
    public function testConstructFromOpenAgenda()
    {
        $ent = new ent([
            'uid' => '1',
            'postalCode' => '12345',
            'createdAt' => Chronos::now()->toAtomString(),
            'description' => json_encode(['fr' => 'Lorem ipsum']),
            'state' => 1,
            'unknownField' => 'value',
        ]);

        $this->assertEquals([
            'uid' => 1,
            'postalCode' => '12345',
            'createdAt' => Chronos::parse('2024-12-23T12:34:56+00:00'),
            'state' => true,
            'description' => ['fr' => 'Lorem ipsum'],
        ], $ent->toArray());

        // boolean field
        $this->assertTrue($ent->state);

        // Unknown field exists in fields / property
        $this->assertEquals('value', $ent->unknownField);
    }

    public function testConstructNoSetter()
    {
        $ent = new ent(['uid' => '1'], ['useSetters' => false]);
        $this->assertSame('1', $ent->uid);
    }

    public function testSetter()
    {
        /** @uses \OpenAgenda\Entity\Entity::_setUid() */
        $ent = new ent(['uid' => '1']);
        $this->assertSame(1, $ent->uid);
    }

    public function testSetProperty()
    {
        $ent = new Ent();
        $ent->uid = '1';
        $this->assertSame(1, $ent->uid);
    }

    public function testToOpenAgenda()
    {
        $now = Chronos::now();
        $ent = new ent([
            'uid' => 1,
            'postalCode' => '12345',
            'createdAt' => $now,
            'description' => ['fr' => 'Lorem ipsum'],
            'state' => true,
            'unknownField' => 'value',
        ]);

        $this->assertSame([
            'uid' => 1,
            'postalCode' => '12345',
            'createdAt' => '2024-12-23T12:34:56',
            'description' => ['fr' => 'Lorem ipsum'],
            'state' => 1,
        ], $ent->toOpenAgenda());
    }

    public function testToOpenAgendaChanged(): void
    {
        $ent = new ent([
            'uid' => 1,
            'postalCode' => '12345',
            'state' => true,
        ], ['markClean' => true]);

        $ent->set('state', false);

        $this->assertSame([
            'uid' => 1,
            'state' => 0,
        ], $ent->toOpenAgenda(true));
    }
}
