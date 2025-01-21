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

use OpenAgenda\Validation;
use PHPUnit\Framework\TestCase;

/**
 * OpenAgenda\Validation tests
 *
 * @uses \OpenAgenda\Validation
 * @covers \OpenAgenda\Validation
 */
class ValidationTest extends TestCase
{
    public function testUrl(): void
    {
        $this->assertTrue(Validation::url('https://example.com/'));
        $this->assertFalse(Validation::url('not an url'));
        $this->assertFalse(Validation::url(null));
    }

    public static function dataPhone()
    {
        return [
            [[''], false],
            [['+44 20 1234 5678'], true],
            [['01.02_03-04 05'], true],
        ];
    }

    /**
     * @dataProvider dataPhone
     */
    public function testPhone($input, $expected): void
    {
        $success = Validation::phone(...$input);

        $this->assertSame($expected, $success);
    }

    public function testLang(): void
    {
        $this->assertTrue(Validation::lang('fr'));
        $this->assertFalse(Validation::lang('ac'));
    }

    public static function dataMultilingual()
    {
        return [
            // Empty is cool
            [[[]], true],
            // Two languages
            [[['fr' => 'Lorem ipsum', 'en' => 'Lorem ipsum']], true],
            // Invalid language
            [[['fra' => 'Lorem ipsum']], '`fra` is an invalid ISO 639-1 language code.'],
            // Max length overflow
            [[['fr' => 'Lorem ipsum'], 5], 'Value for `fr` exceed size limit.'],
            // Max length overflow for array
            [[['fr' => ['tag1']], 4], 'Value for `fr` exceed size limit.'],
        ];
    }

    /**
     * @dataProvider dataMultilingual
     */
    public function testMultilingual($input, $expected): void
    {
        $success = Validation::multilingual(...$input);

        $this->assertSame($expected, $success);
    }

    public static function dataCheckImage(): array
    {
        $path = 'resources/wendywei-1537637.jpg';
        $realPath = TESTS . $path;

        return [
            [['file'], 1, false],
            ['resources/wendywei-1537637.jpg', 1, false],
            [$realPath, 0.001, false],
            [fopen($realPath, 'r'), 0.001, false],
            [$realPath, 1, true],
            [fopen($realPath, 'r'), 1, true],
            [fopen(__FILE__, 'r'), 10, false],
            [false, 1, true],
        ];
    }

    /** @dataProvider dataCheckImage */
    public function testCheckImage($input, $limit, $expected): void
    {
        $success = Validation::image($input, $limit);
        $this->assertSame($expected, $success);
    }
}
