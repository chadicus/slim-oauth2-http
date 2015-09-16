<?php
namespace Chadicus\Slim\OAuth2\Http;

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

        $headers = $request->headers()->getIterator()->getArrayCopy();
        // Fixing bad headers from Slim
        $badHeaders = ['Php-Auth-User','Php-Auth-Pw','Php-Auth-Digest','Auth-Type'];
        $goodHeaders = ['PHP_AUTH_USER','PHP_AUTH_PW','PHP_AUTH_DIGEST','AUTH_TYPE'];

        foreach($badHeaders as $key => $badHeaderName) {
            if(array_key_exists($badHeaderName,$headers)) {
                $headers[$goodHeaders[$key]] = $headers[$badHeaderName];
                unset($headers[$badHeaderName]);
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
            $headers
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
}
