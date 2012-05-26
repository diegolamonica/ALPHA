<?php
require_once  CORE_ROOT. 'classes/interfaces/iConnector.php';
require_once CORE_ROOT. 'classes/Debug.php'; 

class MysqlConnector extends Debugger implements iConnector {
	
	const VERSION = '2.0';
	
	private $conn;
	private $result;
	private $lastError = DB_ERROR_OK;
	private $lastQuery = '';
	public 	$lastErrorObject = ''; 
	private $pagingIsEnabled = true;
	
	
	function MysqlConnector(){
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
	
	function call($storedProcedure, $params = null, $options = null){
		
		if(is_array($params)){
			# Creating missing informations for parameters
			foreach($params as $index => $paramInfo){
				if(!isset($paramInfo[connector::CALL_PARAM_VALUE])) $paramInfo[connector::CALL_PARAM_VALUE] = null;
				if(is_null($paramInfo[connector::CALL_PARAM_VALUE])) $paramInfo[connector::CALL_PARAM_OUTPUT] = true;
				if(!isset($paramInfo[connector::CALL_PARAM_OUTPUT])) $paramInfo[connector::CALL_PARAM_OUTPUT] = false;
				
				$params[$index] = $paramInfo;
				
			}
		}
		
		# Building the SQL caller string
		$sql ="call $storedProcedure(";
		$outputParameters = "";
		
		if(is_array($params)){
			$paramList = '';
			foreach($params as $paramInfo){
				if($paramList!='') $paramList.=',';
				if($paramInfo[connector::CALL_PARAM_OUTPUT]){
					$outParamName = '@'.$paramInfo[connector::CALL_PARAM_NAME];
					$paramList .= $outParamName;
					if($outputParameters!='') $outputParameters.=', ';
					$outputParameters .= $outParamName;
				}else{
					# Upgrading to mysqli
					$value = mysqli_real_escape_string($this->conn, $paramInfo[connector::CALL_PARAM_VALUE]);
					#$value = mysql_real_escape_string($paramInfo[self::CALL_PARAM_VALUE]);
					$paramList .= "'$value'";
				}
			}
			$sql .= $paramList;
		}
		$sql .=")";
		$output = array();
		if(isset($options[connector::CALL_OPTIONS_RESULTSET])){
			$this->query($sql);
		}else{
			$this->query($sql, true);
			if($outputParameters!=''){
				$result = mysqli_query($this->conn, "select $outputParameters");
				$rs = mysqli_fetch_assoc($result);
				
				#$rs = $this->getFirstRecord($sql);
				foreach($rs as $key => $value){
					
					$key = substr($key, 1);
					$output[$key] = $value;
					
				}
				mysqli_free_result($result);
			}
		}
		return $output;
		
		
		
	}
	
	function __destruct(){
		
		if($this->isConnected()){
			mysqli_close($this->conn);
		}
	}
	
	public function getLimitClause($fromRow, $rowCount){
		return ' LIMIT ' . $fromRow . ',' . $rowCount;
		
	}
	
	public function enablePagination(){
		$this->pagingIsEnabled=true;
	}

	public function disablePagination(){
		$this->pagingIsEnabled=false;
	}
	
	public function getLastError(){
		return $this->lastError;
	}
	public function getLastErrorObject(){
			if($this->lastError!=''){	
			return array(
				'message'=> $this->lastError,
				'sqltext'=> '',
				'details'=> mysqli_error($this->conn) 
			);
			}else{
				return null;
			}
	}
	public function isConnected(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Processing ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		return ($this->conn!=null);
	}
	
	public function connect($host, $db, $user, $password){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		
		/**
		 * Issue 20 - MysqlConnector must use pconnect instead of connect as default
		 * 
		 * 1. the mysql_pconnect as default connector (checking if that method exists)
		 * 2. the boolean constant USE_PERSISTENT_CONNECTION to switch between pconnect and connect methods.
		 */
		# Upgrading to mysqli
		#if(function_exists('mysql_pconnect') && (!defined('USE_PERSISTENT_CONNECTION') || USE_PERSISTENT_CONNECTION)){
		#	$conn = mysql_pconnect($host, $user, $password);
		#}else{
		#	/*
		#	 * 3. the fallback support using mysql_connect
		#	 */
		#	$conn = mysql_connect($host, $user, $password, true);
		#}
		#mysql_select_db($db, $conn) or die(mysql_error($conn));
		$this->conn = mysqli_connect($host, $user, $password,$db);
		# Connection fails for some reasons
		# In the will I need to tell the user more about the connection error reasons
		if($this->conn===false) return;
		$dbg->write('Setting charset to ' . SQL_CHARSET, DEBUG_REPORT_OTHER_DATA);
		$dbg->write('Setting collation to ' . SQL_COLLATION, DEBUG_REPORT_OTHER_DATA);

		#mysql_query( "SET NAMES " . SQL_CHARSET . " COLLATE " . SQL_COLLATION, $conn );
		#mysql_query( "SET CHARACTER SET " . SQL_CHARSET, $conn );
		$this->query("SET NAMES " . SQL_CHARSET . " COLLATE " . SQL_COLLATION, true);
		$this->query("SET CHARACTER SET " . SQL_CHARSET . " COLLATE " . SQL_COLLATION, true);
		# Issue #22 - Note #23
		if ( function_exists( 'mysqli_set_charset' ) ) {
			mysqli_set_charset($this->conn, SQL_CHARSET);
		}
		#if ( function_exists( 'mysql_set_charset' ) ) {
		#	mysql_set_charset(SQL_CHARSET, $conn);
		#}
		# $this->conn = $conn;
	}

	public function query($sql, $empty = false, $returnId=true, $unwatched = false){
		ClassFactory::get('Debug')->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		ClassFactory::get('Debug')->writeFunctionArguments(func_get_args());
		
		ClassFactory::get('Events')->raise(ALPHA_EVENT_CONNECTOR_ON_QUERY, $sql, $empty, $returnId, $unwatched);
		
		if($this->isConnected()){
			
			if($empty){
				ClassFactory::get('Events')->raise(ALPHA_EVENT_CONNECTOR_ON_EMPTY_QUERY, $sql, $empty, $returnId, $unwatched);
				ClassFactory::get('Debug')->write('executing empty query');
				$isInsert = false;
				if(substr( trim( strtolower($sql)) , 0,6) =='insert' && $returnId){
					$isInsert = true;
					#$sql .= (' returning id into :ID');
					ClassFactory::get('Events')->raise(ALPHA_EVENT_CONNECTOR_ON_INSERT, $sql);
				}
				
				ClassFactory::get('Debug')->write('executing empty query');
				#mysql_unbuffered_query($sql, $this->conn);
				$resource = mysqli_query($this->conn, $sql);
				/*
				 * From: http://php.net/manual/en/mysqli.query.php
				 * Returns FALSE on failure. For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries mysqli_query() 
				 * will return a mysqli_result object. For other successful queries mysqli_query() will return TRUE
				 */
				if($resource!==false && $resource!==true) mysqli_free_result($resource);
				$this->lastError = mysqli_errno($this->conn);
				#$this->lastError = mysql_errno($this->conn);
				if($isInsert) $this->lastId = mysqli_insert_id($this->conn);
				
			}else{
				ClassFactory::get('Events')->raise(ALPHA_EVENT_CONNECTOR_ON_RESULTSET_QUERY, $sql, $empty, $returnId, $unwatched);
				ClassFactory::get('Debug')->write('executing query with result');
				
				$p = ClassFactory::get('Paging',false);
				if($this->pagingIsEnabled) ClassFactory::get('Debug')->write("Paging is enabled on $sql");
					
				if($p!=null && $this->pagingIsEnabled) {
					ClassFactory::get('Debug')->write('Paging is enabled and $p is not null');
					if(strpos('LIMIT',strtoupper($sql))!==false){
						
						ClassFactory::get('Debug')->write('paging cannot be enabled');
					}else{
						ClassFactory::get('Events')->raise(ALPHA_EVENT_CONNECTOR_ON_PAGINATION, $p);
						$p->updateCount($sql);
						$sql .= ' ' . $p->buildLimitClause();
						ClassFactory::get('Debug')->write('Rebuilded query: ' . $sql);
						
					}
				}
				#$this->result = mysql_query($sql, $this->conn) or die(mysql_error($this->conn) . ' - ' . $sql);
				$resource = mysqli_query($this->conn, $sql) or die(mysqli_error($this->conn) . ' - ' . $sql);
				if(!$unwatched){
					$this->result = $resource;
				}
				#$dbg->write('result is: ' . $this->result);
				
			}
			if(!$unwatched){
				$this->lastQuery=$sql;
			}
			# $this->lastQuery = $sql;
		}else{
			$this->lastError = DB_ERROR_NO_CONNECTION;
		}
		
		ClassFactory::get('Debug')->write('Exiting ' . __FUNCTION__ , DEBUG_REPORT_FUNCTION_EXIT );
		if($unwatched){
			return $resource;
		}
	}
	
	public function allResults(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		if($this->result){
			$return = array();	
			#while($rs = mysql_fetch_assoc($this->result)){
			while($rs = mysqli_fetch_assoc($this->result)){
				if(function_exists('formatRecordset')){
					$return[] = formatRecordset($rs, $this->lastQuery);
				}else{
					$return[] = $rs;
				}
			}
			if(count($return)==0) $return = null;
			$this->release($this->result);
			/*
			 * Multiple queries and stored procedures cause the "out of sync" error because
			 * there are more results to fetch.
			 */
			while(mysqli_more_results($this->conn)) mysqli_next_result($this->conn);
			$this->result = null;
		}else{
			$this->lastError = DB_ERROR_NO_RESOURCE;
			$return = null;
		}
		
		return $return;
	}
	
	public function getArray($sql, $key){
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
	
	public function moveNext($resource = null){
	
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		if(is_null($resource)) $resource = $this->result;
		if($resource){
			
			#$rs = mysql_fetch_assoc($this->result);
			$rs = mysqli_fetch_assoc($resource);
			if(function_exists('formatRecordset')){
				$rs = formatRecordset($rs, $this->lastQuery);
			}
		}else{
			$this->lastError = DB_ERROR_NO_RESOURCE;
			$rs = null;
		}
		return $rs;
		
	}
	
	public function getCount(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		if($this->result){
			 #$result = mysql_num_rows($this->result);
			 $result = mysqli_num_rows($this->result);
		}else{
			$this->lastError = DB_ERROR_NO_RESOURCE;
			$result = 0;
		}
		$dbg->write('Result is: ' . $result, DEBUG_REPORT_OTHER_DATA);
		return $result;
	}
	
	public function release($resource= null){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		if ($resource==null) $resource = $this->result;
		if ($resource==null) return;
		
		#mysql_free_result($resource);
		
		if($resource!=null) mysqli_free_result($resource);
	}
	
	public function getFirstRecord($sql){
		ClassFactory::get('Debug')->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		ClassFactory::get('Debug')->writeFunctionArguments(func_get_args());
		$PaginationStatus = $this->pagingIsEnabled;
		$this->disablePagination();
		$stm = $this->query($sql, false, false, true);
		$rs = $this->moveNext($stm);
		$this->release($stm);
		$this->pagingIsEnabled = $PaginationStatus;
		
		if(function_exists('formatRecordset')){
			$rs = formatRecordset($rs, $sql);
		}
		return $rs;

	}
	
	public function getId(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		if($this->isConnected()){
		#	return mysql_insert_id($this->conn);
			return mysqli_insert_id($this->conn);
		}else{
			$this->lastError = DB_ERROR_NO_CONNECTION;
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
		#$value = mysql_escape_string($value);
		$value = mysqli_escape_string($this->conn, $value);
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

?>