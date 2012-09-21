<?php
require_once  CORE_ROOT. 'classes/interfaces/iConnector.php';
require_once CORE_ROOT. 'classes/Debug.php'; 

class Oci8Connector extends Debugger implements iConnector {
	/*
	 * CHANGELOG:
	 * V 2.2
	 *  - allowed to invoke stored procedure passing parameters by its name
	 * 
	 * V 2.1
	 * - describeTable method is invoking all_tab_columns (better) instead of user_tab_columns
	 * - insert query is building the columns section in the query if missed and try to identify by it's own the id field name
	 * - added logout() method
	 * 
	 * V 2.0
	 * - Complete refactoring of source code
	 */
	
	# const VERSION = '2.0';
	#const VERSION = '2.1';
	const VERSION = '2.2';
	private $conn;
	private $result;
	public $lastQuery;
	private $lastError = DB_ERROR_OK;
	public  $lastErrorObject = '';
	private $pagingIsEnabled = true;
	private $lastId = 0;
	private $doCommit = false;
	
	private $connector_host;
	private $connector_instance;
	private $connector_username;
	private $connector_password;
	
	
	private $descriptors = array();
	private $seequences = array();
	function Oci8Connector($connector_host ='', $connector_instance='', $connector_username='', $connector_password=''){
		$this->__construct();
		
		if($connector_host=='') 	$connector_host 	= CONNECTOR_HOST;
		if($connector_instance=='') $connector_instance = CONNECTOR_INSTANCE;
		if($connector_username=='') $connector_username = CONNECTOR_USERNAME;
		if($connector_password=='') $connector_password = CONNECTOR_PASSWORD;
	
		$this->connector_host = $connector_host;
		$this->connector_instance = $connector_instance;
		$this->connector_username = $connector_username;
		$this->connector_password = $connector_password;
		/*
		 *  Resolution of issue #17 - http://alpha.diegolamonica.info/issues/view.php?id=17
		 *  if at least one of the given parameters are defined you can make the autoconnection.
		 */
		if($this->connector_host.$this->connector_instance.$this->connector_username.$this->connector_password!='')
			$this->connect($connector_host, $connector_instance, $connector_username, $connector_password);
	}
	
	function __destruct(){
		$this->logout();
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Class ' . get_class($this) . ' destructed' , DEBUG_REPORT_CLASS_DESTRUCTION);
	}
	
	function getLimitClause($fromRow, $rowCount){
		return ' rownum<=' . ($fromRow+$rowCount) . (($fromRow!=-1)?') where limitCountColumn> ' . $fromRow:'');
		
	}
	
	public function logout(){
		if($this->conn!=null){
			ocicommit($this->conn);	
			ocilogoff($this->conn);
		}
	}
	
	function doCommit($value = null){
		if($value!=null){
			$this->doCommit = $value;
		}
		return $this->doCommit;
	}
	/**
	 * Enable the pagination
	 */
	function enablePagination(){
		$this->pagingIsEnabled=true;
	}

	/**
	 * Disable the pagination
	 */
	function disablePagination(){
		$this->pagingIsEnabled=false;
	}
	
	function getLastError(){
		return $this->lastError;
	}
	
	function getLastErrorObject(){
		if($this->lastErrorObject!=''){
			$leo = $this->lastErrorObject;
			$dtl = substr($leo['sqltext'], $leo['offset']);
			$c = substr($dtl,0,1);
			if($c=="'" || $c=='"'){
				// Applico la regEx per estrarre la stringa
				
				preg_match('/('.$c.'[^'.$c.'\\\]*(?:\\\.[^'.$c.'\\\]*)*'.$c.')/i', $dtl, $dtl);
				$dtl = $dtl[0];
			}else{
				
				preg_match('/^([^\W]*)/', $dtl, $dtl);
				if(count($dtl)>1){
					$dtl = $dtl[1];
				}
			}
			
			return array(
				'message'=> $leo['message'],
				'sqltext'=> $leo['sqltext'],
				'details'=> '<strong>'.$dtl.'</strong> non è valido', 
			);
		}else{
			return null;
		}
	}
	
	/**
	 * @see iConnector::isConnected()
	 */
	function isConnected(){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Processing ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		return ($this->conn!=null);
	}
	
