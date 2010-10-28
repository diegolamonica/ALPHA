<?php
interface iDebug{
	
	/**
	 * Scrive un messaggio di errore nel file di log
	 *
	 * @param string $data
	 * @param enum[DEBUG_REPORT_*] $level
	 */
	public function write($data, $level = DEBUG_REPORT_OTHER_DATA );

	/**
	 * Scrive nel file di log i parametri passati alla funzione nell'ordine in cui sono stati passati alla funzione (Senza quindi riportare il nome del parametro)
	 *
	 * @param Array ottenuto da func_get_args() $args
	 */
	public function writeFunctionArguments($args);
	/**
	 * Attiva la modalità maniacale: viene tracciato tutto quello che succede (per il quale è stato previsto output nel file di log) fino alla chiamata del metodo maniacalModeOff() 
	 *
	 */
	public function maniacalModeOn();
	/**
	 * Termina il controllo maniacale tracciando solo le informazioni come da impostazioni della costante DEBUG_REPORT_LEVEL
	 *
	 */
	public function maniacalModeOff();
	/**
	 * #####
	 *
	 */
	public function setGroup($groupName);
	
}

?>