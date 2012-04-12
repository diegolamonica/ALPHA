<?php
/**
 * @package ALPHA
 * @subpackage Functions
 * @name sqlDecode
 * @version 1.0
 * @author Diego La Monica <me@diegolamonica.info>
 * richiamare con la seguente sintassi:  sqlDecode(TABLE, KEYFIELD, KEYVALUE, RETURNVALUE)
 * <code>
 * 		{fn:sqlDecode "ANAGRAFICA","MATRICOLA","12345","COGNOME"}
 * </code>
 *
 */
require_once 'iFunction.php';

class sqlDecode implements iFunction{
	private $parameters = array();
	private $separator = '<br />'; 
	public function addParameter($value){
		$this->parameters[] = $value;
	}
	
	public function execute(){
		$table = $this->parameters[0];
		$keyField = $this->parameters[1];
		$keyValue = $this->parameters[2];
		$returnValue = $this->parameters[3];
		
		$sql = 'select ' . $returnValue .' from ' . $table . ' where ' . $keyField . ' = \''. $keyValue . '\'';
		$c = ClassFactory::get('connector');
		$rs = $c->getFirstRecord($sql);
		if($rs == null) return '';
		$buffer = '';
		foreach($rs as $key => $value){
			if($buffer !='') $buffer .= ' ';
			$buffer .= $value;
		}
		#print_r($rs);
		return $buffer; #$rs[$returnValue];
	}
}
?>