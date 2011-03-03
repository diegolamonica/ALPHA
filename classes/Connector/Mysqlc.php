<?php

require_once  CORE_ROOT. 'classes/interfaces/iConnector.php';
require_once CORE_ROOT. 'classes/Debug.php'; 

define('MYSQLC_ERROR_OK', 				0);
define('MYSQLC_ERROR_NO_CONNECTION', 	1);
define('MYSQLC_ERROR_NO_RESOURCE',		2);

!defined('DB_DESCRIPTOR_COLUMN_FIELD') 						&& define('DB_DESCRIPTOR_COLUMN_FIELD',						'Field');
!defined('DB_DESCRIPTOR_COLUMN_KEY') 						&& define('DB_DESCRIPTOR_COLUMN_KEY',						'Key');
!defined('DB_DESCRIPTOR_COLUMN_KEY_PRIMARY') 				&& define('DB_DESCRIPTOR_COLUMN_KEY_PRIMARY',				'PRI');
!defined('DB_DESCRIPTOR_COLUMN_DEFAULT_VALUE') 				&& define('DB_DESCRIPTOR_COLUMN_DEFAULT_VALUE',				'Default');
!defined('DB_DESCRIPTOR_COLUMN_EXTRA_INFO') 				&& define('DB_DESCRIPTOR_COLUMN_EXTRA_INFO',				'Extra');
!defined('DB_DESCRIPTOR_COLUMN_EXTRA_INFO_AUTOINCREMENT') 	&& define('DB_DESCRIPTOR_COLUMN_EXTRA_INFO_AUTOINCREMENT', 	'auto_increment');

class mysqlConnector extends Debugger implements iConnector {
	
	private $conn;
	private $result;
	private $lastError = MYSQLC_ERROR_OK;
	private $lastQuery = '';
	public 	$lastErrorObject = ''; 
	private $pagingIsEnabled = true;
	function mysqlConnector(){
		$this->CustomConnector();
		
	}
	
	function __destruct(){
		
		if($this->isConnected()){
			mysql_close($this->conn);
		}
		
	}
	function CustomConnector(){
		
		$this->__construct();
		/**
		 * Issue 15 - Calling connector via ClassFactory raise some errors if any connection settings given in the application.xml file
		 * Issue 16 - Calling MysqlConnector via ClassFactory raise some errors if any connection settings given in the application.xml file  
		 */
		
		# If at least one of the connector data is given to xml file we should use the auto-connect mode.
		if(CONNECTOR_HOST. CONNECTOR_INSTANCE. CONNECTOR_USERNAME. CONNECTOR_PASSWORD!='')
			$this->connect(CONNECTOR_HOST, CONNECTOR_INSTANCE, CONNECTOR_USERNAME, CONNECTOR_PASSWORD);
			
		/* End Issue 15/16 */
	}
	
	function getLimitClause($fromRow, $rowCount){
		return ' LIMIT ' . $fromRow . ',' . $rowCount;
		
	}
	
	function enablePagination(){
		$this->pagingIsEnabled=true;
	}

	function disablePagination(){
		$this->pagingIsEnabled=false;
	}
	
	function getLastError(){
		return $this->lastError;
	}
	function getLastErrorObject(){
			if($this->lastError!=''){	
			return array(
				'message'=> $this->lastError,
				'sqltext'=> '',
				'details'=> mysql_error($this->conn) 
			);
			}else{
				return null;
			}
	}
	function isConnected(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Processing ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		return ($this->conn!=null);
	}
	
	function connect($host, $db, $user, $password){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		//if(function_exists('mysql_pconnect') ){
		//	$conn = mysql_pconnect($host, $user, $password);
		//}else{
		$conn = mysql_connect($host, $user, $password, true);
		//}
		 mysql_select_db($db, $conn) or die(mysql_error($conn));
		 
#		 if ( function_exists( 'mysql_set_charset' ) ) {
#			mysql_set_charset( 'utf8', $conn );

		
#		} else {

		$dbg->write('Setting charset to ' . SQL_CHARSET, DEBUG_REPORT_OTHER_DATA);
		$dbg->write('Setting collation to ' . SQL_COLLATION, DEBUG_REPORT_OTHER_DATA);

		mysql_query( "SET NAMES " . SQL_CHARSET . " COLLATE " . SQL_COLLATION, $conn );
		mysql_query( "SET CHARACTER SET " . SQL_CHARSET, $conn );
			#		}
		$this->conn = $conn;
	}
	
