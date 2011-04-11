<?php
/**
 * @name Ldap
 * @version 1.0
 * @package ALPHA
 * @author Diego La Monica
 */


/**
 * @desc Classe che si occupa delle operazioni su LDAP
 * @author Diego La Monica
 *
 */
class Ldap  extends Debugger{
	
	private $directoryService = null;
	public $lastEntry=null;
	
	function __destruct(){
		$this->disconnect();
	}
	/**
	 * Esegue la connessione a LDAP. Restituisce true se la connessione ha successo.
	 * @return bool
	 */
	public function connect(){
<<<<<<< HEAD
		/*
		 * @todo identificare il significato dei valori che può assumere l'opzione LDAP_OPT_DEBUG_LEVEL. 
		 */
		ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
=======
		
		ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, LDAPT_DEBUG_LEVEL);
		#ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
>>>>>>> 3d8cea3224676cc824085d77b0a666dd343073ac
		$ds= ldap_connect(LDAP_HOST);
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		$anon = @ldap_bind( $ds );
		// try anonymous login to test connection
		if($anon){
			$this->directoryService = $ds; 
			return true;
		}
		return false;
		
	}
	
	/**
	 * Si disconnette da Ldap. 
	 * La chiamata di questo metodo al termine delle operazioni è facoltativa in quanto
	 * la connessione a Ldap viene chiusa in automatico alla deallocazione dell'oggetto.
	 * Restituisce true se l'oggetto LDAP era connesso ed è avvenuta la disconnessione.
	 * @return bool
	 */
	public function disconnect(){
		if($this->directoryService!=null){
			ldap_close($this->directoryService);
			$this->directoryService = null;
			return true;
		}
		return false;
	}
	/**
	 * Cerca per un entry in Ldap specificando un criterio di ricerca semplice (accoppiata chiave = valore)
	 * $value può contenere dei caratteri speciali ( * ) per una ricerca in like.
	 * L'utente si dovrà preoccupare di fare il corretto escape dei caratteri speciali prima di passare la
	 * stringa alla funzione in quanto essa non si preoccupa di svolgere tale compito.
	 * Se viene ritrovato almeno un record, il metodo restituisce true, altrimenti restituisce false. 
	 * @param $field string
	 * @param $value string
	 * @return bool
	 */
	public function findFor($field, $value){
		
		$this->lastEntry = null;
		if($this->directoryService==null) $this->connect();
		$ds = $this->directoryService;
		if($ds!=null){
			$r = ldap_search( $ds, LDAP_BASEDN, $field.'=' . $value);
			$info = ldap_get_entries($ds, $r);
			$this->lastEntry = $info; 
			return ($info['count']>0);
		}
		
		return false;
	}

	
	/**
	 * Utile per svolgere le interrogazioni Ldap come uno specifico utente.
	 * Questo torna utile in un contesto dove gli utenti generici hanno delle
	 * restrizioni d'accesso.
	 * Il metodo restituisce true se l'utente si è correttamente autenticato,
	 * altrimenti restituisce false in tutti gli altri casi.
	 * @param $user string
	 * @param $password string
	 * @return bool
	 */
	public function authenticateAs($user, $password){
		if($this->findFor('uid', $user)){
			$ds=$this->directoryService;
			if($this->lastEntry['count']>0){
				try {
					$result = @ldap_bind( $ds, $this->lastEntry[0]['dn'], $password);
				} catch (Exception $e) {
				} 
					
				if ($result ) {
					return true;
				} else {
					return false;
				}
			}else{
				return false;
			}
		}
		return false;
	}
	
	public function addEntry($DistinguishedName, $dataEntry){
		/*
		 * @todo: da verificare.
		 */
		if($this->directoryService==null) $this->connect();
		$ds = $this->directoryService;
		$r = false;
		if($ds!=null){
		    // add data to directory
		    $r = ldap_add($ds, $DistinguishedName, $dataEntry);
		}
		return $r;
	}
	
	public function changePassword($DistinguishedName, $newPassword){
		if($this->directoryService==null) $this->connect();
		$ds = $this->directoryService;
		$r = false;
		if($ds!=null){
			$r = ldap_mod_replace($ds, $DistinguishedName, array('userpassword' => "{MD5}".base64_encode(pack("H*",md5($newPassword)))));
		}
		return $r;		
		
	}
}
?>