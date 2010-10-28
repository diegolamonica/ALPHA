<?php
/**
 * @name LDAP Authentication
 * @version 1.0
 * @author Diego La Monica
 * @desc Classe che si preoccupa delle operazioni di autenticazione utente
 * @package ALPHA
 */
/**
 *
 */
require_once CORE_ROOT. 'classes/interfaces/iAuthentication.php';

class CustomAuthentication implements iAuthentication{

	private $lastError = '';
	function getLastError(){
		return $this->lastError;
	}
	/**
	 * (non-PHPdoc)
	 * @see classes/interfaces/iAuthentication#login($user, $password)
	 *
	*/
	public function login($user, $password){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		if ($user != "" && $password != "") {
			$l = ClassFactory::get('Ldap');
			
			if(@$l->connect()){
				if($l->authenticateAs($user, $password)){
					
					$_SESSION[SESSION_USER_KEY_VAR] = $user;
					$_SESSION[SESSION_USER_TOKEN_VAR] = $password;
					$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
					return $l->lastEntry;
				}else{
					if($l->findFor('uid', $user)){
						$this->lastError = AUTHENTICATION_ERROR_WRONG_PASSWORD;
					}else{
						$this->lastError = AUTHENTICATION_ERROR_WRONG_USER;
					}
				}
			}else{
				$this->lastError = AUTHENTICATION_ERROR_LDAP_SERVER_UNAVAILABLE;
			}
		}else{
			$this->lastError = AUTHENTICATION_ERROR_WRONG_USER_OR_PASSWORD;
		}
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}

	public function isAuthenticated(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		if(isset($_SESSION[ SESSION_USER_KEY_VAR ]) && isset($_SESSION[ SESSION_USER_TOKEN_VAR ])){
			
			if($this->login($_SESSION[SESSION_USER_KEY_VAR], $_SESSION[SESSION_USER_TOKEN_VAR])){
				
				$result=true;
			}else{
				$result=false;
			}
		}else{
			$result=false;
				
		}
		
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		return $result;

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
		
		$redirectFrom = 'href=' . urlencode($_SERVER['REQUEST_URI']);
		$authPage .= (strpos('?', $authPage)!==false)?'&':'?' . $redirectFrom;
		header( 'Location: ' . $authPage);

		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);

	}

	public function getUserData(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());

		if($this->isAuthenticated()){
			$l = ClassFactory::get('Ldap');
			$result = $l->lastEntry[0];
			$result['id'] = $result['uidnumber'][0];
			$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
				
		}else{
			$result = null;
				
		}
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		return $result;
	}
}

?>