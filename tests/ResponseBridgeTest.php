<?php

namespace ChadicusTest\Slim\OAuth2\Http;

use Chadicus\Slim\OAuth2\Http\ResponseBridge;
use OAuth2\Response;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the \Chadicus\Slim\OAuth2\Http\ResponseBridge class.
 *
 * @coversDefaultClass \Chadicus\Slim\OAuth2\Http\ResponseBridge
 * @covers ::<private>
 */
final class ResponseBridgeTest extends TestCase
{
    /**
     * Verify basic behavior of fromOAuth2()
     *
     * @test
     * @covers ::fromOAuth2
     *
     * @return void
     */
    public function fromOAuth2()
    {
        $oauth2Response =  new Response(
            ['foo' => 'bar', 'abc' => '123'],
            200,
            ['Content-Type' => 'application/json', 'Accept-Encoding' => 'gzip, deflate']
        );

        $slimResponse = ResponseBridge::fromOAuth2($oauth2Response);

        $this->assertSame(200, $slimResponse->getStatusCode());
        $this->assertSame(
            [
                'Content-Type' => [
                    'application/json',
                ],
                'Accept-Encoding' => [
                    'gzip',
                    'deflate',
                ],
            ],
            $slimResponse->getHeaders()
        );

        $this->assertSame(json_encode(['foo' => 'bar', 'abc' => '123']), (string)$slimResponse->getBody());
    }

    /**
     * Verify behavior of fromOAuth2() with empty response body.
     *
     * @test
     * @covers ::fromOAuth2
     *
     * @return void
     */
    public function fromOAuth2EmptyBody()
    {
        $oauth2Response =  new Response(
            [],
            204,
            ['Content-Type' => 'application/json']
        );

        $slimResponse = ResponseBridge::fromOAuth2($oauth2Response);

        $this->assertSame(204, $slimResponse->getStatusCode());
        $this->assertSame(
            [
                'Content-Type' => [
                    'application/json',
                ],
            ],
            $slimResponse->getHeaders()
        );

        $this->assertSame('', (string)$slimResponse->getBody());
    }

    /**
     * Verify response can be written to when empty.
     *
     * @test
     * @covers ::fromOAuth2
     *
     * @return void
     */
    public function fromOAuth2WritableEmptyBody()
    {
        $oauth2Response =  new Response(
            [],
            204,
            ['Content-Type' => 'application/json']
        );

        $slimResponse = ResponseBridge::fromOAuth2($oauth2Response);

        $slimResponse->getBody()->write('I was here');

        $this->assertSame(204, $slimResponse->getStatusCode());
        $this->assertSame(
            [
                'Content-Type' => [
                    'application/json',
                ],
            ],
            $slimResponse->getHeaders()
        );

        $this->assertSame('I was here', (string)$slimResponse->getBody());
    }
}
