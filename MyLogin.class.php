<?php
require_once('MySocial.class.php');

/**
 * MyLogin 
 *
 * MyLogin Class for Manage MySocial Logins
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
 * @category   MyLogin
 * @package    MyLogin
 * @copyright  Copyright 2015 Jorge Alberto Ponce Turrubiates
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    1.0.0, 2015-07-18
 * @author     Jorge Alberto Ponce Turrubiates (the.yorch@gmail.com)
 */
class MyLogin
{
	/**
     * Instance Handler to Singleton Pattern
     *
     * @var object $_instance Instance Handler
     * @access private
     */
	private static $_instance;

	/**
     * MySocial Instance
     *
     * @var object $_social MySocial Instance
     * @access private
     */
	private $_social = null;

	/**
	 * Social Types
	 */
	const FACEBOOK = 'MyFaceBook';
	const TWITTER = 'MyTwitter';

	/**
	 * Initialize Wrapper
	 * 
	 * @param string $type      Social Type
	 * @param string $appKey    Facebook Application Id or Twitter Consumer Key 
	 * @param string $appSecret Facebook Application Secret or Twitter Consumer Secret
	 * @param string $cbUrl     CallBack URL
	 */
	private function __construct($type = self::FACEBOOK, $appKey = '', $appSecret = '', $cbUrl = '')
	{
		if(class_exists($type)){
			$this->_social = new $type($appKey, $appSecret, $cbUrl);
		}
		else{
			die('Social Type ' . $type . ' Not Implemented.');
		}
	}

	/**
	 * Singleton Implementation
	 *
	 * @param string $type      Social Type
	 * @param string $appKey    Facebook Application Id or Twitter Consumer Key 
	 * @param string $appSecret Facebook Application Secret or Twitter Consumer Secret
	 * @param string $cbUrl     CallBack URL
	 * @return MyLogin MyLogin Instance
	 */
	public static function getInstance($type = self::FACEBOOK, $appKey = '', $appSecret = '', $cbUrl = '')
	{
		if(self::$_instance){
			return self::$_instance;
		}
		else{
			$class = __CLASS__;
			self::$_instance = new $class($type, $appKey, $appSecret, $cbUrl);

			return self::$_instance;
		}
	}

	/**
	 * Validate if Exists FaceBook or Twitter Id in Session
	 * 
	 * @return boolean
	 */
	public function validate()
	{
		return $this->_social->validate();
	}

	/**
	 * Check Login Social Network Session Variables
	 * 
	 * @return boolean
	 */
	public function login()
	{
		return $this->_social->login();
	}

	/**
	 * Gets Authentication URL
	 * 
	 * @return string
	 */
	public function getAuthUrl()
	{
		return $this->_social->getAuthUrl();
	}
}
?>