	function connect($host, $db, $user, $password){
		if($this->isConnected()) return;
		
		$evt = ClassFactory::get('Events');
		$evt->raise(ALPHA_EVENT_CONNECTOR_ON_CONNECTION, $host, $db, $user, $password);
		
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		# In one of the next release i should define NLS_LANG into application.xml constants section.
		exec('set NLS_LANG=ITALIAN_ITALY.AL32UTF8');
		if($host==''){
			# I've specified only the name of the instance as defined in the TNSNAME
			$evt->raise(ALPHA_EVENT_CONNECTOR_ON_TNSNAME_CONNECTION, $host, $db, $user, $password);
			$conn = OCILogon( $user, $password, $db, 'AL32UTF8');
			#$conn = OCILogon( $user, $password, $db, 'WE8MSWIN1252');
		}else{
			# I've specified both server and db instance not defined in the TNSNAME 
			$evt->raise(ALPHA_EVENT_CONNECTOR_ON_URL_CONNECTION, $host, $db, $user, $password);
			$conn = OCILogon( $user, $password, '//'.$host.'/' . $db, 'AL32UTF8');
		}
		$this->conn = $conn;
		
		# In a next release we should put out of the framework the following session definition data
		# or at least define some constants into DB Constants file overridable by application.xml
		$this->query('alter session set nls_language = ITALIAN',true);
		$this->query('alter session set nls_territory = ITALY',true);
		$this->query('alter session set nls_sort = binary_ci',true);
		$this->query('alter session set nls_comp = linguistic',true);
		$this->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY'", true);
		# -------------------------------------------------------------------------------
		
		$evt->raise(ALPHA_EVENT_CONNECTOR_ON_ALTER_SESSION, $this);
	}
	
	/**
	 * Set the error object and raise an error event 
	 * @param array $errorObject
	 * @param string $sql
	 * @param string $specificEvent
	 */
	private function setErrorObject($errorObject, $sql, $specificEvent = ALPHA_EVENT_CONNECTOR_ON_ERROR){
		
		$e = ClassFactory::get('ErrorManager');
		$evt = ClassFactory::get('Events');
		$evt->raise($specificEvent, $errorObject, $sql);
		$this->lastErrorObject = $errorObject;
		
		# Improved for Oracle internal error
		$errorObject['code'] = substr("0000" . $errorObject['code'], -5);
		preg_match("/ORA\-" . $errorObject['code'] ."\:(.*)(\r\n|\r|\n|$)/i", $errorObject['message'], $errorMessage);
		
		$e->setText($errorMessage[1]);
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Execution raised error: ' .  $errorObject['message']);

	}
	