	function query($sql, $empty = false){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		if($this->isConnected()){
			if($empty){
				$dbg->write('executing empty query');
				mysql_unbuffered_query($sql, $this->conn);
				$this->lastError = mysql_errno($this->conn);
				
			}else{
				$dbg->write('executing query with result');
				
				$p = ClassFactory::get('Paging',false);
				if($this->pagingIsEnabled) $dbg->write("Paging is enabled on $sql");
					
				if($p!=null && $this->pagingIsEnabled) {
					$dbg->write('Paging is enabled and $p is not null');
					if(strpos('LIMIT',strtoupper($sql))!==false){
						
						$dbg->write('paging cannot be enabled');
					}else{
						
						$p->updateCount($sql);
						$sql .= ' ' . $p->buildLimitClause();
						$dbg->write('Rebuilded query: ' . $sql);
						
					}
				}
				
				$this->result = mysql_query($sql, $this->conn) or die(mysql_error($this->conn) . ' - ' . $sql);
				$dbg->write('result is: ' . $this->result);
				
			}
			
			$this->lastQuery = $sql;
		}else{
			$this->lastError = MYSQLC_ERROR_NO_CONNECTION;
			
		}
		
		$dbg->write('Exiting ' . __FUNCTION__ , DEBUG_REPORT_FUNCTION_EXIT );
	}
	
	function allResults(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		if($this->result){
			$return = array();	
			while($rs = mysql_fetch_assoc($this->result)){
				if(function_exists('formatRecordset')){
					$return[] = formatRecordset($rs, $this->lastQuery);
				}else{
					$return[] = $rs;
				}
			}
			if(count($return)==0) $return = null;
		}else{
			$this->lastError = MYSQLC_ERROR_NO_RESOURCE;
			$return = null;
		}
		return $return;
	}
	
	function getArray($sql, $key){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		$this->query($sql);

		$r = $this->allResults();
		$a = array();
		
		for($i= 0; $i<count($r); $i++){
			
			$a[] = $r[$i][$key];
		}
		return ($a);
	}
	
	function moveNext(){
	
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		if($this->result){
			
			$rs = mysql_fetch_assoc($this->result);
			if(function_exists('formatRecordset')){
				$rs = formatRecordset($rs, $this->lastQuery);
			}
		}else{
			$this->lastError = MYSQLC_ERROR_NO_RESOURCE;
			$rs = null;
		}
		return $rs;
		
	}
	
	function getCount(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		if($this->result){
			 $result = mysql_num_rows($this->result);
		}else{
			$this->lastError = MYSQLC_ERROR_NO_RESOURCE;
			$result = 0;
		}
		$dbg->write('Result is: ' . $result, DEBUG_REPORT_OTHER_DATA);
		return $result;
	}
	
	function release($resource= null){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		if ($resource==null) $resource = $this->result;
		if ($resource==null) return;
		
		mysql_free_result($resource);
	}
	
	function getFirstRecord($sql){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		$pagingIsEnabled = $this->pagingIsEnabled;
		
		$c = new mysqlConnector();
		if($pagingIsEnabled) $c->disablePagination();
		$c->query($sql);
		$rs = $c->moveNext();
		$c->release();
		if($pagingIsEnabled) $c->enablePagination();
		unset($c);
		return $rs;
	}
	
	public function getId(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		if($this->isConnected()){
			return mysql_insert_id($this->conn);
		}else{
			$this->lastError = MYSQLC_ERROR_NO_CONNECTION;
		}
	}

	public function processDataValue($value, $key = '', $table = ''){
		if(function_exists('customProcessDataValue')){
			$value = customProcessDataValue($value, $key, $table);
		}
		if(is_array($value)) return $value;
		/**
		 * Issue 14 - mysql should escape char with its own method
		 */
		# Only if magic quotes is active we should strip the slashes from the value.
		# Else it's sufficient to escape the string using mysql_escape_string.
		if(get_magic_quotes_gpc()=='1') $value = stripslashes($value);
		$value = mysql_escape_string($value);
		/**
		 * End Issue 14
		 */
		return array('before'=>"'", 'value'=>$value, 'after'=>"'");
		
	}
	public function describeTable($tableName){
		
		$this->query('describe ' . $tableName);
		$fields = array();
		
		while($rs = $this->moveNext()) $fields[] =  $rs;
		
		return $fields;
		
	}
	
}

if(!class_exists('CustomConnector')){
	class CustomConnector extends mysqlConnector{
		
	}
}
?>