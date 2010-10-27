<?php
/**
 * @package ALPHA
 * @subpackage Examples
 * @name Function Interface
 * @version 1.0
 * @author Diego La Monica <me@diegolamonica.info>
 * Esempio di funzione custom richiamabile dal template.
 * La sintassi corretta per la sua esecuzione da template �:
 * <code>
 * 		{fn:myFunction "parametro1","parametro2","parametro3"}
 * </code>
 *
 */
/**
 * 
 */
require_once 'iFunction.php';

/**
 * @name myFunction
 * @desc Esempio di funzione che implementa l'interfaccia iFunction
 */
class sql implements iFunction{
	private $parameters = array();
	
	public function addParameter($value){
		$this->parameters[] = $value;
	}
	
	public function execute(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering Method Execute (sql function)', DEBUG_REPORT_OTHER_DATA);
		
		$key = $this->parameters[0];
		$query = $this->parameters[1];
		$rowSeparator = '<br />';
		$fieldSeparator = ' ';
		#print_r($this->parameters);
		if(isset($this->parameters[2])) $rowSeparator = $this->parameters[2]; 
		if(isset($this->parameters[3])) $fieldSeparator = $this->parameters[3]; 
		$query = str_replace('%key%', $key, $query);
		$buffer = _decodeItem($query, $rowSeparator, $fieldSeparator, false, false);
		
		$dbg->write('Ending Method Execute (sql function)', DEBUG_REPORT_OTHER_DATA);
		return $buffer;
	}
}
?>