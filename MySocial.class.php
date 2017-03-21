<?php
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\Entities\AccessToken;
use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\HttpClients\FacebookHttpable;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * MySocial 
 *
 * MySocial Abstract Class for Implement Social Login
 *
 * Copyright 2015 Jorge Alberto Ponce Turrubiates
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category   MySocial
 * @package    MySocial
 * @copyright  Copyright 2015 Jorge Alberto Ponce Turrubiates
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    1.0.0, 2015-07-17
 * @author     Jorge Alberto Ponce Turrubiates (the.yorch@gmail.com)
 */
abstract class MySocial
{
	/**
     * Facebook Application Id or Twitter Consumer Key 
     *
     * @var string $_appKey App Key
     * @access private
     */
	protected $_appKey = null;

	/**
     * Facebook Application Secret or Twitter Consumer Secret 
     *
     * @var string $_appSecret App Secret
     * @access private
     */
	protected $_appSecret = null;

	/**
     * CallBack URL
     *
     * @var string $_cbUrl CallBack URL
     * @access private
     */
	protected $_cbUrl = null;

	/**
     * Authentication URL
     *
     * @var string $_authUrl Authentication URL
     * @access private
     */
	protected $_authUrl = null;

	/**
     * LOG Object to manage error log
     *
     * @var object $_log Log Object
     * @access private
     */
	protected $_log = null;

	/**
	 * Initialize Log file
	 */
	public function initlog()
	{
		// Create Log
		$logName = 'mylogin_log-' . date("Y-m-d") . '.log';

		$this->_log = new Logger('MyLogin');
		$this->_log->pushHandler(new StreamHandler($logName, Logger::ERROR));
	}

	/**
	 * Validate if Exists FaceBook or Twitter Id in Session
	 * 
	 * @return boolean
	 */
	public function validate()
	{
		return (isset($_SESSION['SOCIAL_TYPE']));
	}

	/**
	 * Gets Authentication URL
	 * @return string
	 */
	public function getAuthUrl()
	{
		return $this->_authUrl;
	}

	/**
	 * Create Session Variables
	 * 
	 * @param  string $type Session Type ('FB' or 'TW')
	 * @param  string $id   Social Id
	 * @param  string $name Social Name
	 * @param  string $last Social Last Name
	 * @param  string $link Social Profile Link
	 * @param  string $profileImg  Profile Image URL
	 * @param  string $email Profile EMail
	 * @param  object $session     Social Session
	 */
	public function createSession($type, $id, $name, $last, $link, $profileImg, $email, $session)
	{
		$_SESSION['SOCIAL_TYPE'] = $type;
		$_SESSION['SOCIAL_ID'] = $id;
		$_SESSION['SOCIAL_NAME'] = $name;
		$_SESSION['SOCIAL_LNAME'] = $last;
		$_SESSION['SOCIAL_LINK'] = $link;
		$_SESSION['SOCIAL_IMG'] = $profileImg;
		$_SESSION['SOCIAL_MAIL'] = $email;
		$_SESSION['SOCIAL_SESSION'] = $session;
	}

	/**
	 * Check Login Social Session Variables
	 * 
	 * @return boolean
	 */
	public abstract function login();
}

/**
 * Facebook Implementation of MySocial
 *
 * @category   MyFaceBook
 * @package    MySocial
 * @copyright  Copyright 2015 Jorge Alberto Ponce Turrubiates
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    1.0.0, 2015-07-17
 * @author     Jorge Alberto Ponce Turrubiates (the.yorch@gmail.com)
 */
class MyFaceBook extends MySocial
{
	/**
	 * Constructor Class
	 * 
	 * @param string $appKey    Facebook Application Id
	 * @param string $appSecret Facebook Application Id
	 * @param string $cbUrl     CallBack URL
	 */
	public function __construct($appKey, $appSecret, $cbUrl)
	{
		// Check Sessions
		if (session_status() == PHP_SESSION_NONE) {
		    session_start();
		}

		// Init Log
		$this->initlog();

		$this->_appKey = $appKey;
		$this->_appSecret = $appSecret;
		$this->_cbUrl = $cbUrl;

		try{
			FacebookSession::setDefaultApplication($this->_appKey, $this->_appSecret);
		}
		catch(Exception $e){
        	$this->_log->addError($e->getMessage());
        }
	}

	/**
	 * Check Login Facebook Session Variables
	 * 
	 * @return boolean
	 */
	public function login()
	{
		$retValue = false;

		try{
			$helper = new FacebookRedirectLoginHelper($this->_cbUrl);

	        try{
	        	$session = $helper->getSessionFromRedirect();
	        }
	        catch(FacebookRequestException $e){
	        	$this->_log->addError($e->getMessage());
	        }
	        catch(Exception $e){
	        	$this->_log->addError($e->getMessage());
	        }

	        if (isset($session)){
	        	$request = new FacebookRequest( $session, 'GET', '/me?fields=id,name,link,email,first_name,last_name' );
	        	$response = $request->execute();
	        	$graphObject = $response->getGraphObject();
	        	
	        	$fbid = $graphObject->getProperty('id');
	        	//$fbname = $graphObject->getProperty('name'); 
	        	$fbname = $graphObject->getProperty('first_name');
	        	$fblast = $graphObject->getProperty('last_name');
	        	$fblink = $graphObject->getProperty('link'); 
	            $fbimg = 'https://graph.facebook.com/' . $fbid . '/picture?type=large';
	            $fbMail  = $graphObject->getProperty('email');

	            // Create Session Variables
	            $this->createSession('FB', $fbid, $fbname, $fblast, $fblink, $fbimg, $fbMail, $session);

	            $retValue = true;
	        } 
	        else{
	        	$this->_authUrl = $helper->getLoginUrl(array('scope' => 'email'));
	        }
		}
		catch(Exception $e){
        	$this->_log->addError($e->getMessage());
        }

		return $retValue;
	}
}

