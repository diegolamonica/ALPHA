<?php

if(!class_exists('Debugger')){
	class Debugger{
		/**
		 * @ignore
		 */
		function __construct(){		
			$dbg = ClassFactory::get('Debug');
			$dbg->setGroup(get_class( $this) );
			$dbg->write('Class ' . get_class($this) . ' created' , DEBUG_REPORT_CLASS_DESTRUCTION, FirePHP_WARN);
			
			
		}
		
		/**
		 * @ignore
		 */
		function __destruct(){
			$dbg = ClassFactory::get('Debug');
			$dbg->setGroup('');
			$dbg->write('Class ' . get_class($this) . ' destructed' , DEBUG_REPORT_CLASS_DESTRUCTION, FirePHP_WARN);
		}
		
		/**
		 * 
		 */
		
		function __call($functionName, $arguments ){
			
			$m = ClassFactory::get('Model');
			
			$m->setView('error');
			$m->setVar('errorDescription', ERROR_FUNCTION_NOT_FOUND);
			$m->setVar('calledFunction', $functionName);
			$m->setVar('calledClass', get_class($this));
			$m->setVar('calledFunctionArguments',$arguments);
			/*
			 * Variable $_GET['__url'] no more used related to security issue
			 * https://github.com/diegolamonica/ALPHA/issues#issue/1
			 */
			$m->setVar('script', REQUESTED_URL);
			/*$m->setVar('script', $_GET['__url']);*/
			$m->setVar('uri', $_SERVER['REQUEST_URI']);
			$m->process();
			$m->render();
			exit();
		}
		
	}
}
?>