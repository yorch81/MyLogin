# MyLogin #

## Description ##
Abstract Class for Login in Social Networks

## Requirements ##
* [PHP 5.4.1 or higher](http://www.php.net/)

## Developer Documentation ##
Execute phpdoc -d MyLogin/

## Installation ##
Create file composer.json
~~~
{
    "require": {
    	"php": ">=5.4.0",
        "yorch/mylogin" : "dev-master",
        "monolog/monolog": "1.13.1",
        "facebook/php-sdk-v4" : "4.0.23",
        "ruudk/twitter-oauth" : "dev-master",
        "google/apiclient": "1.*"
    }
}
~~~

Execute composer.phar install

## Example ##
~~~

$social = MyLogin::getInstance(MyLogin::FACEBOOK, 'APP_ID', 'APP_SECRET', 'CALLBACK_URL');

if ($social->login()){
	redirect_to(MYPAGE);
}
else
	redirect_to($social->getAuthUrl());

~~~

## Notes ##
The Library creates session variables:

~~~

$_SESSION['SOCIAL_TYPE'] = ('FB', 'TW', 'GP')
$_SESSION['SOCIAL_ID'] = 'SOCIAL_ID'
$_SESSION['SOCIAL_NAME'] = 'SOCIAL_NAME'
$_SESSION['SOCIAL_LINK'] = 'http://SOCIAL_URL/'
$_SESSION['SOCIAL_IMG'] = 'http://SOCIAL_IMG/'
$_SESSION['SOCIAL_MAIL'] = 'SOCIAL@MAIL'
$_SESSION['SOCIAL_SESSION'] = 'SOCIAL_TOKEN';

~~~

This tool uses PHP Sessions and Facebook SDk, Abraham Twitter OAuth Library and Google Api Client.

## References ##
https://developers.facebook.com/
https://dev.twitter.com/
https://console.developers.google.com
https://en.wikipedia.org/wiki/OAuth

P.D. Let's go play !!!




