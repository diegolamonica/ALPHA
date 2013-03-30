<?php
class SessionStorage implements iStorage{
	/*
	 * ChangeLog:
	 *
	 * V 1.2
	 * - method destroy() accepts either string and array as parameter or list of parameters
	 * 
	 * V 1.1
	 * - Checking for headers sent on the class constructor method.
	 * - Added some debugging code
	 * - defined methods scope
	 * - now the class implements the iStorage interface.
	 * - Removed from the Storage.php class file
	 * 
	 */

	public function __construct(){
		ClassFactory::get('Debug')->write( __CLASS__ ."::construct");

		if(!isset($_SESSION)){
			if(headers_sent($file, $line)){
				ClassFactory::get('Debug')->write( "Cannot start session, header already sent on $file, $line");
			}else{
				ClassFactory::get('Debug')->write( "Starting Session");
				session_start();
			}
		}else{
			ClassFactory::get('Debug')->write( "Cannot create session, already started");

		}
	}

	public function write($key, $value ){
		$_SESSION[$key] = $value;
	}

	public function read($key){
		if(!isset($_SESSION[$key])) return null;
		return $_SESSION[$key];

	}

	/**
	 * (non-PHPdoc)
	 * @see iStorage::destroy()
	 */
	public function destroy($key = ''){
		
		$arguments = func_get_args();
		/*
		 * If multiple parameters given then I will consider them as cookie keys
		 */
		if(count($arguments)>1) $key = $arguments;
		
		/*
		 * If array of keys given then I need to recursively invoke this method for each key
		 */
		if(is_array($key)){
			foreach($key as $k)
				$this->destroy($k);
		}else if($key!=''){
			unset($_SESSION[$key]);
		}else{
			unset($_SESSION);
		}
		session_destroy();

	}
	
	
	public function debug(){

		foreach($_SESSION as $key => $value){

			echo $key . ' =  ' . print_r(unserialize($value), true) . '<br />';
		}
	}
}
?>