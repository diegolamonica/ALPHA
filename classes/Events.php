<?php
require_once 'Debug.php';
class Events extends Debugger{

	/**
	 * Registra un metodo da scatenare all'occorrenza di un preciso evento.
	 * @param $event Nome dell'evento
	 * @param $method Nome del metodo da richiamare al verificarsi dell'evento
	 */
	
	private $events = array();
	
	function register($event, $method){
	
		if(!isset($this->events[$event])) $this->events[$event] = array();
		
		if(array_search($method, $this->events[$event],true)===false){
			$this->events[$event][] = $method;
		}
		
	}
	
	/**
	 * Invoca tutti i metodi associati ad un preciso evento
	 * @param $eventName (string) Nome dell'evento scatenato
	 * @param <strong>(mixed) ...</strong> Lista dei parametri da inviare a ciascun metodo invocato dall'evento scatenante   
	 */
	function raise(){
		
		$argList = func_get_args();
		
		
		$eventName = array_shift($argList);
		if(isset($this->events[$eventName])){
			$theEventMethods = $this->events[$eventName];
			$argList[] = $this;
			for($i = 0; $i<count($theEventMethods); $i++){
				call_user_func_array($theEventMethods[$i], $argList);
			}
		}
		
	}

}