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

class ValidationTest extends TestCase
{
    public function dataPhone()
    {
        return [
            [[''], false],
            [['+44 20 1234 5678'], true],
            [['020 1234 5678', 'GB'], true],
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

    public function dataMultilingual()
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
}
