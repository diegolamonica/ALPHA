<?php
/**
 * @name My SQL Authentication
 * @author Diego La Monica
 * @desc Takes care to authenticate user against mysql table
 * @package ALPHA 
 */
/**
 * 
 */
require_once CORE_ROOT. 'classes/interfaces/iAuthentication.php';
/**
 * Authenticatione Class based on mySQL.
 * @author Diego La Monica
 * @version 1.0
 *
 */
class CustomAuthentication implements iAuthentication {
	/**
	 * Identifica l'ultimo errore che si è presentato nel contesto di autenticazione dell'utente
	 * @var integer
	 */
	private $lastError = AUTHENTICATION_ERROR_OK;
	private $_isAuthenticated = false;
	/**
	 * Restituisce l'ultimo codice di errore scatenato in fase di autenticazione 
	 * @return integer
	 */
	function getLastError(){
		return $this->lastError;
	}
	
	/**
	 * Costruttore della classe authentication
	 */
	function Authentication(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Class Authentication generated' , DEBUG_REPORT_CLASS_CONSTRUCTION);
	
		$c = ClassFactory::get('connector');
	
	}

	/**
	 * @ignore
	 */
	function __destruct(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Class ' . __CLASS__ . ' destructed' , DEBUG_REPORT_CLASS_DESTRUCTION);
	}

	/**
	 * Interroga la tabella del database verificando se le credenziali fornite sono corrette
	 * e immagazina in due variabili di sessione il {@link SESSION_USER_TOKEN_VAR Token} e l'{@link SESSION_USER_KEY_VAR ID} per l'utente
	 * 
	 * @see classes/interfaces/iAuthentication#login($user, $password)
	 */
	function login($user, $password){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		$c = ClassFactory::get('connector');
		$q = $this->getQuerySQL($user, $password);
		$c->query($q);

		if($c->getCount()==1){
			$dbg->write('Given credential (UserName and Password) are valid', DEBUG_REPORT_OTHER_DATA);
			
			$rs = $c->moveNext();
			$storage = ClassFactory::get('Storage');
			$storage->write(SESSION_USER_TOKEN_VAR, $rs[AUTHENTICATION_FIELD_TOKEN]);
			$storage->write(SESSION_USER_KEY_VAR, $rs[AUTHENTICATION_FIELD_TO_STORE]);
			
			#$_SESSION[SESSION_USER_TOKEN_VAR] 	= $rs[AUTHENTICATION_FIELD_TOKEN];
			#$_SESSION[SESSION_USER_KEY_VAR] 	= $rs[AUTHENTICATION_FIELD_TO_STORE];
			
			
		}else{
			$dbg->write('Invalid UserName or Password', DEBUG_REPORT_OTHER_DATA);
			$this->lastError = AUTHENTICATION_ERROR_WRONG_USER_OR_PASSWORD;
		}
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}
	/**
	 * Fornendo il token e la chiave dell'utente mantenute in due variabili di sessione,
	 * interroga il database per verificare che le credenziali fornite siano corrette.
	 * @see classes/interfaces/iAuthentication#isAuthenticated()
	 */
	function isAuthenticated(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		if($this->_isAuthenticated!==false) return $this->_isAuthenticated;
		$rs = null;
		#if(isset($_SESSION[SESSION_USER_TOKEN_VAR]) && isset($_SESSION[SESSION_USER_KEY_VAR])){
		#	$token = $_SESSION[SESSION_USER_TOKEN_VAR];
		#	$key = $_SESSION[SESSION_USER_KEY_VAR];
		
		$storage = ClassFactory::get('Storage');
		$token = $storage->read(SESSION_USER_TOKEN_VAR);
		$key = $storage->read(SESSION_USER_KEY_VAR);
		
		if(!is_null($token) && !is_null($key)){
			#$token = $_SESSION[SESSION_USER_TOKEN_VAR];
			#$key = $_SESSION[SESSION_USER_KEY_VAR];
			$q = $this->getQuerySQL('','', $token, $key);
			
			$c = ClassFactory::get('connector');
			
			$rs = $c->getFirstRecord($q);
			if($rs==null){
				$this->lastError = AUTHENTICATION_ERROR_INVALID_KEY + AUTHENTICATION_ERROR_INVALID_TOKEN;
				$this->_isAuthenticated = false;
			}else{
				
				$this->_isAuthenticated = $rs;
			}
		}else{
			#echo('dati non impostati???');	
		}
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		return ($rs != null);
	}
	/**
	 * Restituisce il valore del campo identificato da {@link AUTHENTICATION_FIELD_USERNAME} 
	 * @see classes/interfaces/iAuthentication#currentUser()
	 */
	function currentUser(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		$rs = $this->getUserData();
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		if($rs!=null){
			return $rs[AUTHENTICATION_FIELD_USERNAME];
		}else{
			return '';
		}
	}
	/**
	 * resetta le variabili di sessione relative alle informazioni utente.
	 * @see classes/interfaces/iAuthentication#logout()
	 */
	function logout(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		if($this->isAuthenticated()){
			$storage = ClassFactory::get('Storage');
			
			$storage->destroy(SESSION_USER_KEY_VAR);
			$storage->destroy(SESSION_USER_TOKEN_VAR);
			
			#$_SESSION[SESSION_USER_KEY_VAR] = '';
			#$_SESSION[SESSION_USER_TOKEN_VAR] = '';
			#unset($_SESSION[SESSION_USER_KEY_VAR]);
			#unset($_SESSION[SESSION_USER_TOKEN_VAR]);
		}
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}
	/**
	 * Restituisce il record relativo all'utente loggato.
	 * @see classes/interfaces/iAuthentication#getUserData()
	 */
	function getUserData(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		$rs = null;
		
		if(defined('MYSQL_ROLE_MODULE') && MYSQL_ROLE_MODULE=='yes'){
			
			$storage = ClassFactory::get('Storage');
			$userData = $storage->read(SESSION_USER_KEY_VAR .'_userdata');
			if(!is_null($userData)) return $userData;
			#if(isset($_SESSION[SESSION_USER_KEY_VAR .'_userdata'])){
				
			#	return unserialize($_SESSION[SESSION_USER_KEY_VAR .'_userdata']);
				
			#}
		}
		$rs = $this->isAuthenticated();
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		return $rs;
	}
	/**
	 * Forza la disconnessione dell'utente e la riautenticazione dirottando il flusso
	 * sulla {@link AUTHENTICATION_LOGIN_PAGE pagina di autenticazione} 
	 * @see classes/interfaces/iAuthentication#forceLogin()
	 */
	function forceLogin(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		$this->logout();
		$authPage = AUTHENTICATION_LOGIN_PAGE;
		$redirectFrom = 'href=' . urlencode($_SERVER['REQUEST_URI']);
		$authPage .= (strpos('?', $authPage)!==false)?'&':'?' . $redirectFrom;
		
		header( 'Location: ' . $authPage);
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		exit();
	}
	
