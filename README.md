# MyLogin #

## Description ##
Abstract Class for Login in Social Networks

## Requirements ##
* [PHP 5.4.1 or higher](http://www.php.net/)

## Developer Documentation ##
Execute phpdoc -d MyLogin/

## Installation ##
Create file composer.json

{
    "require": {
    	"php": ">=5.4.0",
        "yorch/mylogin" : "dev-master",
        "monolog/monolog": "1.13.1",
        "facebook/php-sdk-v4" : "4.0.23",
        "ruudk/twitter-oauth" : "dev-master"
    }
}

Execute composer.phar install

## Example ##
~~~

$social = MyLogin::getInstance('MyFaceBook', 'APP_ID', 'APP_SECRET', 'CALLBACK_URL');

$social->login();
$social->getAuthUrl();

~~~

## Notes ##
This tool uses PHP Facebook SDk and Abraham Twitter OAuth Library.

## References ##
https://developers.facebook.com/
https://dev.twitter.com/

P.D. Let's go play !!!




