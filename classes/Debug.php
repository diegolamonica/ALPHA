<?php

!defined('APPLICATION_DEBUG_MODULE') && define('APPLICATION_DEBUG_MODULE','BaseDebug');

require CORE_ROOT. 'classes/Debug/' . APPLICATION_DEBUG_MODULE . '.php';


class Debug extends CustomDebug{
	
	function Debug(){
		
		parent::__construct();
		
	}
	
}

?>