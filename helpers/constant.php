<?php
class HelperConstant{
	
	private static $constantsToBeDefined = array();
	
	/**
	 * Add constant to the definition queue.
	 * @param string $key the name of the constant to be defined
	 * @param any $value the value the constant will assume or the name of the referring constant if $referencesConstant is true
	 * @param bool $overwrite (optional default `false`) if this declaration is more important than the previouses than that will be overriden 
	 * @param bool $referencesConstant (optional default `false`) if the value is referring to a defined constant this value must be true.  
	 * @param bool $immediate (optional default `false`) if the definition must be immediate
	 */
	public static function define($key, $value, $overwrite= false, $referencesConstant = false, $immediate = false){
		if(!isset(self::$constantsToBeDefined[$key] ) || $overwrite){
			if($immediate){
				self::applySingle($key, array($value, $referencesConstant));
			}else{
				self::$constantsToBeDefined[$key] = array($value, $referencesConstant);
			}
		}
	}
	
	/**
	 * Create the definition constant if it not exists and creates any related constant.
	 * @param string $key the key to be defined
	 * @param any $value the value.
	 */
	public static function applySingle($key, $value){
		if($value[1]){
			if(!defined($value[0])){
				
				self::applySingle($value[0], self::$constantsToBeDefined[$value[0]]);
			}
			$value[0]=constant($value[0]);
		}
		if(!defined($key)) define($key, $value[0]);
	}

	/**
	 * Create all constant in the queue of definitions.
	 */
	public static function applyAll(){
		
		foreach(self::$constantsToBeDefined as $key => $value){
			if(!defined($key)){
				self::applySingle($key, $value);
			}
		}
		
	}
	
}