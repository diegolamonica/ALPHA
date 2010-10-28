<?php

require_once  CORE_ROOT. 'classes/Debug/debugger.php';
require_once  CORE_ROOT. 'classes/interfaces/iDebug.php';
if(!class_exists('SilentDebug')){
	class SilentDebug implements iDebug{
		function __construct(){	}
		
		function __destruct(){ }
		public function write($data, $level= DEBUG_REPORT_OTHER_DATA){ }

		public function writeFunctionArguments($args){ }
	
		public function maniacalModeOn(){ }
		
		public function maniacalModeOff(){ }
		
		private function plainValue($value){ }
		
		public function restoreGroup($groupName){ }
		
		public function skipGroup($groupName){ }
		public function setGroup($groupName){ }
	}
}
if(!class_exists('CustomDebug')){
	class CustomDebug extends SilentDebug{ 	
		
	}
}
?>