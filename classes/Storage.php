<?php
require_once dirname(__FILE__) . '/interfaces/iStorage.php';

class Storage extends Debugger{
	/*
	 * ChangeLog:
	 * 
	 * V 1.1
	 * - Added VERSION constant.
	 * - Improved the write() and read() methods.
	 * - Improved encryption methods: now they check for the existence of the used methods.
	 * - Optional encryption of the stored data via the definable application constant STORAGE_SECURE_DATA.
	 * - Add public method setSalt()
	 * - (Almost) full documented
	 * - Removed SessionStorage and CookieStorage classes from this file.
	 * - defined methods scope
	 * 
	 */
	
	const	VERSION = 1.1;
	
	/**
	 * the specialized Storage handler.
	 * @var iStorage
	 */
	private $handler;
	
	/**
	 * 
	 * @var string
	 */
	private $encryptionKey;
	
	/**
	 * 
	 * @var string
	 */
	private $saltKey = '';
	
	public function __construct(){
		
		parent::__construct();
		
		!defined('STORAGE_ENCRYPTION_KEY')	&& 		define('STORAGE_ENCRYPTION_KEY', '\\1234567890\'ìqwertyuiopè+asdfghjklòàù<zxcvbnm,.-|!"£$%&/()=?^é*ç°§>;:_[]');
		!defined('STORAGE_METHOD')			&&		define('STORAGE_METHOD', SESSION_STORAGE);
		$this->setEncryptionKey(STORAGE_ENCRYPTION_KEY);
		$this->setStorage(STORAGE_METHOD);
	
	}
	
	/**
	 * Defines the salt key.
	 * @param string $saltKey
	 */
	public function setSalt($saltKey){
		$this->saltKey = $saltKey;
	}
	
	
	/**
	 * Defines which method to use for the storage.
	 * Actually the storage class could be SessionStorage or CookieStorage.
	 * If the method is unable to instantiate the correct class then it will return false else true.
	 * More storage methods can be defined in the (actually not existing) directory Storage.
	 * 
	 * @param string $handlerClass
	 * @return bool
	 */
	public function setStorage($handlerClass){
		/*
		 * If class desired class is not already defined
		 * then I should try to create it.
		 */
		if(!class_exists($handlerClass)){
			/*
			 * Creating a the correct filePath
			 */
			$fileToInclude = dirname(__FILE__) . "/Storage/$handlerClass.php";
			
			/*
			 * Then i will include it
			 */
			if(file_exists($fileToInclude)) require_once($fileToInclude);
		}
		
		if(class_exists($handlerClass)){
			$this->handler = new $handlerClass();
			return true;
		}
		return false;
	}

	/**
	 * Set the salt key to be used during encryp / decrypt methods
	 * @param string $key the salt key
	 */
	public function setEncryptionKey($key){
	
		$this->encryptionKey = $key;
	
	}
	
	/**
	 * Write the given value in the storage system.
	 * @param string $key
	 * @param any $value
	 */
	public function write($key, $value){
		$value = serialize($value);
		/*
		 * I will store in the Storage object the encrypted serialized value
		 */
		$this->handler->write(
				$key, 
				$this->encrypt($value)
			);
		
	}
	
	/**
	 * Read the value from the defined storage system.
	 * @param string $key
	 */
	public function read($key){
		/*
		 * Invoking the handler storage class method read() 
		 */
		$value = $this->handler->read($key);
		if(!is_null($value) && $value!=''){
			/*
			 * If the received value is not null and not is empty
			 * i will try to decode it and to deserialize it. 
			 */
			$value = ($this->decrypt($value));
			if($this->is_serialized($value, $retVal)) $value = $retVal;
		}
		return $value;
	}
	
	/**
	 * Call the current storage destroy() method.
	 * @param string $key (optional) the key to remove from the storage or empty string to remove all keys from storage.
	 */
	public function destroy($key = ''){
		$this->handler->destroy($key);
	}
	/**
	 * Call the debug() method of the iStorage handler
	 */
	public function debug(){
		$this->handler->debug();
	}
	
	/**
	 * encode the string using the mcrypt_encrypt method and return the encrypted string.
	 * If STORAGE_SECURE_DATA is set to 'true' then the return value is equal to the input value.
	 * 
	 * @param string $decrypted
	 * @return string
	 * 
	 * @uses STORAGE_SECURE_DATA
	 */
	private function encrypt($decrypted) {
		
		if(function_exists('mcrypt_decrypt')&& (!defined('STORAGE_SECURE_DATA') || defined('STORAGE_SECURE_DATA') && STORAGE_SECURE_DATA=='true')){
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
	/**
	 * decode the encoded string using the mcrypt_decrypt method.
	 * 
	 * @param string $encrypted
	 * @return boolean|string
	 */
	private function decrypt($encrypted) {
		
		if(function_exists('mcrypt_decrypt') && (!defined('STORAGE_SECURE_DATA') || defined('STORAGE_SECURE_DATA') && STORAGE_SECURE_DATA=='true') ){
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
	private function is_serialized($value, &$result = null){
		// Bit of a give away this one
		if (!is_string($value)) return false;
	
		// Serialized false, return true. unserialize() returns false on an
		// invalid string or it could return false if the string is serialized
		// false, eliminate that possibility.
		if ($value === 'b:0;'){
			$result = false;
			return true;
		}
	
		$length	= strlen($value);
		$end	= '';
	
		switch ($value[0]){
			case 's':
				if ($value[$length - 2] !== '"'){
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
	
				if ($value[1] !== ':') return false;
				
				# This is more readable...
				if(!preg_match('#^\d$#', $value[2])) return false;
				
				# ...than that:
				/*
				switch ($value[2]){
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
				*/
				
				break;
			case 'N':
				$end .= ';';
				if ($value[$length - 1] !== $end[0]) return false;
				break;
			default:
				return false;
		}
		/*
		 * odd behavior why $value sometimes is not a string so I need to use print_r
		 * I have to investigate further and more on this point. 
		 */
		$result = @unserialize(print_r($value,true));
		if ($result === false){
			$result = null;
			return false;
		}
		return true;
	}	 
	 
}
