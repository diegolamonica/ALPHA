<?php
class CookieStorage implements iStorage{
	/*
	 * ChangeLog:
	 * 
	 * V 1.1
	 * - Introduced optional parameter $expiration in write() method
	 * - Check for PHP version for changes in the PHP setcookie() method
	 * - Immediate creation of key in the $_COOKIE array to grant existence during the current page execution
	 * - Immediate removal of key from the $_COOKIE array when using the destroy() method.
	 * - Checking for the magic quotes option to unescape values from cookie.
	 * - Removed from the Storage.php class file
	 * - defined methods scope
	 * - now the class implements the iStorage interface.
	 * - (Almost) full documented
	 */

	private $expiration = 0;
	private $domain = '';
	private $path = '';
	private $secure = false;	# true = only over https
	private $httponly = true;
	/**
	 * Creates a new instance for the CookieStorage class and defines the undefined constants.
	 * @uses STORAGE_COOKIE_EXPIRATION
	 * @uses STORAGE_COOKIE_DOMAIN
	 * @uses STORAGE_COOKIE_PATH
	 * @uses STORAGE_COOKIE_SECURE
	 * @uses STORAGE_COOKIE_HTTPONLY
	 *
	 * @return CookieStorage
	 */
	public function __construct(){
		!defined('STORAGE_COOKIE_EXPIRATION') 	&& define('STORAGE_COOKIE_EXPIRATION', 	'3600');
		!defined('STORAGE_COOKIE_DOMAIN') 		&& define('STORAGE_COOKIE_DOMAIN', 		'');
		!defined('STORAGE_COOKIE_PATH') 		&& define('STORAGE_COOKIE_PATH', 		'');
		!defined('STORAGE_COOKIE_SECURE') 		&& define('STORAGE_COOKIE_SECURE', 		false);
		!defined('STORAGE_COOKIE_HTTPONLY') 	&& define('STORAGE_COOKIE_HTTPONLY', 	true);

		$this->expiration 	= time()+STORAGE_COOKIE_EXPIRATION;
		$this->domain 		= STORAGE_COOKIE_DOMAIN;
		$this->path			= STORAGE_COOKIE_PATH;
		$this->secure		= STORAGE_COOKIE_SECURE;
		/*
		 * This will be used only if PHP>5.2 because the previous setcookie() method does not support it!
		*/
		$this->httponly		= STORAGE_COOKIE_HTTPONLY;

	}

	/**
	 * Store data in a cookie.
	 * Instead of setcookie() method that causes the relative key to to be available in the $_COOKIE system array
	 * only after the page reload, with this method the $_COOKIE key will be imediatelly set and available in the
	 * current execution context.
	 *
	 * @param string $key the key of the cookie.
	 * @param string $value the value to store in the given key of the cookie.
	 * @param int|null $expiration (optional) the expiration time of the cookie.
	 */
	public function write($key, $value, $expiration = null){
		/*
		 * If expiration is not given as parameter I will use the default defined value.
		*/
		$expiration = is_null($expiration)?$this->expiration:$expiration;
		if(version_compare(PHP_VERSION, '5.2.0' )>=0){
			/*
			 *
			* PHP 5.2.0 and greater supports the last boolean parameter $httpOnly
			* @see http://en.php.net/manual/en/functions.setcookie.php
			*
			*/
			setcookie($key, $value, $expiration, $this->path, $this->domain, $this->secure, $this->httponly);
				
		}else{
			setcookie($key, $value, $expiration, $this->path, $this->domain, $this->secure);
				
		}
		/*
		 * Immediate key setting in the $_COOKIE array.
		*/
		$_COOKIE[$key] = $value;
	}

	public function read($key){
		$value = (isset($_COOKIE) && isset($_COOKIE[$key]))?$_COOKIE[$key]:'';

		// If MAGIC QUOTES were enabled returns true.
		// in PHP 5.4 it will return always false, because MAGIC QUOTES DIRECTIVE has been removed.
		$value = get_magic_quotes_gpc()?stripslashes($value):$value;

		return $value;
	}
	/**
	 * Will remove a specific key from stored cookie.
	 * If the key parameter is not defined or is an empty string all stored cookies will be remoed.
	 * @param string $key (optional) the key to remove.
	 */
	public function destroy($key = ''){

		if($key!=''){
			/*
			 * unsetting the cookie telling that it is expired.
			*/
			$this->write($key, NULL, -1);
			unset($_COOKIE[$key]);
		}else{
			foreach ($_COOKIE as $key => $value){
				/*
				 * Recursive call to destroy the all the stored cookies.
				*/
				$this->destroy($key);
			}
		}
	}
	
	public function debug(){
		
	}
}
?>