/**
 * Twitter Implementation of MySocial
 *
 * @category   MyTwitter
 * @package    MySocial
 * @copyright  Copyright 2015 Jorge Alberto Ponce Turrubiates
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    1.0.0, 2015-07-18
 * @author     Jorge Alberto Ponce Turrubiates (the.yorch@gmail.com)
 */
class MyTwitter extends MySocial
{
	/**
	 * Constructor Class
	 * 
	 * @param string $appKey    Twitter Consumer Key
	 * @param string $appSecret Twitter Secret Key
	 * @param string $cbUrl     CallBack URL
	 */
	public function __construct($appKey, $appSecret, $cbUrl)
	{
		// Check Sessions
		if (session_status() == PHP_SESSION_NONE) {
		    session_start();
		}

		// Init Log
		$this->initlog();
		
		$this->_appKey = $appKey;
		$this->_appSecret = $appSecret;
		$this->_cbUrl = $cbUrl;
	}

	/**
	 * Check Login Twitter Session Variables
	 * 
	 * @return boolean
	 */
	public function login()
	{
		$retValue = false;

		try{
			if(isset($_REQUEST['oauth_token']) && $_SESSION['token'] == $_REQUEST['oauth_token']){
				$tw = new \TwitterOAuth\Api($this->_appKey, $this->_appSecret, $_SESSION['token'], $_SESSION['token_secret']);
	            $access_token = $tw->getAccessToken($_REQUEST['oauth_verifier']);

	            if($tw->http_code=='200')
	            {
	                $_SESSION['status'] = 'verified';
	                $_SESSION['request_vars'] = $access_token;
	                
	                $twid   = $_SESSION['request_vars']['user_id'];
	                $twname = $_SESSION['request_vars']['screen_name'];
	                $twlast = $_SESSION['request_vars']['screen_name'];
	                $twlink = 'https://twitter.com/intent/user?user_id=' . $twid;
	                $twImg  = 'https://twitter.com/' . $twname . '/profile_image?size=original';
	                $twMail = 'twitter@twitter.com';

	                // Create Session Variables
	            	$this->createSession('TW', $twid, $twname, $twlast, $twlink, $twImg, $twMail, $access_token);

	                unset($_SESSION['token']);
	                unset($_SESSION['token_secret']);
	                
	                $retValue = true;
	            }
	            else{
	            	$this->_log->addError($e->getMessage("Twitter error, try again later!"));
	            }
	        }
	        else{
	            $tw = new \TwitterOAuth\Api($this->_appKey, $this->_appSecret);
	            $request_token = $tw->getRequestToken($this->_cbUrl);

	            $_SESSION['token']        = $request_token['oauth_token'];
	            $_SESSION['token_secret'] = $request_token['oauth_token_secret'];
	            
	            if($tw->http_code=='200'){
	                $this->_authUrl = $tw->getAuthorizeURL($request_token['oauth_token']);
	            }
	            else{
	            	$this->_log->addError($e->getMessage("error connecting to Twitter! try again later!"));
	            }
	        }
		}
		catch(Exception $e){
        	$this->_log->addError($e->getMessage());
        }

		return $retValue;
	}
}

/**
 * Google+ Implementation of MySocial
 *
 * @category   MyGoogle
 * @package    MySocial
 * @copyright  Copyright 2015 Jorge Alberto Ponce Turrubiates
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    1.0.0, 2015-10-21
 * @author     Jorge Alberto Ponce Turrubiates (the.yorch@gmail.com)
 */
class MyGoogle extends MySocial
{
	/**
	 * Constructor Class
	 * 
	 * @param string $appKey    Twitter Consumer Key
	 * @param string $appSecret Twitter Secret Key
	 * @param string $cbUrl     CallBack URL
	 */
	public function __construct($appKey, $appSecret, $cbUrl)
	{
		// Check Sessions
		if (session_status() == PHP_SESSION_NONE) {
		    session_start();
		}

		// Init Log
		$this->initlog();
		
		$this->_appKey = $appKey;
		$this->_appSecret = $appSecret;
		$this->_cbUrl = $cbUrl;
	}

	/**
	 * Check Login Google+ Session Variables
	 * 
	 * @return boolean
	 */
	public function login()
	{
		$retValue = false;

		try{
			$client = new Google_Client();
			$client->setClientId($this->_appKey);
			$client->setClientSecret($this->_appSecret);
			$client->setRedirectUri($this->_cbUrl);

			$client->addScope("email");
			$client->addScope("profile");

			$service = new Google_Service_Oauth2($client);

			if (isset($_GET['code'])) {
				$client->authenticate($_GET['code']);

				// Create access_token
				$_SESSION['access_token'] = $client->getAccessToken();
			}

			if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
				$client->setAccessToken($_SESSION['access_token']);

				// Get Info User
				$user = $service->userinfo->get();

				$gpid   = $user->getId();
                $gpname = $user->getGivenName();
                $gplast = $user->getFamilyName();
                $gplink = $user->getLink();
                $gpImg  = $user->getPicture();
                $gMail  = $user->getEmail();
                $access_token = $_SESSION['access_token'];

                // Create Session Variables
            	$this->createSession('GP', $gpid, $gpname, $gplast, $gplink, $gpImg, $gMail, $access_token);

            	$retValue = true;
			} 
			else {
				$this->_authUrl = $client->createAuthUrl();
			}
		}
		catch(Exception $e){
        	$this->_log->addError($e->getMessage());
        }

		return $retValue;
	}
}
?>