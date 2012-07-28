<?php
Class AlphaUnitTestFailureException extends Exception{
	
}

Class AlphaUnitTest{
	
	private $passedCount 	= 0;
	private $failedCount	= 0;
	private $index			= 0;
	
	private $throw 			= true;
	private $stop 			= false;
	private $id 			= '';
	 
	public function __construct($id){
		$this->id = $id;
	}
	
	public function throwException($status){
		$this->throw = $status;
	}
	
	public function stopOnFirstError($status){
		$this->stop = $status;
	}
	
	/**
	 * Check for value
	 * @param any $a
	 * @param any $b
	 * @return bool
	 */
	public function sameValue($a, $b){
		$output = ($a == $b);
		return $output; 
	}
	/**
	 * Check type and value 
	 * @param any $a
	 * @param any $b
	 * @return bool
	 */
	public function equals($a, $b){
		$output = (var_export($a,true) === var_export($b,true));
		return $output;
		
	}
	
	/**
	 * Check if $a is null
	 * @param any $a
	 * @return boolean
	 */
	public function isNull($a){
		$output = is_null($a);
		return $output;
	}
	
	/**
	 * Check if $a is an array
	 * @param any $a
	 * @return boolean
	 */
	public function isArray($a){
		$output = is_array($a);
		return $output;
	}
	
	/**
	 * Check if $a is an object
	 * @param any $a
	 * @return boolean
	 */
	public function isObject($a){
		$output = is_object($a);
		return $output;
	}
	
	/**
	 * Check if $b is an existing key or property into $a 
	 * @param unknown_type $a
	 * @param unknown_type $b
	 * @return bool
	 */
	public function has($a, $b){
		$output = (is_array($a) && isset($a[$b])) || (is_object($a) && isset($a->$b));
		return $output;
	}
	
	/**
	 * Raise a AlphaUnitTestFailureException or an alert if check is true. 
	 * @param bool $check
	 * @param string $success
	 * @param string $error
	 */
	public function throwIfNot($check, $success, $error){
		$args = func_get_args();
		$args[0] = !$args[0];
		call_user_func_array(array($this, 'throwIf' ), $args);
	}
	
	public function throwIf($check, $success, $error){
		$this->index+=1;
		$index = "[#{$this->id}.$this->index". (($this->stop && $this->failedCount>0)?" STOP":''). '] ';
		if($check || $this->stop && $this->failedCount>0 ){
			if(!$this->stop) $this->failedCount += 1;
			$args = func_get_args();
			array_shift($args); # removing $check from args 
			array_shift($args); # removing $success from args
			array_shift($args); # removing $error from args
			$args = var_export($args,true);
			if($this->throw){
				throw new AlphaUnitTestFailureException("$index $error\n\n$args\n");
			}else{
				echo("<p class=\"error\">$index $error</p><pre>$args</pre>");
			}
		}else{
			$this->passedCount += 1;
			echo("<p class=\"success\">$index $success</p>");
		}
		
	}
	
	/**
	 * Return the number of passed unit test
	 * @return integer
	 */
	public function passed(){
		return $this->passedCount;
	}
	
	/**
	 * Return the number of failed unit test
	 * @return integer
	 */
	public function failed(){
		return $this->failedCount;
	}
	/**
	 * Returns true if any unit test raises error.
	 * @return bool 
	 */
	public function isFullCompliant(){
		return $this->failedCount = 0;
	}
	
}