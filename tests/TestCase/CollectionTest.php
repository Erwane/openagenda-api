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
namespace OpenAgenda\Test\TestCase;

use OpenAgenda\Collection;
use OpenAgenda\Entity\Agenda;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testConstructWithArray(): void
    {
        $c = new Collection(['red', 'blue', 'green']);
        $this->assertInstanceOf(Collection::class, $c);
    }

    public function testCount(): void
    {
        $c = new Collection(['red', 'blue', 'green']);
        $this->assertEquals(3, count($c));
    }

    public static function dataFirst()
    {
        return [
            [[], null],
            [['red', 'blue', 'green'], 'red'],
        ];
    }

    /** @dataProvider dataFirst */
    public function testFirst(): void
    {
        $c = new Collection(['red', 'blue', 'green']);
        $this->assertEquals('red', $c->first());
    }

    public static function dataLast()
    {
        return [
            [[], null],
            [['red', 'blue', 'green'], 'green'],
        ];
    }

    /** @dataProvider dataLast */
    public function testLast($input, $expected): void
    {
        $c = new Collection($input);
        $this->assertEquals($expected, $c->last());
    }

    public function testToArray()
    {
        $c = new Collection([
            'red',
            new Agenda(['uid' => 1]),
        ]);

        $this->assertSame([
            'red',
            ['uid' => 1],
        ], $c->toArray());
    }

    public function testSerializeJson()
    {
        $c = new Collection([
            'item',
            'colors' => ['red', 'green'],
            'agenda' => new Agenda(['uid' => 1]),
        ]);

        $json = json_encode($c);
        $expected = json_encode([
            'item',
            'colors' => ['red', 'green'],
            'agenda' => ['uid' => 1],
        ]);
        $this->assertJsonStringEqualsJsonString($expected, $json);
    }
}
