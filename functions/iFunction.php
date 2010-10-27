<?php
/**
 * @package ALPHA
 * @name Function Interface
 * @version 1.0
 * @author Diego La Monica <me@diegolamonica.info>
 * @desc tutte le funzioni custom richiamabili da template devono implementare la seguente interfaccia
 *
 */
if(!interface_exists('iFunction')){
	interface iFunction{
		/**
		 * Questa funzione viene richiamata dal model per indicare i valori dei parametri passati alla funzione
		 * @param array $value un array di parametri passati alla funzione
		 * @return void
		 */
		public function addParameter($value);
		/**
		 * Viene richiamato dal model ogni volta che trova un occorrenza della funzione nel template.
		 * @return string Il buffer risultato dall'elaborazione della funzione
		 */
		public function execute();
	}
}
?>