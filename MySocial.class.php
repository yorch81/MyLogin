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
	 * @param  string $link Social Profile Link
	 * @param  string $profileImg Profile Image URL
	 */
	public function createSession($type, $id, $name, $link, $profileImg)
	{
		$_SESSION['SOCIAL_TYPE'] = $type;
		$_SESSION['SOCIAL_ID'] = $id;
		$_SESSION['SOCIAL_NAME'] = $name;
		$_SESSION['SOCIAL_LINK'] = $link;
		$_SESSION['SOCIAL_IMG'] = $profileImg;
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
	        	$request = new FacebookRequest( $session, 'GET', '/me?fields=id,name,link' );
	        	$response = $request->execute();
	        	$graphObject = $response->getGraphObject();
	        	
	        	$fbid = $graphObject->getProperty('id');
	        	$fbname = $graphObject->getProperty('name');
	        	$fblink = $graphObject->getProperty('link'); 
	            $fbimg = 'https://graph.facebook.com/' . $fbid . '/picture?type=large';

	            // Create Session Variables
	            $this->createSession('FB', $fbid, $fbname, $fblink, $fbimg);

	            $retValue = true;
	        } 
	        else{
	        	$this->_authUrl = $helper->getLoginUrl();
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
	                $twlink = 'https://twitter.com/intent/user?user_id=' . $twid;
	                $twImg  = 'https://twitter.com/' . $twname . '/profile_image?size=original';

	                // Create Session Variables
	            	$this->createSession('TW', $twid, $twname, $twlink, $twImg);

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
?>