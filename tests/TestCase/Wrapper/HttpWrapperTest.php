<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types=1);

namespace OpenAgenda\Test\TestCase\Wrapper;

use League\Uri\Uri as LeagueUri;
use OpenAgenda\Wrapper\HttpWrapper;
use PHPUnit\Framework\TestCase;

/**
 * @uses   \OpenAgenda\Wrapper\HttpWrapper
 * @covers \OpenAgenda\Wrapper\HttpWrapper
 */
class HttpWrapperTest extends TestCase
{
    public static function dataBuildUri()
    {
        return [
            [
                'https://www.example.com',
            ],
            [
                LeagueUri::createFromString('https://www.example.com'),
            ],
        ];
    }

    /**
     * @dataProvider dataBuildUri
     */
    public function testBuildUri($input)
    {
        $wrapper = $this->getMockForAbstractClass(
            HttpWrapper::class,
            [],
            '',
            false
        );

        $uri = $wrapper->buildUri($input);
        $this->assertInstanceOf(LeagueUri::class, $uri);
    }
}
