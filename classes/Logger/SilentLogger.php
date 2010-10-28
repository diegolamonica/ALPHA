<?php
/**
 * @name Silent Logger
 * @version 1.0
 * @author Diego La Monica
 * @desc Classe che si preoccupa del logging delle operazioni. Questa classe nello specifico disattiva
 * il logging delle operazioni
 * @package ALPHA
 */
/**
 *
 */
require_once CORE_ROOT. 'classes/interfaces/iLogger.php';
class CustomLogger extends Debugger  implements iLogger {
	
	private $_source = null;
	private $_sourceKey = null;
		
	function getLastEntry($idUser){
	}

	function getEntries($idUser = '', $source = '', $sourceKey = '', $field = '', $limit=''){
	}

	function write($descrizione, $source = '', $sourceKey = '', $field = '', $from_value = '', $to_value = ''){
	}
	
	public function forceSource($source = null, $sourceKey = null){
	}
}
?>