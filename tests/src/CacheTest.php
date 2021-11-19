<?php
declare(strict_types=1);

namespace OpenAgenda\Test;

use OpenAgenda\Cache;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \OpenAgenda\Cache
 */
class CacheTest extends TestCase
{
    /**
     * @test
     * @covers ::get
     */
    public function testGetNull(): void
    {
        $value = Cache::get('unknown');

        $this->assertNull($value);
    }

    /**
     * @test
     * @covers ::set
     */
    public function testSet(): void
    {
        Cache::set('key', 'testing', 30);

        $value = Cache::get('key');

        $this->assertSame('testing', $value);
    }
}
