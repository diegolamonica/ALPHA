<?php
/**
 * @name Cross Site Authentication
 * @version 1.0
 * @author Diego La Monica
 * @desc Classe che si preoccupa delle operazioni di autenticazione utente
 * @package ALPHA
 */
/**
 *
 */
require_once CORE_ROOT. '/classes/interfaces/iAuthentication.php';

class CustomAuthentication implements iAuthentication{

	private $lastError = '';
	private $currentUserData = null;
	function getLastError(){
		return $this->lastError;
	}

	/**
	 * (non-PHPdoc)
	 * @see classes/interfaces/iAuthentication#login($user, $password)
	 */
	public function login($user, $password){
		/*
		 * Si aspetta la matricola nella $user e il token in $password
		 * altrimenti va in errore
		 */
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		$return = false;
		if ($user != "") {
			$c = ClassFactory::get('connector');
			$sql = "select * from " . AUTHENTICATION_DATABASE_TABLE . " where matricola='$user' and token='$password'";
			$rs = $c->getFirstRecord($sql);
			if($rs!=null){
				$this->currentUserData = $rs;
				$_SESSION[SESSION_USER_KEY_VAR ] = $user;
				$_SESSION[SESSION_USER_TOKEN_VAR] = serialize($rs);
				$return = true;
			}else{
				$this->lastError = AUTHENTICATION_ERROR_XS_NO_RECORD_FOUND;
			}
		}else{
			$this->lastError = AUTHENTICATION_ERROR_WRONG_USER_OR_PASSWORD;
		}
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	  	return $return;
	}

	public function isAuthenticated(){
		
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		$result=false;

		if(isset($_GET['token'])){
			$c = ClassFactory::get('connector');
			$token = $_GET['token'];
			$sql = "select * from ".AUTHENTICATION_DATABASE_TABLE." where TOKEN='$token'";
			$rs = $c->getFirstRecord($sql);
			if($rs!=null){
				
				$_SESSION[SESSION_USER_KEY_VAR] = trim($rs['MATRICOLA']);
				$_SESSION[SESSION_USER_TOKEN_VAR] = $rs['DATI'];
				$allData = unserialize($rs['DATI']);
				$_SESSION['multi_apps_logon'][$allData['applicationID']] =  $rs['DATI'];
				$c->query('delete from '.AUTHENTICATION_DATABASE_TABLE.' where id=' . $rs['ID'], true);
				
				
				$url = $_SERVER['REDIRECT_URL'];
				$get = '';
				foreach($_GET as $key => $value){
					if(substr($key,0,2)!='__' && $key!='token'){
						if($get!='') $get.='&';
						$get .= rawurlencode($key) . '=' . rawurldecode($value);
					}
				} 
				$url = $url . '?' . $get;
				
				header('Location: ' . $url);
				exit();
			}
			
		}
	
		if(isset($_SESSION[ SESSION_USER_KEY_VAR ]) && isset($_SESSION[ SESSION_USER_TOKEN_VAR ])){
			$rs = unserialize($_SESSION[ SESSION_USER_TOKEN_VAR ]);
			if(isset($rs['userData']) && isset($rs['userData'][AUTHENTICATION_FIELD_TOKEN]) && $rs['userData'][AUTHENTICATION_FIELD_TOKEN]==$_SESSION[ SESSION_USER_KEY_VAR ]){
				$id = $this->getApplicationId();
				if(isset($_SESSION['multi_apps_logon'][$id])){
					$_SESSION[ SESSION_USER_TOKEN_VAR ] = $_SESSION['multi_apps_logon'][$id];
					$result=true;
					#$result = $_SESSION[ SESSION_USER_TOKEN_VAR ]; 
				} else{
					unset($_SESSION[SESSION_USER_KEY_VAR]);
					unset($_SESSION[SESSION_USER_TOKEN_VAR]);
				}
				
			}else{
				echo('quindi non sono qui!');
				unset($_SESSION[SESSION_USER_KEY_VAR]);
				unset($_SESSION[SESSION_USER_TOKEN_VAR]);
			}
			#exit();
			
		}
		
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		return $result;

	}
	private function getApplicationId(){
		
		$c = ClassFactory::get('connector');
		
		$r = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		if($_SERVER['SERVER_PROTOCOL']=='HTTP/1.1') $r = 'http://' . $r;
		$sql = "select ID from ".APPLICATIONS_DATABASE_TABLE." where UPPER(url)=UPPER(SUBSTR('$r',1,LENGTH(url)))";
		$rs = $c->getFirstRecord($sql);
		return $rs['ID'];	
	}
	public function currentUser(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());

		$result = $this->getUserData();
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		if($result!=null) return $result[0];
	}

	public function logout(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());

		unset($_SESSION[SESSION_USER_TOKEN_VAR ]);
		unset($_SESSION[SESSION_USER_KEY_VAR]);
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}

	public function forceLogin(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		
		$this->logout();

		$authPage = AUTHENTICATION_LOGIN_PAGE;
		$fromHost = (isset($_SERVER['HTTP_X_FORWARDED_HOST'])?$_SERVER['HTTP_X_FORWARDED_HOST']:$_SERVER['HTTP_HOST']);
		$redirectFrom = 'href=' . urlencode(($_SERVER['SERVER_PROTOCOL']=='HTTP/1.1'?'http://':'https://').$fromHost . $_SERVER['REQUEST_URI']);
		$authPage .= (strpos('?', $authPage)!==false)?'&':'?' . $redirectFrom;
		
		header( 'Location: ' . $authPage);
		
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		exit();

	}

	public function getUserData(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());

		if($this->isAuthenticated()){
			$result = unserialize($_SESSION[ SESSION_USER_TOKEN_VAR ]);
		}else{
			$result = null;
				
		}
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		return $result;
	}
}
?>
