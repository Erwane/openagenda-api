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

use DateTimeImmutable;
use OpenAgenda\DateTime;
use PHPUnit\Framework\TestCase;

class DateTimeTest extends TestCase
{
    public static function dataParse(): array
    {
        return [
            [NOW],
            [new DateTime(NOW)],
            [new DateTimeImmutable(NOW)],
        ];
    }

    /** @dataProvider dataParse */
    public function testParse($input): void
    {
        $d = DateTime::parse($input);
        $this->assertInstanceOf(DateTime::class, $d);
    }

    public function testToAtomString(): void
    {
        $d = DateTime::parse(NOW);
        $this->assertEquals(NOW, $d->toAtomString());
    }
}
