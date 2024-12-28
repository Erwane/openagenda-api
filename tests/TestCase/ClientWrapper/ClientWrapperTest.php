<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types=1);

namespace OpenAgenda\Test\TestCase\ClientWrapper;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Psr7\Uri as GuzzleUri;
use League\Uri\Uri as LeagueUri;
use OpenAgenda\ClientWrapper\ClientWrapper;
use OpenAgenda\ClientWrapper\ClientWrapperInterface;
use OpenAgenda\ClientWrapper\UnknownClientException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;

/**
 * @uses   \OpenAgenda\ClientWrapper\ClientWrapper
 * @covers \OpenAgenda\ClientWrapper\ClientWrapper
 */
class ClientWrapperTest extends TestCase
{
    public function testUnknownHttpClient()
    {
        $http = $this->getMockBuilder(ClientInterface::class)
            ->setMockClassName('TestingHttpClient')
            ->getMock();
        $this->expectException(UnknownClientException::class);
        $this->expectExceptionMessage('Http client "TestingHttpClient" is not supported yet. Please open issue or PR with your client class name.');
        ClientWrapper::build($http);
    }

    public static function dataBuild()
    {
        return [
            [Guzzle::class],
        ];
    }

    /**
     * @dataProvider dataBuild
     */
    public function testBuild($httpClass)
    {
        $http = new $httpClass();
        $client = ClientWrapper::build($http);

        $this->assertInstanceOf(ClientWrapperInterface::class, $client);
    }

    public static function dataBuildUri()
    {
        return [
            [
                'https://www.example.com',
            ],
            [
                new GuzzleUri('https://www.example.com'),
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
            ClientWrapper::class,
            [],
            '',
            false
        );

        $uri = $wrapper->buildUri($input);
        $this->assertInstanceOf(LeagueUri::class, $uri);
    }
}
