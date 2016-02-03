<?php
namespace Chadicus\Slim\OAuth2\Http;

/**
 * Static utility class for bridging Slim Requests/Response to OAuth2 Requests/Response.
 */
class MessageBridge
{
    /**
     * Returns a new instance of \OAuth2\Request based on the given \Slim\Http\Request
     *
     * @param \Slim\Http\Request $request The slim framework request.
     *
     * @return \OAuth2\Request
     */
    public static function newOauth2Request(\Slim\Http\Request $request)
    {
        $post = $request->post();
        if (substr_count($request->headers()->get('Content-Type'), '/json')) {
            $post = $request->getBody();
            if (is_string($post)) {
                $post = json_decode($post, true) ?: [];
            }
        }

        return new \OAuth2\Request(
            $request->get(),
            $post,
            [],
            $request->cookies()->getIterator()->getArrayCopy(),
            [],
            \Slim\Environment::getInstance()->getIterator()->getArrayCopy(),
            $request->getBody(),
            self::cleanupHeaders($request->headers())
        );
    }

    /**
     * Copies values from the given \Oauth2\Response to the given \Slim\Http\Response.
     *
     * @param \OAuth2\ResponseInterface $oauth2Response The OAuth2 server response.
     * @param \Slim\Http\Response       $slimResponse   The slim framework response.
     *
     * @return void
     */
    public static function mapResponse(\OAuth2\ResponseInterface $oauth2Response, \Slim\Http\Response $slimResponse)
    {
        foreach ($oauth2Response->getHttpHeaders() as $key => $value) {
            $slimResponse->headers->set($key, $value);
        }

        $slimResponse->status($oauth2Response->getStatusCode());
        $slimResponse->setBody($oauth2Response->getResponseBody());
    }

    /**
     * Helper method to clean header keys.
     *
     * Slim will convert all headers to Camel-Case style. There are certain headers such as PHP_AUTH_USER that the
     * OAuth2 library requires CAPS_CASE format. This method will adjust those headers as needed.
     *
     * @param \Slim\Http\Headers $uncleanHeaders The headers to be cleaned.
     *
     * @return array The cleaned headers
     */
    private static function cleanupHeaders(\Slim\Http\Headers $uncleanHeaders)
    {
        $cleanHeaders = [];
        $headerMap = [
            'Php-Auth-User' => 'PHP_AUTH_USER',
            'Php-Auth-Pw' => 'PHP_AUTH_PW',
            'Php-Auth-Digest' => 'PHP_AUTH_DIGEST',
            'Auth-Type' => 'AUTH_TYPE',
        ];
        foreach ($uncleanHeaders as $key => $value) {
            if (!array_key_exists($key, $headerMap)) {
                $cleanHeaders[$key] = $value;
                continue;
            }

            $cleanHeaders[$headerMap[$key]] = $value;
        }

        return $cleanHeaders;
    }
}
