<?php

namespace ChadicusTest\Slim\OAuth2\Http;

use Chadicus\Slim\OAuth2\Http\RequestBridge;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Stream;
use Slim\Http\UploadedFile;
use Slim\Http\Uri;

/**
 * Unit tests for the \Chadicus\Slim\OAuth2\Http\RequestBridge class.
 *
 * @coversDefaultClass \Chadicus\Slim\OAuth2\Http\RequestBridge
 * @covers ::<private>
 */
final class RequestBridgeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Verify basic behavior of toOAuth2()
     *
     * @test
     * @covers ::toOAuth2
     *
     * @return void
     */
    public function toOAuth2()
    {
        $uri = Uri::createFromString('https://example.com/foo/bar?baz=bat');

        $headers = new Headers();
        $headers->add('Host', 'example.com');
        $headers->add('Accept', 'application/json');
        $headers->add('Accept', 'text/json');

        $cookies = [
            'PHPSESSID' => uniqid(),
        ];

        $server = [
            'SCRIPT_NAME'     => __FILE__,
            'SCRIPT_FILENAME' => __FILE__,
        ];

        $json = json_encode(
            [
                'foo' => 'bar',
                'abc' => '123',
            ]
        );

        $stream = fopen('php://memory','r+');
        fwrite($stream, $json);
        rewind($stream);
        $body = new Stream($stream);

        $files = [
            'foo' => new UploadedFile(
                __FILE__,
                'foo.txt',
                'text/plain',
                100,
                UPLOAD_ERR_OK
            ),
        ];

        $slimRequest = new Request('PATCH', $uri, $headers, $cookies, $server, $body, $files);

        $oauth2Request = RequestBridge::toOauth2($slimRequest);

        $this->assertInstanceOf('\OAuth2\Request', $oauth2Request);
        $this->assertSame('bat', $oauth2Request->query('baz'));
        $this->assertSame('example.com', $oauth2Request->headers('Host'));
        $this->assertSame('application/json, text/json', $oauth2Request->headers('Accept'));
        $this->assertSame($cookies, $oauth2Request->cookies);

        $this->assertSame(__FILE__, $oauth2Request->server('SCRIPT_NAME'));

        $this->assertSame($json, $oauth2Request->getContent());

        $this->assertSame(
            [
                'foo' => [
                    'name' => 'foo.txt',
                    'type' => 'text/plain',
                    'size' => 100,
                    'tmp_name' => __FILE__,
                    'error' => UPLOAD_ERR_OK,
                ],
            ],
            $oauth2Request->files
        );
    }

    /**
     * Verify behavior of toOAuth2() with application/json content type
     *
     * @test
     * @covers ::toOAuth2
     *
     * @return void
     */
    public function toOAuth2JsonContentType()
    {
        $uri = Uri::createFromString('https://example.com/foos');

        $json = json_encode(
            [
                'foo' => 'bar',
                'abc' => '123',
            ]
        );

        $stream = fopen('php://memory','r+');
        fwrite($stream, $json);
        rewind($stream);
        $body = new Stream($stream);

        $headers = new Headers();
        $headers->add('Content-Type', 'application/json');
        $headers->add('Content-Length', strlen($json));

        $slimRequest = new Request('POST', $uri, $headers, [], [], $body, []);

        $oauth2Request = RequestBridge::toOAuth2($slimRequest);

        $this->assertSame((string)strlen($json), $oauth2Request->headers('Content-Length'));
        $this->assertSame('application/json', $oauth2Request->headers('Content-Type'));
        $this->assertSame('bar', $oauth2Request->request('foo'));
        $this->assertSame('123', $oauth2Request->request('abc'));
    }

    /**
     * Verify behavior of replacing bad header key names
     *
     * @test
     * @covers ::toOAuth2
     *
     * @return void
     */
    public function toOAuth2HeaderKeyNames()
    {
        $uri = Uri::createFromString('https://example.com/foos');

        $headers = new Headers();
        $headers->add('Php-Auth-User', 'test_client_id');
        $headers->add('Php-Auth-Pw', 'test_secret');

        $slimRequest = new Request('GET', $uri, $headers, [], [], new RequestBody());

        $oauth2Request = RequestBridge::toOAuth2($slimRequest);

        $this->assertSame('test_client_id', $oauth2Request->headers('PHP_AUTH_USER'));
        $this->assertSame('test_secret', $oauth2Request->headers('PHP_AUTH_PW'));
        $this->assertNull($oauth2Request->headers('Php-Auth-User'));
        $this->assertNull($oauth2Request->headers('Php-Auth-Pw'));
    }
}
