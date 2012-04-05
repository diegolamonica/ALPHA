<?php

class CookieStorage{

	private $expiration = 0;
	private $domain = '';
	private $path = '';
	private $secure = false;	# true = only over https
	private $httponly = true;
	
	function __construct(){
		!defined('STORAGE_COOKIE_EXPIRATION') 	&& define('STORAGE_COOKIE_EXPIRATION', 	'3600');
		!defined('STORAGE_COOKIE_DOMAIN') 		&& define('STORAGE_COOKIE_DOMAIN', 		'');
		!defined('STORAGE_COOKIE_PATH') 		&& define('STORAGE_COOKIE_PATH', 		'');
		!defined('STORAGE_COOKIE_SECURE') 		&& define('STORAGE_COOKIE_SECURE', 		false);
		!defined('STORAGE_COOKIE_HTTPONLY') 	&& define('STORAGE_COOKIE_HTTPONLY', 	true);
		
		$this->expiration 	= time()+STORAGE_COOKIE_EXPIRATION;
		$this->domain 		= STORAGE_COOKIE_DOMAIN;
		$this->path			= STORAGE_COOKIE_PATH;
		$this->secure		= STORAGE_COOKIE_SECURE;
		$this->httponly		= STORAGE_COOKIE_HTTPONLY;
		
	}
	
	function write($key, $value){
		setcookie($key, $value, $this->expiration, $this->path, $this->domain, $this->secure, $this->httponly);
		$_COOKIE[$key] = $value;
	}
	
	function read($key){
		return $_COOKIE[$key];
	}
	
	function destroy($key = ''){
		
		if($key!=''){
			unset($_COOKIE[$key]);
			setcookie($key, NULL, -1);
		}else{
			foreach ($_COOKIE as $key => $value){
				unset($_COOKIE[$key]);
				setcookie($key, NULL, -1); 
			}
		}
	}
}

class SessionStorage{
	function __construct(){
		if(!isset($_SESSION)) session_start();
	}
	
	function write($key, $value ){
		$_SESSION[$key] = $value;
	}
	
	function read($key){
		if(!isset($_SESSION[$key])) return null;
		return $_SESSION[$key];
	
	}
	
	function destroy($key = ''){
		if($key!=''){
			unset($_SESSION[$key]);
		}else{
			unset($_SESSION);
		}
		session_destroy();
		
	}
	function debug(){
	
		foreach($_SESSION as $key => $value){
		
			echo $key . ' =  ' . print_r(unserialize($value), true) . '<br />';
		}
	}
}

class Storage extends Debugger{

	private $handler;
	private $encryptionKey;
	private $saltKey = '';
	
	function __construct(){
	
		parent::__construct();
		
		!defined('STORAGE_ENCRYPTION_KEY')	&& 		define('STORAGE_ENCRYPTION_KEY', '\\1234567890\'ìqwertyuiopè+asdfghjklòàù<zxcvbnm,.-|!"£$%&/()=?^é*ç°§>;:_[]');
		!defined('STORAGE_METHOD')			&&		define('STORAGE_METHOD', SESSION_STORAGE);
		$this->setEncryptionKey(STORAGE_ENCRYPTION_KEY);
		$this->setStorage(STORAGE_METHOD);
	
	}


	function setStorage($handlerClass){
		if(!class_exists($handlerClass)){
			$fileToInclude = "Storage/$handlerClass.php";
			if(file_exists($fileToInclude)){
				require_once($fileToInclude);
			} 
		}
		if(class_exists($handlerClass))	$this->handler = new $handlerClass();
	}

	function setEncryptionKey($key){
	
		$this->encryptionKey = $key;
	
	}

	function write($key, $value){
		$value = serialize($value);
		$this->handler->write($key, $this->encrypt($value));
	}
	
	function read($key){
		$value = ($this->decrypt($this->handler->read($key)));
		if($this->is_serialized($value)){
			$value = unserialize($value);
		}
		return $value;
		#return unserialize($this->decrypt($this->handler->read($key)));
	}
	
	function destroy($key = ''){
		$this->handler->destroy($key);
	}
	
	function debug(){
		$this->handler->debug();
	}
	
