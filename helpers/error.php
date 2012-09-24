<?php
class HelperError{
	
	/**
	 * Send to apache log the alert that the specific method is deprecated in favor of the newes, since certain version.
	 * @param string $functionName
	 * @param string $version
	 * @param string $use
	 */
	static function methodDeprecated($functionName, $version, $use){
		
		error_log("Method `$functionName` is deprecated since version `$version` use `$use` instead.", 0);
		
	}
	
	/**
	 * Send to apache log the alert that the specific method parameter is deprecated since certain version.
	 * @param string $functionName
	 * @param string $version
	 * @param string $use
	 */
	 static function paramDeprecated($functionName, $parameter, $version){
		
		error_log("Parameter `$paramter` of method `$functionName` is deprecated since version `$version`.");
		
	}
	
}