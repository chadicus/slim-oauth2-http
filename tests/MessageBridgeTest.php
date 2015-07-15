<?php

namespace ChadicusTest\Slim\OAuth2\Http;

use Chadicus\Slim\OAuth2\Http\MessageBridge;

/**
 * Unit tests for the \Chadicus\Slim\OAuth2\Http\MessageBridge class.
 *
 * @coversDefaultClass \Chadicus\Slim\OAuth2\Http\MessageBridge
 * @covers ::<private>
 */
final class MessageBridgeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Verify basic behavior of newOAuth2Request()
     *
     * @test
     * @covers ::newOAuth2Request
     *
     * @return void
     */
    public function newOAuth2Request()
    {
        $env = \Slim\Environment::mock(
            [
                'REQUEST_METHOD' => 'POST',
                'QUERY_STRING' => 'one=1&two=2&three=3',
                'slim.input' => 'foo=bar&abc=123',
                'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
                'CONTENT_LENGTH' => 15,
            ]
        );

        $slimRequest = new \Slim\Http\Request($env);

        $oauth2Request = MessageBridge::newOauth2Request($slimRequest);

        $this->assertSame(15, $oauth2Request->headers('Content-Length'));
        $this->assertSame('application/x-www-form-urlencoded', $oauth2Request->headers('Content-Type'));
        $this->assertSame('123', $oauth2Request->request('abc'));
        $this->assertSame('2', $oauth2Request->query('two'));

    }

    /**
     * Verify behavior of newOAuth2Request() with application/json content type
     *
     * @test
     * @covers ::newOAuth2Request
     *
     * @return void
     */
    public function newOAuth2RequestJsonContentType()
    {
        $json = json_encode(
            [
                'foo' => 'bar',
                'abc' => '123',
            ]
        );
        $env = \Slim\Environment::mock(
            [
                'REQUEST_METHOD' => 'POST',
                'slim.input' => $json,
                'CONTENT_LENGTH' => strlen($json),
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $slimRequest = new \Slim\Http\Request($env);

        $oauth2Request = MessageBridge::newOauth2Request($slimRequest);

        $this->assertSame(strlen($json), $oauth2Request->headers('Content-Length'));
        $this->assertSame('application/json', $oauth2Request->headers('Content-Type'));
        $this->assertSame('bar', $oauth2Request->request('foo'));
        $this->assertSame('123', $oauth2Request->request('abc'));
    }

    /**
     * Verify basic behavior of mapResponse()
     *
     * @test
     * @covers ::mapResponse
     *
     * @return void
     */
    public function mapResponse()
    {
        $oauth2Response =  new \OAuth2\Response(
            ['foo' => 'bar', 'abc' => '123'],
            200,
            ['content-type' => 'application/json', 'fizz' => 'buzz']
        );
        $slimResponse = new \Slim\Http\Response('will be over written', 500, []);

        MessageBridge::mapResponse($oauth2Response, $slimResponse);

        $this->assertSame(200, $slimResponse->status());
        $this->assertSame(
            ['Content-Type' => 'application/json', 'Fizz' => 'buzz'],
            $slimResponse->headers()->getIterator()->getArrayCopy()
        );

        $this->assertSame(json_encode(['foo' => 'bar', 'abc' => '123']), $slimResponse->getBody());
    }
}