	/**
	 * 
	 * Executes both a stored procedure or a function with given parameters and return the output parameters.
	 * Further information available in the online documentation.
	 * 
	 * @param string $storedProcedure stored procedure name
	 * @param array $params the list of given parameters and relative values
	 * @param array $options extra options
	 * @return array
	 */
	function call($storedProcedure, $params = null, $options = null){
		/*
		 * $storedProcedure => 'call PKG.MYSP';
		 * 
		 * $params =>
		 * 		array(
		 * 			0 => array('i1', 1234), // Input Params
		 * 			1 => array('i2', 'test'), // Input Params
		 * 			2 => array('io3', 'test', true), // Input/output
		 * 			3 => array('o4'),	// Output
		 * 			4 => array('rc', null, true, OCI_B_CURSOR)
		 * 		)
		 * 	
		 * $options =>
		 * 		array(
		 * 			'RESULTSET', ':rc'
		 * 		)
		 * 
		 * 
		 * Execution of:
		 * 		call PKG.SP(:i1, :i2, :io3, :o4, :rc);
		 * 
		 * After Execution:
		 * 
		 * 	$this->result = :rc
		 * 
		 * Output:
		 * 
		 * 	array(
		 * 		':io3' => generated value by :io3
		 * 		':o4' => generated value by :o4
		 * 		':rc' => generated value by :rc
		 *  )
		 * 
		 * 
		 */
		
		# Builidng the Stored Procedure Query
		$sql ="call $storedProcedure(";
		if(is_array($params)){
			$paramList = '';
			
			foreach($params as $paramInfo){
				if($paramList!='') $paramList.=',';
				# If the option PARAM_BY_NAME is defined and set to true then 
				# each parameter will be binded by original name
				
				if(isset($options[connector::CALL_OPTIONS_PARAM_BY_NAME]) && $options[connector::CALL_OPTIONS_PARAM_BY_NAME]==true) 
					$paramList .= $paramInfo[connector::CALL_PARAM_NAME] .'=>';
				# Oracle uses ":" as placeholder for binding parameters
				$paramList .= ':'.$paramInfo[connector::CALL_PARAM_NAME];
			}
			$sql .= $paramList;
		}
		$sql .=")";
		$this->lastQuery = $sql;
		
		$stm = oci_parse($this->conn, $sql);
		
		if(is_array($params)){
			# Binds all params
			foreach($params as $index => $paramInfo){
				if(!isset($paramInfo[1])){
					# it means that the parameter is an output parameter
					$params[$index][connector::CALL_PARAM_VALUE] = null;
					$params[$index][connector::CALL_PARAM_OUTPUT] = true; 
					
					
				}
				if(!isset($paramInfo[connector::CALL_PARAM_TYPE])) $paramInfo[connector::CALL_PARAM_TYPE] = SQLT_CHR;
				if(!isset($paramInfo[connector::CALL_PARAM_SIZE])) $paramInfo[connector::CALL_PARAM_SIZE] = -1;
				
				if($paramInfo[connector::CALL_PARAM_TYPE]== OCI_B_CURSOR){
					# If the parameter is a cursor I need to instantiate it
					$params[$index][1] = oci_new_cursor($this->conn);
					
				}
				
				oci_bind_by_name($stm, 
					# Oracle uses ":" as placeholder for binding parameters
					$paramInfo[connector::CALL_PARAM_NAME], 
					$params[$index][connector::CALL_PARAM_VALUE], 
					$paramInfo[connector::CALL_PARAM_SIZE], 
					$paramInfo[connector::CALL_PARAM_TYPE] );
				
			}
		}
		
		$response = @oci_execute($stm);
 		$output = array();
		if(!$response){
			$errorObject = oci_error($stm);
			$this->setErrorObject($errorObject, $sql, ALPHA_EVENT_CONNECTOR_ON_ERROR);
		}else{
			if(is_array($params)){
				# We have to 	*/create the option array
				if(!is_array($options)) $options = array();
				
				# If the RESULTSET option is not defined then we should create it as empty value
				if(!isset($options[connector::CALL_OPTIONS_RESULTSET])) $options[connector::CALL_OPTIONS_RESULTSET] = ''; 
				
				foreach($params as $paramInfo){
					
					if(isset($paramInfo[connector::CALL_PARAM_OUTPUT]) && $paramInfo[connector::CALL_PARAM_OUTPUT]){ # Is an output parameter?
						$output[$paramInfo[connector::CALL_PARAM_NAME]] = $paramInfo[connector::CALL_PARAM_VALUE];
						
						if($paramInfo[connector::CALL_PARAM_NAME] == $options[connector::CALL_OPTIONS_RESULTSET]){ # Should we make the cursor as the current resultset?
							oci_execute($paramInfo[connector::CALL_PARAM_VALUE]);
							$this->result = $paramInfo[connector::CALL_PARAM_VALUE];
						}
					}
				}
			}
		}
		
		return $output;
	}
	
