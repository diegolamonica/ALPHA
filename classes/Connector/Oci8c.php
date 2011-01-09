<?php
if(!class_exists('ociConnector')){
	require_once  CORE_ROOT. 'classes/interfaces/iConnector.php';
	require_once CORE_ROOT. 'classes/Debug.php'; 
	!defined('DB_ERROR_OK') && define('DB_ERROR_OK', 				0);
	!defined('DB_ERROR_NO_CONNECTION') && define('DB_ERROR_NO_CONNECTION', 	1);
	!defined('DB_ERROR_NO_RESOURCE') && define('DB_ERROR_NO_RESOURCE',		2);
	
	define('DB_DESCRIPTOR_COLUMN_FIELD',					'Field');
	define('DB_DESCRIPTOR_COLUMN_KEY',						'Key');
	define('DB_DESCRIPTOR_COLUMN_KEY_PRIMARY',				'PRI');
	define('DB_DESCRIPTOR_COLUMN_DEFAULT_VALUE',			'Default');
	define('DB_DESCRIPTOR_COLUMN_EXTRA_INFO',				'Extra');
	define('DB_DESCRIPTOR_COLUMN_EXTRA_INFO_AUTOINCREMENT', 'auto_increment');
	
	class ociConnector extends Debugger implements iConnector {
		
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
		function ociConnector($connector_host ='', $connector_instance='', $connector_username='', $connector_password=''){
			$this->__construct();
			
			if($connector_host=='') 	$connector_host 	= CONNECTOR_HOST;
			if($connector_instance=='') $connector_instance = CONNECTOR_INSTANCE;
			if($connector_username=='') $connector_username = CONNECTOR_USERNAME;
			if($connector_password=='') $connector_password = CONNECTOR_PASSWORD;
		
			$this->connector_host = $connector_host;
			$this->connector_instance = $connector_instance;
			$this->connector_username = $connector_username;
			$this->connector_password = $connector_password;
			
			
			$this->connect($connector_host, $connector_instance, $connector_username, $connector_password);
		}
		
		function __destruct(){
			ocicommit($this->conn);	
			if($this->conn!=null)
				ocilogoff($this->conn);
			$dbg = ClassFactory::get('Debug');
			$dbg->write('Class ' . get_class($this) . ' destructed' , DEBUG_REPORT_CLASS_DESTRUCTION);
		}
		
		function getLimitClause($fromRow, $rowCount){
			return ' rownum<=' . ($fromRow+$rowCount) . (($fromRow!=-1)?') where limitCountColumn> ' . $fromRow:'');
			
		}
		
		function doCommit($value = null){
			if($value!=null){
				$this->doCommit = $value;
			}
			return $this->doCommit;
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
		
		function isConnected(){
			$dbg = ClassFactory::get('Debug');
			$dbg->write('Processing ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
			$dbg->writeFunctionArguments(func_get_args());
			
			return ($this->conn!=null);
		}
		function connect($host, $db, $user, $password){
			if($this->isConnected()) return;
			$dbg = ClassFactory::get('Debug');
			$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
			$dbg->writeFunctionArguments(func_get_args());
			#print_r(array($user, $password, $host, $db));
			
			exec('set NLS_LANG=ITALIAN_ITALY.AL32UTF8');
			if($host==''){
				# Ho passato solo il nome dell'istanza come identificato nel TNSNAME
				
				$conn = OCILogon( $user, $password, $db, 'AL32UTF8');
				#$conn = OCILogon( $user, $password, $db, 'WE8MSWIN1252');
			}else{
				# Ho pasato il server e il DB non identificati sul TNS Name 
				$conn = OCILogon( $user, $password, '//'.$host.'/' . $db, 'AL32UTF8');
				#$conn = OCILogon( $user, $password, '//'.$host.'/' . $db, 'WE8MSWIN1252');
			}
			$this->conn = $conn;
			#$this->query('alter session set NLS_LANG=ITALIAN_ITALY.AL32UTF8', true);
			
			$this->query('alter session set nls_language = ITALIAN',true);
			$this->query('alter session set nls_territory = ITALY',true);
			$this->query('alter session set nls_sort = binary_ci',true);
			$this->query('alter session set nls_comp = linguistic',true);
			$this->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY'", true);
		}
		
		function query($sql, $empty = false, $returnId=true, $unwatched = false){
			if(!$unwatched){
				$this->lastQuery=$sql;
			}
			$dbg = ClassFactory::get('Debug');
			$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
			$dbg->writeFunctionArguments(func_get_args());
			
			if($this->isConnected()){
				
				if($empty){
					$dbg->write('executing empty query');
					$isInsert = false;
					if(substr( trim( strtolower($sql)) , 0,6) =='insert' && $returnId){
						$isInsert = true;
						$sql .= (' returning id into :ID');
					}
					$sql = utf8_encode($sql);
					
					$stm = oci_parse($this->conn, $sql);
					if($isInsert) OCIBindByName($stm,":ID",$id,32);	
					oci_execute($stm);
					
					$dbg->write('Executing query' . $sql,DEBUG_REPORT_OTHER_DATA );
					if($isInsert) $this->lastId = $id;
					
				}else{
					$dbg->write('executing query with result');
					
					$p = ClassFactory::get('Paging',false);
					if($p!=null && $this->pagingIsEnabled) {
						if(strpos('ROWNUM',strtoupper($sql))!==false){
							
							$dbg->write('paging cannot be enabled');
						}else{
							/*
							$s = ClassFactory::get('Searcher');
							$sql = $s->getFilter($sql);
							*/
							$p->updateCount($sql);
							$limit = $p->buildLimitClause();
							$sql = 'select * from (select rownum as limitCountColumn, x.* from(' . $sql . ') x where ' . $limit;
							
						}
					}
					
					$stm= oci_parse($this->conn, $sql);
					#echo('oci8.2 ' .date('Y-m-d H:i:s') ." <strong>$sql</strong> " . '<br />');
					oci_execute($stm);
					#echo('oci8.3 ' .date('Y-m-d H:i:s') .'<br />');
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
				$dbg->write('Exiting ' . __FUNCTION__ , DEBUG_REPORT_FUNCTION_EXIT );
			}
		}
		
		function allResults(){
			$dbg = ClassFactory::get('Debug');
			$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
			$dbg->writeFunctionArguments(func_get_args());
			
			if($this->result){
				$return = array();	
				while($rs = oci_fetch_assoc($this->result)){
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
		
		function getCount(){
			$dbg = ClassFactory::get('Debug');
			$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
			$dbg->writeFunctionArguments(func_get_args());
			oci_fetch_all($this->result, $array);
			$result = oci_num_rows($this->result);
			$dbg->write('Result is: ' . $result, DEBUG_REPORT_OTHER_DATA);
			return $result;
		}
		
		function release($resource= null){
			$dbg = ClassFactory::get('Debug');
			$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
			$dbg->writeFunctionArguments(func_get_args());
			
			if ($resource==null) $resource = $this->result;
			if ($resource==null) return;
			
			oci_free_statement($resource);
		}
		
		function getFirstRecord($sql){
			$dbg = ClassFactory::get('Debug');
			$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
			$dbg->writeFunctionArguments(func_get_args());
			//$pagingIsEnabled = $this->pagingIsEnabled;
			$PaginationStatus = $this->pagingIsEnabled;
			$this->disablePagination();
			$stm = $this->query($sql, false, false, true);
			$rs = $this->moveNext($stm);
			$this->release($stm);
			$this->pagingIsEnabled = $PaginationStatus;
			if($rs!=null){
				foreach($rs as $key => $value){
					if (is_object($value)) { // protect against a NULL LOB
					    $data = $value->load();
					    $value->free();
					    $rs[$key] = $data;
					}
				}
			}
			unset($c);
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
		public function describeTable($tab){
			
			if(isset($this->descriptors[$tab])) return $this->descriptors[$tab]; 
			
			$sql = "select * from user_tab_columns where table_name ='$tab'";
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
					#if($tab=='RUOLI_APPLICAZIONI') echo($rs['DATA_DEFAULT'] .'<br/>');
					$field[DB_DESCRIPTOR_COLUMN_DEFAULT_VALUE] = $rs['DATA_DEFAULT'];
					# ***** PULISCO IL VALORE PREDEFINITO ****
					if( substr($field[DB_DESCRIPTOR_COLUMN_DEFAULT_VALUE],0,1)=="'"){
						$field[DB_DESCRIPTOR_COLUMN_DEFAULT_VALUE] = substr($field[DB_DESCRIPTOR_COLUMN_DEFAULT_VALUE],1, strlen($field[DB_DESCRIPTOR_COLUMN_DEFAULT_VALUE])-2);
					}
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
			$this->descriptors[$tab] = $fields;
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
	
}
if(!class_exists('CustomConnector')){
	class CustomConnector extends ociConnector{ 	}
}
?>