	private function encrypt($decrypted) {
		if(function_exists('mcrypt_decrypt')){
			$password 	= $this->encryptionKey;
			$salt		= $this->saltKey;
			// Build a 256-bit $key which is a SHA256 hash of $salt and $password.
			$key = hash('SHA256', $salt . $password, true);
			// Build $iv and $iv_base64.  We use a block size of 128 bits (AES compliant) and CBC mode.  (Note: ECB mode is inadequate as IV is not used.)
			srand(); $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
			if (strlen($iv_base64 = rtrim(base64_encode($iv), '=')) != 22) return false;
			// Encrypt $decrypted and an MD5 of $decrypted using $key.  MD5 is fine to use here because it's just to verify successful decryption.
			$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $decrypted . md5($decrypted), MCRYPT_MODE_CBC, $iv));
			// We're done!
			return $iv_base64 . $encrypted;
		}else{
			return $decrypted;
		}
	 }

	private function decrypt($encrypted) {
		if(function_exists('mcrypt_decrypt')){
			$password 	= $this->encryptionKey;
			$salt		= $this->saltKey;
			// Build a 256-bit $key which is a SHA256 hash of $salt and $password.
			$key = hash('SHA256', $salt . $password, true);
			// Retrieve $iv which is the first 22 characters plus ==, base64_decoded.
			$iv = base64_decode(substr($encrypted, 0, 22) . '==');
			// Remove $iv from $encrypted.
			$encrypted = substr($encrypted, 22);
			// Decrypt the data.  rtrim won't corrupt the data because the last 32 characters are the md5 hash; thus any \0 character has to be padding.
			$decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($encrypted), MCRYPT_MODE_CBC, $iv), "\0\4");
			// Retrieve $hash which is the last 32 characters of $decrypted.
			$hash = substr($decrypted, -32);
			// Remove the last 32 characters from $decrypted.
			$decrypted = substr($decrypted, 0, -32);
			// Integrity check.  If this fails, either the data is corrupted, or the password/salt was incorrect.
			if (md5($decrypted) != $hash) return false;
			// Yay!
			return $decrypted;
		}else{
			return $encrypted;
		}
	 }
	 
	/**
	 * This program is free software. It comes without any warranty, to
	 * the extent permitted by applicable law. You can redistribute it
	 * and/or modify it under the terms of the Do What The Fuck You Want
	 * To Public License, Version 2, as published by Sam Hocevar. See
	 * http://sam.zoy.org/wtfpl/COPYING for more details.
	 */ 
	
	/**
	 * Tests if an input is valid PHP serialized string.
	 *
	 * Checks if a string is serialized using quick string manipulation
	 * to throw out obviously incorrect strings. Unserialize is then run
	 * on the string to perform the final verification.
	 *
	 * Valid serialized forms are the following:
	 * <ul>
	 * <li>boolean: <code>b:1;</code></li>
	 * <li>integer: <code>i:1;</code></li>
	 * <li>double: <code>d:0.2;</code></li>
	 * <li>string: <code>s:4:"test";</code></li>
	 * <li>array: <code>a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}</code></li>
	 * <li>object: <code>O:8:"stdClass":0:{}</code></li>
	 * <li>null: <code>N;</code></li>
	 * </ul>
	 *
	 * @author		Chris Smith <code+php@chris.cs278.org>
	 * @copyright	Copyright (c) 2009 Chris Smith (http://www.cs278.org/)
	 * @license		http://sam.zoy.org/wtfpl/ WTFPL
	 * @param		string	$value	Value to test for serialized form
	 * @param		mixed	$result	Result of unserialize() of the $value
	 * @return		boolean			True if $value is serialized data, otherwise false
	 */
	function is_serialized($value, &$result = null)
	{
		// Bit of a give away this one
		if (!is_string($value))
		{
			return false;
		}
	
		// Serialized false, return true. unserialize() returns false on an
		// invalid string or it could return false if the string is serialized
		// false, eliminate that possibility.
		if ($value === 'b:0;')
		{
			$result = false;
			return true;
		}
	
		$length	= strlen($value);
		$end	= '';
	
		switch ($value[0])
		{
			case 's':
				if ($value[$length - 2] !== '"')
				{
					return false;
				}
			case 'b':
			case 'i':
			case 'd':
				// This looks odd but it is quicker than isset()ing
				$end .= ';';
			case 'a':
			case 'O':
				$end .= '}';
	
				if ($value[1] !== ':')
				{
					return false;
				}
	
				switch ($value[2])
				{
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
					case 5:
					case 6:
					case 7:
					case 8:
					case 9:
					break;
	
					default:
						return false;
				}
			case 'N':
				$end .= ';';
	
				if ($value[$length - 1] !== $end[0])
				{
					return false;
				}
			break;
	
			default:
				return false;
		}
	
		if (($result = @unserialize($value)) === false)
		{
			$result = null;
			return false;
		}
		return true;
	}	 
	 
}