	function query($sql, $empty = false, $returnId=true, $unwatched = false){
		ClassFactory::get('Events')->raise(ALPHA_EVENT_CONNECTOR_ON_QUERY, $sql, $empty, $returnId, $unwatched);
		if(!$unwatched){
			$this->lastQuery=$sql;
		}
		ClassFactory::get('Debug')->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		ClassFactory::get('Debug')->writeFunctionArguments(func_get_args());
		
		if($this->isConnected()){
			
			if($empty){
				ClassFactory::get('Events')->raise(ALPHA_EVENT_CONNECTOR_ON_EMPTY_QUERY, $sql, $empty, $returnId, $unwatched);
				ClassFactory::get('Debug')->write('executing empty query');
				$isInsert = false;
				if($returnId && preg_match("#^(\s*insert\s+into\s+([^\s]+)\s+)(.*)$#im", $sql, $matches )){
					$isInsert = true;
					/*
					 * If you are using an insert command like "insert into table values(a,b,c);"
					 * the returning id into :ID causes an sql error because the field list
					 * is not specified.
					 * You should use something like "insert into table (id, field1, field2) values(a,b,c);"
					 * to avoid the error.
					 * 
					 */
					// we have to detect the table name into the insert sql query
					$theTableName = $matches[2];
					$sqlLeftPart = $matches[1];
					$sqlRightPart = $matches[3];
					$fields = $this->describeTable($theTableName);
					$sqlColumns = '';
					// As default behavior the first field in the table will be
					// identified as the autoincrement field
					$idField = $fields[0][DB_DESCRIPTOR_COLUMN_FIELD];
					$needColumnsInInsert = (preg_match("#^values#i", $sqlRightPart));
					// Building the field columns insert section
					foreach($fields as $field){
						if($sqlColumns!='') $sqlColumns.=',';
						if($needColumnsInInsert) $sqlColumns .= $field[DB_DESCRIPTOR_COLUMN_FIELD];
						if($field[DB_DESCRIPTOR_COLUMN_EXTRA_INFO] == DB_DESCRIPTOR_COLUMN_EXTRA_INFO_AUTOINCREMENT){
							// If we found an autoincrement field we use it!
							$idField =$field[DB_DESCRIPTOR_COLUMN_FIELD];
						}
					}
					
					
					if($needColumnsInInsert) $sql = "$sqlLeftPart ($sqlColumns) $sqlRightPart";

					$sql .= (" returning $idField into :ID");
					
					ClassFactory::get('Events')->raise(ALPHA_EVENT_CONNECTOR_ON_INSERT, $sql);
				}
				$sql = utf8_encode($sql);
				
				$stm = oci_parse($this->conn, $sql);
				if($isInsert) OCIBindByName($stm,":ID",$id,32);	
				# Managed OCI Error in case of execution fail.
				ClassFactory::get('Debug')->write('Executing query' . $sql,DEBUG_REPORT_OTHER_DATA );
			#	echo("<strong>Executing:</strong><pre>$sql</pre>");
				$response = @oci_execute($stm);
				
				if(!$response){
					$errorObject = oci_error($stm);
					$this->setErrorObject($errorObject, $sql, ALPHA_EVENT_CONNECTOR_ON_ERROR);
				}
				if($isInsert) $this->lastId = $id;
				
			}else{
				ClassFactory::get('Events')->raise(ALPHA_EVENT_CONNECTOR_ON_RESULTSET_QUERY, $sql, $empty, $returnId, $unwatched);
				ClassFactory::get('Debug')->write('executing query with result');
				
				$p = ClassFactory::get('Paging',false);
				if($p!=null && $this->pagingIsEnabled) {
				
					if(strpos('ROWNUM',strtoupper($sql))!==false){
						
						ClassFactory::get('Debug')->write('paging cannot be enabled');
					}else{
						ClassFactory::get('Events')->raise(ALPHA_EVENT_CONNECTOR_ON_PAGINATION, $p);
						$p->updateCount($sql);
						$limit = $p->buildLimitClause();
						$sql = 'select * from (select rownum as limitCountColumn, x.* from(' . $sql . ') x where ' . $limit;
						
					}
				}
				
				$stm= oci_parse($this->conn, $sql);
				
				# Managed OCI Error in case of execution fail.
				$response = @oci_execute($stm);
				
				if(!$response){
					$errorObject = oci_error($stm);
					$this->setErrorObject($errorObject, $sql, ALPHA_EVENT_CONNECTOR_ON_ERROR);
				}
				
				if(!$unwatched){
				
					if($this->doCommit()) oci_commit($this->conn);
					$this->result = $stm; 
					#$dbg->write('result is: ' . $this->result);
				}
				
				
			}
		}else{
			
			$this->lastError = DB_ERROR_NO_CONNECTION;
		}
		if($unwatched){
			return $stm;
		}else{
			$err = oci_error($stm);
			$this->lastErrorObject = (($err==null)?'':$err);
			ClassFactory::get('Debug')->write('Exiting ' . __FUNCTION__ , DEBUG_REPORT_FUNCTION_EXIT );
		}
	}
	
