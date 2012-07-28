<?php

require_once  CORE_ROOT. 'classes/Debug/debugger.php';
require_once  CORE_ROOT. 'classes/interfaces/iDebug.php';

if(!class_exists('BaseDebug')){

	class BaseDebug implements iDebug{
		
		private $fileName = ''; 
		private $lastLevel = DEBUG_REPORT_NONE;
		private $filler = '';
		private $maniacalDebug = false;
		
		function __construct(){
			
			$file = DEBUG_FILE_PATH . DEBUG_FILE_NAME;
			$this->fileName = $file;
			$this->write('*** Debug started ', DEBUG_REPORT_CLASS_CONSTRUCTION);
			$this->write('Enabled options:', DEBUG_REPORT_CLASS_CONSTRUCTION);
			
			if((DEBUG_REPORT_LEVEL & DEBUG_REPORT_CLASS_CONSTRUCTION)!=0) $this->write('DEBUG_REPORT_CLASS_CONSTRUCTION');
			if((DEBUG_REPORT_LEVEL & DEBUG_REPORT_CLASS_DESTRUCTION)!=0) $this->write('DEBUG_REPORT_CLASS_DESCTRUCTION');
			if((DEBUG_REPORT_LEVEL & DEBUG_REPORT_CLASS_FUNCTION_INFO)!=0) $this->write('DEBUG_REPORT_CLASS_FUNCTION_INFO');
			if((DEBUG_REPORT_LEVEL & DEBUG_REPORT_CLASS_INFO)!=0) $this->write('DEBUG_REPORT_CLASS_INFO');
			if((DEBUG_REPORT_LEVEL & DEBUG_REPORT_FUNCTION_ENTER)!=0) $this->write('DEBUG_REPORT_FUNCTION_ENTER');
			if((DEBUG_REPORT_LEVEL & DEBUG_REPORT_FUNCTION_PARAMETERS)!=0) $this->write('DEBUG_REPORT_FUNCTION_PARAMETERS');
			if((DEBUG_REPORT_LEVEL & DEBUG_REPORT_FUNCTION_EXIT)!=0) $this->write('DEBUG_REPORT_FUNCTION_EXIT');
			if((DEBUG_REPORT_LEVEL & DEBUG_REPORT_OTHER_DATA)!=0) $this->write('DEBUG_REPORT_OTHER_DATA');
	
		}
		
		function __destruct(){
			$this->write('*** Debug end', DEBUG_REPORT_CLASS_DESTRUCTION);
		}

		public function write($data, $level= DEBUG_REPORT_OTHER_DATA ){
			
			if(($level & DEBUG_REPORT_LEVEL) !== 0 || $this->maniacalDebug){
				$f = fopen($this->fileName, 'a+');
				if($f!==false){
					$filler = str_repeat(' ', $level);
					
					
					fwrite($f, date('Y-m-d H:i:s') . $filler . " $data \n");
					fclose($f);
				}
			}
				
		}
				
		public function writeFunctionArguments($args){
			
			if((DEBUG_REPORT_FUNCTION_PARAMETERS & DEBUG_REPORT_LEVEL) !== 0 || $this->maniacalDebug){
				foreach($args as $key => $value){
					$this->write('argument[' . $key . ']: ' . $this->plainValue($value), DEBUG_REPORT_FUNCTION_PARAMETERS);
				}
			}
		}
	
		public function maniacalModeOn(){
			$this->maniacalDebug = true;
			$this->write('**** Entering Maniacal Mode ****');
		}

		public function maniacalModeOff(){
			$this->write('**** Exiting Maniacal Mode ****');
			$this->maniacalDebug = false;
		}
		
		private function plainValue($value){
			ob_start();
			print_r($value);
			$value = ob_get_clean();
			
			$value = preg_replace('/\\n/i', ' ', $value);
			return $value;
		}
		
		public function setGroup($groupName){
		}
		
		public function skipGroup($groupName){
			
		}
		
		public function restoreGroup($groupName){
			
		}
	}

}

if(!class_exists('CustomDebug')){
	
	class CustomDebug extends BaseDebug{		
	}
	
}
?>