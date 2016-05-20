<?php
namespace Chadicus\Slim\OAuth2\Http;

use Slim\Http\Headers;
use Slim\Http\Response;
use Slim\Http\Stream;
use OAuth2;

/**
 * Static utility class for bridging OAuth2 responses to PSR-7 responses.
 */
class ResponseBridge
{
    /**
     * Copies values from the given Oauth2\Response to a Slim Response.
     *
     * @param OAuth2\ResponseInterface $oauth2Response The OAuth2 server response.
     *
     * @return Response
     */
    final public static function fromOauth2(OAuth2\ResponseInterface $oauth2Response)
    {
        $headers = new Headers();
        foreach ($oauth2Response->getHttpHeaders() as $key => $value) {
            $headers->set($key, explode(', ', $value));
        }

        $body = new Stream(fopen('php://temp', 'r'));
        if (!empty($oauth2Response->getParameters())) {
            $stream = fopen('php://memory','r+');
            fwrite($stream, $oauth2Response->getResponseBody());
            rewind($stream);
            $body = new Stream($stream);
        }

        return new Response($oauth2Response->getStatusCode(), $headers, $body);
    }
}