	function allResults($stm = null){
		
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		if(is_null($stm)) $stm = $this->result;
		
		if($stm){

			$return = array();
			while($rs = oci_fetch_assoc($stm)){
				if(function_exists('formatRecordset')){
					$return[] = formatRecordset($rs, $this->lastQuery);
				}else{
					$return[] = $rs;
				}
			}
			
			if(count($return)==0) $return = null;
		}else{
			$this->lastError = DB_ERROR_NO_RESOURCE;
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
	
	function moveNext($resource = null){
	
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		$res = $resource;
		if($res == null) $res = $this->result;
		if($res){
			#echo('<p>eccomi <strong>' . $this->lastQuery .'</strong></p>');
			$rs = oci_fetch_assoc($res);
			
			if($rs!=null){
				if(function_exists('formatRecordset') && $resource==null){
					
					$rs = formatRecordset($rs, $this->lastQuery);
				}
			}else{
			#	echo('No data found');
				
			}
		}else{
			$this->lastError = DB_ERROR_NO_RESOURCE;
			$rs = null;
		}
		return $rs;
		
	}
	
	function getCount($stm = null){
		
		if(is_null($stm)) $stm = $this->result;
		
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		oci_fetch_all($stm, $array);
		$result = oci_num_rows($stm);
		$dbg->write('Result is: ' . $result, DEBUG_REPORT_OTHER_DATA);
		return $result;
	}
	
	public function release($resource= null){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		if ($resource==null) $resource = $this->result;
		if ($resource==null) return;
		
		oci_free_statement($resource);
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
		
		// Managin LOB and other oracle objects
		if($rs!=null){
			foreach($rs as $key => $value){
				if (is_object($value)) { // protect against a NULL LOB
				    $data = $value->load();
				    $value->free();
				    $rs[$key] = $data;
				}
			}
		}
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
			return ($this->lastId);
		}else{
			$this->lastError = DB_ERROR_NO_CONNECTION;
		}
	}
	public function describeTable($tableName){
		
		if(isset($this->descriptors[$tableName])) return $this->descriptors[$tableName]; 
		/*
		 * Improvements of the following row of code:
		 * $sql = "select * from user_tab_columns where table_name ='$tableName'";
		 * 
		 * If the table is an alias of another table the above query does not return any result.
		 * Instead if I made a query against the all_tab_columns it will give the same results 
		 * querying any object regardless its type (TABLE, VIEW, ALIAS, ... ).
		 */
		$sql = "SELECT 	COLUMN_NAME, DATA_TYPE, DATA_LENGTH, DATA_DEFAULT,
		         		DATA_PRECISION, DATA_SCALE, NULLABLE, TABLE_NAME
		    	FROM 	all_tab_columns
		   		WHERE 	TABLE_NAME = '$tableName' ORDER BY COLUMN_ID";
		
		$paginationStatus = $this->pagingIsEnabled;
		$this->disablePagination();
		
		$stm = $this->query($sql,false, false, true);
		$this->pagingIsEnabled = $paginationStatus; 
		/*
		$c = new ociConnector($this->connector_host,$this->connector_instance, $this->connector_username, $this->connector_password);
		$c->query($sql);
		*/
		$fields = array();
		while($rs = $this->moveNext($stm)){
			$field = array();
			if(isset($rs['COLUMN_NAME'])){
				$field[DB_DESCRIPTOR_COLUMN_FIELD] = $rs['COLUMN_NAME'];
				
				// issue #38: Oci8c default value of a field keep a single quote if it is a char
				if($rs['DATA_DEFAULT'] != null && $rs['DATA_DEFAULT'] != ''){
					$dv = $rs['DATA_DEFAULT'];
					// If the default value starts with an "'" we need to remove the first and the last quote
					if($dv{0} == "'"){
						$dv = substr($dv,1, strlen($dv)-3 );
						// In Oracle the quotes is stored and retrieved as two single quote character so we need to replace it with the \' char.
						$dv = str_replace("''", "'", $dv);
						
					} 
					$field[DB_DESCRIPTOR_COLUMN_DEFAULT_VALUE] = $dv;
					
				}else{
					$field[DB_DESCRIPTOR_COLUMN_DEFAULT_VALUE] = null;
				}
				# ***** PULISCO IL VALORE PREDEFINITO ****
				/*
				if( substr($field[DB_DESCRIPTOR_COLUMN_DEFAULT_VALUE],0,1)=="'"){
					$field[DB_DESCRIPTOR_COLUMN_DEFAULT_VALUE] = substr($field[DB_DESCRIPTOR_COLUMN_DEFAULT_VALUE],1, strlen($field[DB_DESCRIPTOR_COLUMN_DEFAULT_VALUE])-2);
				}
				*/
				# ***** NECESSARIO ****
				# todo: devo vedere come un campo si identifica come primary key!!! 
				 $field[DB_DESCRIPTOR_COLUMN_KEY] = '';
				# **********************
				# Mi serve solo per segnare l'auto increment (poi magari posso impostarci 
				# in futuro altre cose se le gestirò sul binder
				$field[DB_DESCRIPTOR_COLUMN_EXTRA_INFO] = $this->hasSequence($rs['TABLE_NAME'], $rs['COLUMN_NAME'])?DB_DESCRIPTOR_COLUMN_EXTRA_INFO_AUTOINCREMENT:'';
				if($field[DB_DESCRIPTOR_COLUMN_EXTRA_INFO]!='')	$field[DB_DESCRIPTOR_COLUMN_KEY] = DB_DESCRIPTOR_COLUMN_KEY_PRIMARY;
				$fields[] = $field; 
			}
		}
		
		// verifica se c'è una sequence su un campo
		$this->descriptors[$tableName] = $fields;
		return $fields;
	}
	
	public function processDataValue($value, $key = '', $table = ''){
		#echo($value);
		if(gettype($value)!='string'){
			$before = '';
			$after ='';
			#echo($value . ' - ' . gettype($value) .'<br />');
			#exit();
		}else{
			
			$value = stripslashes($value);
			$value = str_replace('\'','\'\'',$value);
			# Modifica di Diego La Monica
			# del 2010-04-15
			# Motivo: Per poter inviare il simbolo dell'euro correttamente a Oracle devo 
			# convertire il simbolo euro (€) nel controvalore unicode (hex: 20AC) e utilizzare
			# la funzione oracle unistr per la conversione di codici unicode in utf8 
			$value = str_replace('€', "'||unistr('\\20AC')||'", $value);
			#$value = str_replace('€', "'||unistr('\\20AC')||'", $value);
			# Fine Modifica
			
			# Modifica di Diego La Monica
			# del 2010-04-20
			# Stessa questione di cui sopra ma per le codifiche Javascript %u0000 > %uFFFF
			$value = preg_replace('/\%u([a-f0-9]{4})/i', '\'||unistr(\'\\\\\1\')||\'', $value);
			# Fine Modifica
			$before = '\'';
			$after = '\'';
			if($key!=''){
				
				if(preg_match('/(^|[^a-z])data([^a-z]|$)/i',$key)){
					if($value!='') {
						if(strpos($value,' ')){
							$value = 'TO_DATE(\''.$value.'\',\'dd/mm/yyyy HH24:MI:SS\')';
							
						}else
							$value = 'TO_DATE(\''.$value.'\',\'dd/mm/yyyy\')';
						$before = '';
						$after = '';
					}
				}
				if(function_exists('customProcessDataValue')){
					$ret = customProcessDataValue($value, $key, $table);
					if($ret != null) return $ret; 
				}
			}
		}
		
		return 
			array(
				'before'=>$before,
				'after'=>$after,
			 	//'value'=>$value
			 	'value'=>($table!='log')?utf8_decode($value):$value
			);
		
	}
	
	/**
	 * Check if a specific field of a given table has a sequence
	 * @param string $tableName
	 * @param string $fieldName
	 */
	private function hasSequence($tableName, $fieldName){
		if(isset($this->seequences[$tableName.'@'.$fieldName])) return $this->seequences[$tableName.'@'.$fieldName];
		$sql = "SELECT TABLE_NAME,TRIGGER_BODY FROM user_triggers where table_name='$tableName' and STATUS='ENABLED' and TRIGGERING_EVENT like '%INSERT%'";
		$paginationStatus = $this->pagingIsEnabled;
		$this->disablePagination();
		
		$stm = $this->query($sql,false, false, true);
		
		$this->pagingIsEnabled = $paginationStatus; 

		$return = false;
		while($rs = $this->moveNext($stm)){
			$body = $rs['TRIGGER_BODY'];
			
			$body = strtolower($body);
			$fieldName = strtolower($fieldName);
			if(preg_match('/\.nextval\s+into\s+:new.'. $fieldName.'\s+/is', $body)){
				
				$return = true;
				break;
			}
		}
		$this->release($stm);
		$this->seequences[$tableName.'@'.$fieldName] = $return;
		return $return;
	}
}

?>