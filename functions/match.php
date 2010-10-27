<?php

require_once 'iFunction.php';

class match implements iFunction{
	private $parameters = array();
	public function addParameter($value){
		$this->parameters[] = $value;
	}
	
	public function execute(){
		
		$source = $this->parameters[0];
		$destination = $this->parameters[1];
		$responseTrue = $this->parameters[2];
		$responseFalse = isset($this->parameters[3])?$this->parameters[3]:'';
		$result = $responseFalse;
		
		if(!is_array($source)){
			if($source == $destination) $result = $responseTrue;
		}else{
			for($i=0; $i<count($source); $i++){
				if($source[$i] == $destination){
					$result = $responseTrue;
					break;
				}
			}
		}
		
		return $result;
	}
}
?>
