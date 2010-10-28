<?php
/**
 * @name ALPHA - Authentication Interface
 * @package ALPHA
 * @author Diego La Monica
 * @version 1.0
 * @desc Interfaccia che descrive i metodi e le prorpietà che una classe di autenticazione deve esporre.
 */
interface iAuthentication{
	/**
	 * Restituisce l'ultimo codice di errore riscontrato in fase di autenticazione o verifica di autenticazione
	 * @return integer
	 */
	function getLastError();
	
	/**
	 * Autentica l'utente.
	 * Verifica che le credenziali fornite corrispondano ad un utente censito presso la sorgente dati
	 * @param string $user nome utente
	 * @param string $password password
	 * @return void
	 */
	function login($user, $password);
	/**
	 * Restituisce true se l'utente risulta autenticato nel sistema.
	 * @return bool
	 */
	function isAuthenticated();
	/**
	 * Restituisce il nome dell'utente corrente
	 * @return string
	 */
	function currentUser();
	/**
	 * Forza la rimozione dei dati di autenticazione dell'utente attualmente loggato
	 */
	function logout();
	/**
	 * Forza l'autenticazione dell'utente rimuovendo i dati di accesso e dirottando l'utente
	 * verso la pagina di login.
	 */
	function forceLogin();
	
	/**
	 * Restituisce un array associativo con tutti i dati dell'utente corrente
	 * @return array
	 */
	function getUserData();
}
?>