	/*
	 * Private Functions
	 */
	
	/**
	 * Genera la query SQL necessaria all'identificazione dell'utente corrispondente ai parametri specificati
	 * @param string $user il nome dell'utente
	 * @param string $password la password dell'utente loggato
	 * @param string $token il token generato per l'utente loggato
	 * @param string $key l'ID dell'utente loggato
	 * @return string la query SQL generata
	 */
	private function getQuerySQL($user = '', $password = '', $token = '', $key =''){
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup('Entering '. __FUNCTION__);
		$dbg = ClassFactory::get('Debug');
		$dbg->writeFunctionArguments(func_get_args());
		 $q = 'select *';
		 $q .=',sha1( concat(`' . AUTHENTICATION_FIELD_USERNAME .'`,`'. AUTHENTICATION_FIELD_PASSWORD . '`)) as `'.AUTHENTICATION_FIELD_TOKEN. '`';
		 $q .=' from `' . AUTHENTICATION_DATABASE_TABLE . '` where ';

		 if($token!='' && $key!=''){
		 	$q .='sha1(concat(`' . AUTHENTICATION_FIELD_USERNAME .'`,`'. AUTHENTICATION_FIELD_PASSWORD . '`)) = "' . $token .'" and `' . AUTHENTICATION_FIELD_TO_STORE .'`= "' . $key . '"';
		 }else if($user!='' && $password!=''){
		 	$q.='`' . AUTHENTICATION_FIELD_USERNAME . '` = "' . $user . '" and `' . AUTHENTICATION_FIELD_PASSWORD . '` ="' . $this->encodePassword($password). '"';
	 	}else if($key!=''){
	 		$q.='`' . AUTHENTICATION_FIELD_TO_STORE .'`= "' . $key . '"';
	 	}else{
	 		$q.='true=false';
	 		
	 	}
	 	
	 	$dbg->write('Authentication Query is: ' . $q);
	 	$dbg->setGroup('');
	 	
	 	return $q;
	}
	/**
	 * Si preoccupa di codificare la password secondo le pecifiche indicate nella costante {@link AUTHENTICATION_PASSWORD_ENCODING} 
	 * @param string $password la password in chiaro
	 * @return string la password codificata secondo quanto indicato nel parametro di configurazioen {@link AUTHENTICATION_PASSWORD_ENCODING}
	 */
	private function encodePassword($password){
		switch(AUTHENTICATION_PASSWORD_ENCODING){
			case AUTHENTICATION_PASSWORD_ENCODING_MD5:
				return md5($password);
				break;
			case AUTHENTICATION_PASSWORD_ENCODING_PLAIN:
				return $password;
				break;
 			case AUTHENTICATION_PASSWORD_ENCODING_SHA:
				return sha1($password);
				break;
		}
	}
}



?>