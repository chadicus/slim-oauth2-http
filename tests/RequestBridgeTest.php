<?php

namespace ChadicusTest\Slim\OAuth2\Http;

use Chadicus\Slim\OAuth2\Http\RequestBridge;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\UploadedFile;

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
        $uri = 'https://example.com/foo/bar';
        $headers = ['Host' => ['example.com'], 'Accept' => ['application/json', 'text/json']];
        $cookies = ['PHPSESSID' => uniqid()];
        $server = ['SCRIPT_NAME'     => __FILE__, 'SCRIPT_FILENAME' => __FILE__];
        $json = json_encode(['foo' => 'bar', 'abc' => '123']);

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $json);
        rewind($stream);

        $files = [
            'foo' => new UploadedFile(
                __FILE__,
                100,
                UPLOAD_ERR_OK,
                'foo.txt',
                'text/plain'
            ),
        ];

        $psr7Request = new ServerRequest($server, $files, $uri, 'PATCH', $stream, $headers, $cookies, ['baz' => 'bat']);

        $oauth2Request = RequestBridge::toOauth2($psr7Request);

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
        $uri = 'https://example.com/foos';

        $data = ['foo' => 'bar', 'abc' => '123'];

        $json = json_encode($data);
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $json);
        rewind($stream);

        $headers = [
            'Content-Type' => ['application/json'],
            'Content-Length' => [strlen($json)],
        ];

        $psr7Request = new ServerRequest([], [], $uri, 'POST', $stream, $headers, [], [], $data);

        $oauth2Request = RequestBridge::toOAuth2($psr7Request);

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
        $uri = 'https://example.com/foos';

        $headers = [
            'Php-Auth-User' => ['test_client_id'],
            'Php-Auth-Pw' => ['test_secret'],
        ];

        $psr7Request = new ServerRequest([], [], $uri, 'GET', 'php://input', $headers);

        $oauth2Request = RequestBridge::toOAuth2($psr7Request);

        $this->assertSame('test_client_id', $oauth2Request->headers('PHP_AUTH_USER'));
        $this->assertSame('test_secret', $oauth2Request->headers('PHP_AUTH_PW'));
        $this->assertNull($oauth2Request->headers('Php-Auth-User'));
        $this->assertNull($oauth2Request->headers('Php-Auth-Pw'));
    }

    /**
     * Verify behavior of PSR-7 request with Authorization header.
     *
     * @test
     * @covers ::toOAuth2
     *
     * @return void
     */
    public function toOAuth2WithAuthorization()
    {
        $uri = 'https://example.com/foos';

        $headers = ['HTTP_AUTHORIZATION' => ['Bearer abc123']];

        $psr7Request = new ServerRequest([], [], $uri, 'GET', 'php://input', $headers);

        $oauth2Request = RequestBridge::toOAuth2($psr7Request);

        $this->assertSame('Bearer abc123', $oauth2Request->headers('AUTHORIZATION'));
    }
}
