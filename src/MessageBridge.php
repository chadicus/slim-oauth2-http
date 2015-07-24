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

        return new \OAuth2\Request(
            $request->get(),
            $post,
            [],
            $request->cookies()->getIterator()->getArrayCopy(),
            [],
            \Slim\Environment::getInstance()->getIterator()->getArrayCopy(),
            $request->getBody(),
            $request->headers()->getIterator()->getArrayCopy()
        );
    }

    /**
     * Copies values from the given \Oauth2\Response to the given \Slim\Http\Response.
     *
     * @param \OAuth2\Response    $oauth2Response The OAuth2 server response.
     * @param \Slim\Http\Response $slimResponse   The slim framework response.
     *
     * @return void
     */
    public static function mapResponse(\OAuth2\Response $oauth2Response, \Slim\Http\Response $slimResponse)
    {
        foreach ($oauth2Response->getHttpHeaders() as $key => $value) {
            $slimResponse->headers->set($key, $value);
        }

        $slimResponse->status($oauth2Response->getStatusCode());
        $slimResponse->setBody($oauth2Response->getResponseBody());
    }
}
