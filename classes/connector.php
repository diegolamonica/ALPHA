<?php

require_once  CORE_ROOT. 'classes/interfaces/iConnector.php';
require_once CORE_ROOT. 'classes/Debug.php'; 
!defined('DB_ERROR_OK') && define('DB_ERROR_OK', 				0);
!defined('DB_ERROR_NO_CONNECTION') && define('DB_ERROR_NO_CONNECTION', 	1);
!defined('DB_ERROR_NO_RESOURCE') && define('DB_ERROR_NO_RESOURCE',		2);

!defined('DB_DESCRIPTOR_COLUMN_FIELD') 						&& define('DB_DESCRIPTOR_COLUMN_FIELD',						'Field');
!defined('DB_DESCRIPTOR_COLUMN_KEY') 						&& define('DB_DESCRIPTOR_COLUMN_KEY',						'Key');
!defined('DB_DESCRIPTOR_COLUMN_KEY_PRIMARY') 				&& define('DB_DESCRIPTOR_COLUMN_KEY_PRIMARY',				'PRI');
!defined('DB_DESCRIPTOR_COLUMN_DEFAULT_VALUE') 				&& define('DB_DESCRIPTOR_COLUMN_DEFAULT_VALUE',				'Default');
!defined('DB_DESCRIPTOR_COLUMN_EXTRA_INFO') 				&& define('DB_DESCRIPTOR_COLUMN_EXTRA_INFO',				'Extra');
!defined('DB_DESCRIPTOR_COLUMN_EXTRA_INFO_AUTOINCREMENT') 	&& define('DB_DESCRIPTOR_COLUMN_EXTRA_INFO_AUTOINCREMENT', 	'auto_increment');

class connector{
	
	const VERSION = '2.0';
	
	const CALL_PARAM_NAME 			= 0;
	const CALL_PARAM_VALUE			= 1;
	const CALL_PARAM_OUTPUT			= 2;
	const CALL_PARAM_TYPE 			= 3;
	const CALL_PARAM_SIZE 			= 4;
	const CALL_OPTIONS_RESULTSET	= 'RESULTSET';
	/**
	 * Connector specific class
	 * @var iConnector
	 */
	private $connector = null;
	/**
	 * @return iConnector
	 */
	function __construct(){
		if(defined('APPLICATION_CONNECTOR_MODULE')){
			$this->setModule(APPLICATION_CONNECTOR_MODULE);
			#$this->connect();
		}
	}
	/**
	 * Set the connector module according to its name in the Connector directory
	 * @param string $moduleName
	 */
	public function setModule($moduleName){
		
		if(!is_null($this->connector)){
			
			unset($this->connector);
			$this->connector = null;
			
		}
		$className = "{$moduleName}Connector";
		require_once dirname(__FILE__) . "/Connector/$className.php";
		$this->connector = new $className();
		
	}
	
	/**
	 * Magic method to call the relative method in the real connector instantiated class
	 * @param string $methodName
	 * @param array $methodParameters
	 * @return any
	 */
	public function __call($methodName, $methodParameters){
		return call_user_func_array(array($this->connector, $methodName), $methodParameters);
	}	
}

/**
 * 
 * Deprecated way to call the connector class
 * @deprecated
 * @author diego
 *
 */
/* class connector extends Connector{} */
