<?php
/**
 * @author Diego La Monica
 * @version 1.1
 * @name Events
 * @package ALPHA
 * @uses Debugger
 */

require_once CORE_ROOT . 'classes/Debug.php';
class Events extends Debugger{
	/*
	 * ChangeLog:
	*
	* V 1.1
	* - Added VERSION constant
	* - Added unregister method to unbind events
	* - Added filter method to manage data filtering
	* - Added unfilter method to remove a previously defined filter
	* - Added apply method to run filter over data
	* - Added methods scope
	* 
	*/
	const VERSION = '1.1';
	
	private $events = array();
	private $filters = array();
	
	/**
	 * Bind a method to certain event
	 * @param $event The event name
	 * @param $method The method to invoke
	 */
	public function register($event, $method){
	
		if(!isset($this->events[$event])) $this->events[$event] = array();
		
		if(array_search($method, $this->events[$event],true)===false){
			$this->events[$event][] = $method;
		}
		
	}
	
	/**
	 * Bind a method to certain filter
	 * @param string $filter the filter name
	 * @param string $method the method to invoke in occourrence of the given filter.
	 */
	public function filter($filter, $method){
		
		if(!isset($this->filters[$filter])) $this->filters[$filter] = array();
		
		if(array_search($method, $this->filters[$filter],true)===false){
			$this->filters[$filter][] = $method;
		}
		
	}
	
	/**
	 * Unbind a defined method filter or all methods from a filter if the method is not given   
	 * @param string $filter the filter name
	 * @param string|null $method (optional default is null) the method to unbind
	 */
	public function unfilter($filter, $method = null){
		
		if(isset($this->filters[$filter])){
			
			if(is_null($method)) 
				$this->filters[$filter] = array();
			else{
				
				$methodIndex = array_search($method, $this->filters[$filter],true);
				if($methodIndex!==false){
					
					unset($this->filters[$filter][$methodIndex]);
					
				}
			} 
			
		}
		
	}
	
	/**
	 * Apply all the defined filters to the given value 
	 * @param string $filter
	 * @param any $value the input value to be manipulated
	 * @param mixed ... a list of arguments
	 * @return any the filtered $value
	 */
	public function apply($filter, $value){
		
		if(isset($this->filters[$filter])){
			
			$args = func_get_args();
			/*
			 * Shift out the $filter function argument, and the value argument,
			 * all other argumens are the optional arguments to pass to the 
			 * binded method
			 */
			array_shift($args);
			array_shift($args);
			/*
			 * Apply all the defined filters  
			 */
			foreach($this->filters[$filter] as $method){
				$value = call_user_func_array($method, array_merge($value, $args));
			}
			
		}
		return $value;
		
	}
	
	/**
	 * Unbind a defined method event   
	 * @param string $event the event name
	 * @param string|null $method the method to unbind
	 */
	public function unregister($event, $method){
		error_log("trying to deatach listner $method() from $event");
		if(isset($this->events[$event])){
			error_log("$event has at least one listner");
			foreach($this->events[$event] as $index => $eventMethod){
				if($eventMethod == $method){
					error_log("$method found");
					unset($this->events[$event][$index]);
					break;
				} 
				
			}
		}
		
	}
	
	/**
	 * Invokes all methods binded to the given event
	 * @param $eventName (string) Event name to raise
	 * @param (mixed) ... arguments to give to each called method    
	 */
	public function raise($eventName){
		
		$argList = func_get_args();
		
		
		$eventName = array_shift($argList);
		if(isset($this->events[$eventName])){
			$theEventMethods = $this->events[$eventName];
			$argList[] = $this;
			for($i = 0; $i<count($theEventMethods); $i++){
				call_user_func_array($theEventMethods[$i], $argList);
			}
		}
		
	}

}