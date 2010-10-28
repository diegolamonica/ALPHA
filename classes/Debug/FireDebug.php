<?php

require_once  CORE_ROOT. 'classes/Debug/debugger.php';
require_once  CORE_ROOT. 'classes/interfaces/iDebug.php';
require_once CORE_ROOT.'includes/FirePHPCore/FirePHP.class.php';
if(!class_exists('FireDebug')){
	class FireDebug implements iDebug{
		private $fb = null;
		private $groupName = '';
		private $nextGroupToWrite = '';
		private $disallowedGroups = array();
		function __construct(){
			ini_set('max_execution_time',0);
			if($this->fb==null){
				$f = new FirePHP();
				$this->fb  = $f;
			}
			$this->write('*** Debug started ', DEBUG_REPORT_CLASS_CONSTRUCTION, FirePHP_INFO);
			
			$table = array();
			$table[] = array('Option','Status');
			$table[] = array(
				'DEBUG_REPORT_CLASS_CONSTRUCTION',
				(((DEBUG_REPORT_LEVEL & DEBUG_REPORT_CLASS_CONSTRUCTION)!=0)?'On':'Off')
			);
			$table[] = array(
				'DEBUG_REPORT_CLASS_DESCTRUCTION',
				(((DEBUG_REPORT_LEVEL & DEBUG_REPORT_CLASS_DESCTRUCTION)!=0)?'On':'Off')
			);
			$table[] = array(
				'DEBUG_REPORT_CLASS_FUNCTION_INFO',
				(((DEBUG_REPORT_LEVEL & DEBUG_REPORT_CLASS_FUNCTION_INFO)!=0)?'On':'Off')
			);
			$table[] = array(
				'DEBUG_REPORT_CLASS_INFO',
				(((DEBUG_REPORT_LEVEL & DEBUG_REPORT_CLASS_INFO)==DEBUG_REPORT_CLASS_INFO)?'On':'Off')
			);
			$table[] = array(
				'DEBUG_REPORT_FUNCTION_ENTER',
				(((DEBUG_REPORT_LEVEL & DEBUG_REPORT_FUNCTION_ENTER)!=0)?'On':'Off')
			);
			$table[] = array(
				'DEBUG_REPORT_FUNCTION_PARAMETERS',
				(((DEBUG_REPORT_LEVEL & DEBUG_REPORT_FUNCTION_PARAMETERS)!=0)?'On':'Off')
			);
			$table[] = array(
				'DEBUG_REPORT_FUNCTION_EXIT',
				(((DEBUG_REPORT_LEVEL & DEBUG_REPORT_FUNCTION_EXIT)!=0)?'On':'Off')
			);
			$table[] = array(
				'DEBUG_REPORT_OTHER_DATA',
				(((DEBUG_REPORT_LEVEL & DEBUG_REPORT_OTHER_DATA)!=0)?'On':'Off')
			);

			$f->table('Debug Enabled Options', $table);
			
		}
		
		function __destruct(){
			$this->write('*** Debug end', DEBUG_REPORT_CLASS_DESTRUCTION, FirePHP_INFO );
		}
		public function write($data, $level= DEBUG_REPORT_OTHER_DATA, $type= FirePHP_LOG ){
			if(!$this->maniacalDebug && isset($this->disallowedGroups[$groupName]) && $this->disallowedGroups[$groupName] ) return false; 
			if(($level & DEBUG_REPORT_LEVEL) !== 0 || $this->maniacalDebug){

				if($this->nextGroupToWrite !=$this->groupName){
					$f = $this->fb;
					if($this->nextGroupToWrite=='') $f->groupEnd();
					if($this->nextGroupToWrite!='') $f->group($this->nextGroupToWrite . ' ' . date('d/m/Y H:i:s'));
					$this->groupName = $this->nextGroupToWrite;
					
				}
					
				$f = $this->fb;
				switch($type){
					case FirePHP_LOG:
						$f->log( $data, date('d-m-Y H:i:s'));
						break;
					case FirePHP_WARN:
						$f->warn( $data, date('d-m-Y H:i:s'));
						break;
					case FirePHP_ERROR:
						$f->error( $data, date('d-m-Y H:i:s'));
						break;
					case FirePHP_INFO:
						$f->info( $data, date('d-m-Y H:i:s'));
						break;
				}
				
			}
				
		}

		public function writeFunctionArguments($args){
			if((DEBUG_REPORT_FUNCTION_PARAMETERS & DEBUG_REPORT_LEVEL) !== 0 || $this->maniacalDebug){
					$f = $this->fb;
					$f->info($args,'Parametri');
			}
		}
	
		public function maniacalModeOn(){
			$this->maniacalDebug = true;
			$this->write('**** Entering Maniacal Mode ****', DEBUG_REPORT_OTHER_DATA, FirePHP_WARN);
		}
		
		public function maniacalModeOff(){
			$this->write('**** Exiting Maniacal Mode ****', DEBUG_REPORT_OTHER_DATA, FirePHP_WARN);
			$this->maniacalDebug = false;
		}
		
		private function plainValue($value){
			ob_start();
			print_r($value);
			$value = ob_get_clean();
			
			$value = preg_replace('/\\n/i', ' ', $value);
			return $value;
		}
		
		public function restoreGroup($groupName){
			if(isset($this->disallowedgroups[$groupName])) $this->disallowedGroups[$groupName] = false;	
		}
		
		public function skipGroup($groupName){
			$this->disallowedGroups[$groupName] = true;
		}
		public function setGroup($groupName){
			$this->nextGroupToWrite = $groupName; 
		
		}
	}
}
if(!class_exists('CustomDebug')){
	class CustomDebug extends FireDebug{ 	
		
	}
}
?>