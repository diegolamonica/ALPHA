<?php
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