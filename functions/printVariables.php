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

#require_once 'iFunction.php';

/**
 * @name myFunction
 * @desc Esempio di funzione che implementa l'interfaccia iFunction
 */
class printVariables {
	private $parameters = array();
	public function addParameter($value){
		$this->parameters = $value; 
	}
	
	public function execute(){
		ob_start();
		print_r(Model::$variables);
		$buffer = ob_get_clean();
		
		return $buffer . '<br />';
	}
}
?>