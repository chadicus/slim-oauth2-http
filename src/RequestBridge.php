<?php
namespace Chadicus\Slim\OAuth2\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use OAuth2;

/**
 * Static utility class for bridging Psr-7 Requests to OAuth2 Requests.
 */
class RequestBridge
{
    /**
     * Returns a new instance of \OAuth2\Request based on the given \Slim\Http\Request
     *
     * @param ServerRequestInterface $request The psr-7 request.
     *
     * @return OAuth2\Request
     */
    final public static function toOAuth2(ServerRequestInterface $request)
    {
        $contents = $request->getBody()->getContents();
        $request->getBody()->rewind();

        return new OAuth2\Request(
            (array)$request->getQueryParams(),
            (array)$request->getParsedBody(),
            $request->getAttributes(),
            $request->getCookieParams(),
            self::convertUploadedFiles($request->getUploadedFiles()),
            $request->getServerParams(),
            $contents,
            self::cleanupHeaders($request->getHeaders())
        );
    }

    /**
     * Helper method to clean header keys and values.
     *
     * Slim will convert all headers to Camel-Case style. There are certain headers such as PHP_AUTH_USER that the
     * OAuth2 library requires CAPS_CASE format. This method will adjust those headers as needed.  The OAuth2 library
     * also does not expect arrays for header values, this method will implode the multiple values with a ', '
     *
     * @param array $uncleanHeaders The headers to be cleaned.
     *
     * @return array The cleaned headers
     */
    private static function cleanupHeaders(array $uncleanHeaders = [])
    {
        $cleanHeaders = [];
        $headerMap = [
            'Php-Auth-User' => 'PHP_AUTH_USER',
            'Php-Auth-Pw' => 'PHP_AUTH_PW',
            'Php-Auth-Digest' => 'PHP_AUTH_DIGEST',
            'Auth-Type' => 'AUTH_TYPE',
            'HTTP_AUTHORIZATION' => 'AUTHORIZATION',
        ];

        foreach ($uncleanHeaders as $key => $value) {
            if (array_key_exists($key, $headerMap)) {
                $key = $headerMap[$key];
            }

            $cleanHeaders[$key] = is_array($value) ? implode(', ', $value) : $value;
        }

        return $cleanHeaders;
    }

    /**
     * Convert a PSR-7 uploaded files structure to a $_FILES structure.
     *
     * @param array $uploadedFiles Array of file objects.
     *
     * @return array
     */
    private static function convertUploadedFiles(array $uploadedFiles)
    {
        $files = [];
        foreach ($uploadedFiles as $name => $uploadedFile) {
            if (!is_array($uploadedFile)) {
                $files[$name] = self::convertUploadedFile($uploadedFile);
                continue;
            }

            $files[$name] = [];
            foreach ($uploadedFile as $file) {
                $files[$name][] = self::convertUploadedFile($file);
            }
        }

        return $files;
    }

    private static function convertUploadedFile(UploadedFileInterface $uploadedFile)
    {
        return [
            'name' => $uploadedFile->getClientFilename(),
            'type' => $uploadedFile->getClientMediaType(),
            'size' => $uploadedFile->getSize(),
            'tmp_name' => $uploadedFile->getStream()->getMetadata('uri'),
            'error' => $uploadedFile->getError(),
        ];
    }
}
