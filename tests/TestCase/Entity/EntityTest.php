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

use InvalidArgumentException;
use OpenAgenda\DateTime;
use OpenAgenda\Entity\Agenda;
use OpenAgenda\Entity\Entity;
use OpenAgenda\Entity\Event;
use OpenAgenda\Entity\Location;
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
    public function testConstructFromOpenAgenda()
    {
        $ent = new ent([
            'uid' => '1',
            'postalCode' => '12345',
            'createdAt' => '2024-12-23T12:34:56+00:00',
            'description' => json_encode(['fr' => 'Lorem ipsum']),
            'state' => 1,
            'unknownField' => 'value',
            'agenda' => ['uid' => 123],
            'location' => new Location(['agendaUid' => 123, 'uid' => 456]),
            'event' => ['agendaUid' => 123, 'uid' => 789],
        ]);

        $result = $ent->toArray();

        [$created, $agenda, $location, $event] = [
            $result['createdAt'],
            $result['agenda'],
            $result['location'],
            $result['event'],
        ];
        unset($result['createdAt'], $result['agenda'], $result['location'], $result['event']);

        $this->assertSame([
            'uid' => 1,
            'postalCode' => '12345',
            'description' => ['fr' => 'Lorem ipsum'],
            'state' => true,
            'image' => null,
        ], $result);

        $this->assertEquals([
            DateTime::parse('2024-12-23T12:34:56+00:00'),
            [
                'uid' => 123,

                'title' => null,
                'slug' => null,
                'description' => null,
                'url' => null,
                'image' => null,
                'official' => null,
                'private' => null,
                'indexed' => null,
                'networkUid' => null,
                'locationSetUid' => null,
                'createdAt' => null,
                'updatedAt' => null,
            ],
            [
                'agendaUid' => 123, 'uid' => 456,

                'name' => null,
                'address' => null,
                'access' => null,
                'description' => null,
                'image' => null,
                'imageCredits' => null,
                'slug' => null,
                'locationSetUid' => null,
                'city' => null,
                'department' => null,
                'region' => null,
                'postalCode' => null,
                'insee' => null,
                'countryCode' => null,
                'district' => null,
                'latitude' => 0.0,
                'longitude' => 0.0,
                'createdAt' => null,
                'updatedAt' => null,
                'website' => null,
                'email' => null,
                'phone' => null,
                'links' => null,
                'timezone' => null,
                'extId' => null,
                'state' => false,
            ],
            [
                'agendaUid' => 123, 'uid' => 789,

                'locationUid' => null,
                'slug' => null,
                'title' => null,
                'description' => null,
                'longDescription' => null,
                'conditions' => null,
                'keywords' => null,
                'image' => null,
                'imageCredits' => null,
                'registration' => null,
                'accessibility' => null,
                'timings' => null,
                'type' => null,
                'age' => null,
                'attendanceMode' => null,
                'onlineAccessLink' => null,
                'links' => null,
                'timezone' => null,
                'status' => null,
                'state' => null,
                'featured' => null,
                'createdAt' => null,
                'updatedAt' => null,
                'originAgenda' => null,
                'location' => null,
            ],
        ], [$created, $agenda, $location, $event]);

        // boolean field
        $this->assertTrue($ent->state);

        // Unknown field exists in fields / property
        $this->assertEquals('value', $ent->get('unknownField'));
    }

    public function testConstructNoSetter()
    {
        $ent = new ent(['uid' => '1'], ['useSetters' => false]);
        $this->assertSame('1', $ent->uid);
        $this->assertTrue($ent->isDirty());
        $this->assertTrue($ent->isNew());
    }

    public function testConstructNoSetterAndClean()
    {
        $ent = new ent(['uid' => '1'], ['markClean' => true, 'useSetters' => false]);
        $this->assertSame('1', $ent->uid);
        $this->assertFalse($ent->isDirty());
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
        $this->assertTrue($ent->has('uid'));
    }

    public function testSetEmpty(): void
    {
        $ent = new Ent();
        $this->expectException(InvalidArgumentException::class);
        $ent->set(null, '');
    }

    public function testGetEmpty(): void
    {
        $ent = new Ent();
        $this->expectException(InvalidArgumentException::class);
        $ent->get('');
    }

    public function testGetWithAccessor(): void
    {
        $ent = new Ent(['uid' => 1]);
        $this->assertSame(1, $ent->id);
    }

    public function testToOpenAgenda()
    {
        $ent = new ent([
            'uid' => 1,
            'postalCode' => '12345',
            'createdAt' => DateTime::parse(NOW),
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

    public function testToOpenAgendaFile(): void
    {
        $ent = new ent([
            'image' => TESTS . 'resources/wendywei-1537637.jpg',
        ]);
        $this->assertIsResource($ent->toOpenAgenda()['image']);
    }

    public function testArrayAccess()
    {
        $ent = new ent();

        $this->assertFalse(isset($ent['offsetExists']));
        $this->assertNull($ent['offsetGet']);
        $ent['offsetSet'] = 'value';
        $this->assertSame('value', $ent['offsetSet']);
        unset($ent['offsetSet']);
        $this->assertNull($ent['offsetSet']);
    }

    public function testSetNew(): void
    {
        $ent = new ent(['uid' => 1], ['markClean' => true]);
        $this->assertFalse($ent->isDirty('uid'));
        $ent->setNew(true);
        $this->assertTrue($ent->isDirty('uid'));
    }

    public function testExtractOnlyDirty(): void
    {
        $ent = new ent(['uid' => 1], ['markClean' => true]);
        $ent->description = ['fr' => 'lorem ipsum'];
        $this->assertEquals([
            'description' => ['fr' => 'lorem ipsum'],
        ], $ent->extract(['uid', 'description'], true));
    }

    public function testIsDirty(): void
    {
        $ent = new ent([
            'id' => 99,
            'description' => ['fr' => 'lorem'],
        ], ['markClean' => true]);

        $ent->id = 99;
        $ent->description = ['fr' => 'lorem ipsum'];
        $this->assertTrue($ent->isDirty());
    }

    public function testDirtyNotSetWhenNoDiff()
    {
        $ent = new ent([
            'id' => 99,
            'description' => ['fr' => 'lorem'],
        ], ['markClean' => true]);
        $this->assertFalse($ent->isDirty());
        $ent->id = 99;
        $ent->description = ['fr' => 'lorem'];
        $this->assertFalse($ent->isDirty());
    }

    public static function dataSetImage(): array
    {
        $realPath = TESTS . 'resources/wendywei-1537637.jpg';
        $resource = fopen($realPath, 'r');

        return [
            [null, null],
            [$realPath, $realPath],
            ['https://example.com', 'https://example.com'],
            [$resource, $resource],
            [false, false],
        ];
    }

    /**
     * @dataProvider dataSetImage
     * @covers       \OpenAgenda\Entity\Event::_setImage
     */
    public function testSetImage($image, $expected): void
    {
        $entity = new Event(['image' => $image]);
        $this->assertSame($expected, $entity->image);
    }

    public static function dataNoHtml(): array
    {
        return [
            ['simple string', true, 'simple string'],
            ["new\n  \nline", true, "new\n \nline"],
            ["new\n\nlines\n", false, 'new lines'],
            ['ça d&eacute;veloppe', true, 'ça développe'],
            ['<span>this</span> <a href="not this">is a</a>  weird text', true, 'this is a weird text'],
            [
                <<<HTML
<span>This</span> description
<p>should be on <a href="not this">one</a></p>
<ul>
<li>line </li>
<li>and clean.</li>
</ul>
HTML
                , false, 'This description should be on one line and clean.',
            ],
        ];
    }

    /** @dataProvider dataNoHtml */
    public function testNoHtml($html, $keep, $expected): void
    {
        $result = Entity::noHtml($html, $keep);
        $this->assertSame($expected, $result);
    }

    /** @covers \OpenAgenda\Entity\Entity::setMultilingual */
    public function testSetMultilingual(): void
    {
        $string = 'A <b>Multilingual</b> string with html';
        $result = Entity::setMultilingual($string, true, 10);
        $this->assertEquals(['fr' => 'A Mult ...'], $result);
    }
}
