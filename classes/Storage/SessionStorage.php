<?php
class SessionStorage implements iStorage{
	/*
	 * ChangeLog:
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

	public function destroy($key = ''){
		if($key!=''){
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