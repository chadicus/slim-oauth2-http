# Chadicus\Slim\OAuth2\Http

[![Build Status](https://travis-ci.org/chadicus/slim-oauth2-http.svg?branch=master)](https://travis-ci.org/chadicus/slim-oauth2-http)
[![Code Quality](https://scrutinizer-ci.com/g/chadicus/slim-oauth2-http/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/chadicus/slim-oauth2-http/?branch=master)
[![Code Coverage](https://coveralls.io/repos/github/chadicus/slim-oauth2-http/badge.svg?branch=master)](https://coveralls.io/github/chadicus/slim-oauth2-http?branch=master)
[![Dependency Status](https://www.versioneye.com/user/projects/55b9070f65376200200012d8/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55b9070f65376200200012d8)

[![Latest Stable Version](https://poser.pugx.org/chadicus/slim-oauth2-http/v/stable)](https://packagist.org/packages/chadicus/slim-oauth2-http)
[![Latest Unstable Version](https://poser.pugx.org/chadicus/slim-oauth2-http/v/unstable)](https://packagist.org/packages/chadicus/slim-oauth2-http)
[![License](https://poser.pugx.org/chadicus/slim-oauth2-http/license)](https://packagist.org/packages/chadicus/slim-oauth2-http)

[![Total Downloads](https://poser.pugx.org/chadicus/slim-oauth2-http/downloads)](https://packagist.org/packages/chadicus/slim-oauth2-http)
[![Daily Downloads](https://poser.pugx.org/chadicus/slim-oauth2-http/d/daily)](https://packagist.org/packages/chadicus/slim-oauth2-http)
[![Monthly Downloads](https://poser.pugx.org/chadicus/slim-oauth2-http/d/monthly)](https://packagist.org/packages/chadicus/slim-oauth2-http)

[![Documentation](https://img.shields.io/badge/reference-phpdoc-blue.svg?style=flat)](http://pholiophp.org/chadicus/slim-oauth2-http)

Library of classes to be used for bridging http requests/responses messages.

## Requirements

Chadicus\Slim\OAuth2\Http requires PHP 5.5 (or later).

##Composer
To add the library as a local, per-project dependency use [Composer](http://getcomposer.org)! Simply add a dependency on
`chadicus/slim-oauth2-http` to your project's `composer.json` file such as:

```json
{
    "require": {
        "chadicus/slim-oauth2-http": "~1.0"
    }
}
```

##Contact
Developers may be contacted at:

 * [Pull Requests](https://github.com/chadicus/slim-oauth2-http/pulls)
 * [Issues](https://github.com/chadicus/slim-oauth2-http/issues)

##Project Build
With a checkout of the code get [Composer](http://getcomposer.org) in your PATH and run:

```sh
./composer install
./vendor/bin/phpunit
```

##Example Usage

###Simple route for creating a new oauth2 access token
```php
use Chadicus\Slim\OAuth2\Http\MessageBridge;
use OAuth2;
use Slim;

$server = new OAuth2\Server();
// configure the OAuth2 Server
//...

$app = new Slim\Slim();
// configure the Slim Application
//...

$app->post('/token', function () use ($app, $server) {
    //create an \OAuth2\Request from the current \Slim\Http\Request Object
    $oauth2Request = MessageBridge::newOAuth2Request($app->request());

    //Allow the oauth2 server instance to handle the oauth2 request
    $oauth2Response = $server->handleTokenRequest($oauth2Request),

    //Map the oauth2 response into the slim response
    MessageBridge::mapResponse($oauth2Response, $app->response());
});

```
