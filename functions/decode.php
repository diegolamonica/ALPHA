<?php
/*

Function name: decode ( arrayOfPairs, match)
Description: look for the match value into the first key of the arrayOfPairs. 
             arrayOfPairs is an array of items which one is an array of two items the code and the relative decoding value.
Path: /functions/decode.php


Example of use:

	--- PHP SCRIPT ---
	...
	$m->setVar('arrayOfPairs', 
		array(
		 	array(1,'orange'),
		 	array(2,'pear'),
		 	array(3,'apple')
		)
	);
	$m->setVar('match', 2);
	...

	--- TEMPLATE FILE ----
	{fn:decode ( arrayOfPairs, match)}

	--- OUTPUT ---
	pear

*/

require_once 'iFunction.php';
class decode implements ifunction{
	private $parameters = array();
	
	public function addParameter($value){
		$this->parameters[] = $value;
	}
	
	
	public function execute(){
		$iterable = $this->parameters[0];
		$value = $this->parameters[1];
		
		for($i=0; $i<count($iterable); $i++){
			$item = array_values($iterable[$i]);
			
			if($item[0]== $value){
				return $item[1];
			}
		}
		
		
	}
	
}
?>
