<?php
/**
 * @name Binder
 * @version 1.0
 * @package ALPHA
 * @author Diego La Monica
 */

/**
 *
 */
require_once 'interfaces/iBinder.php';
require_once 'Debug.php';
/**
 * @desc Classe che si occupa del bind con la sorgente dati.
 * @desc Nello specifico si occupa di interrogare e scrivere su una base dati mySQL
 * @author Diego La Monica
 *
 */
class Binder extends Debugger implements iBinder {
	/**
	 * Nome della tabella da cui leggere/scrivere
	 * @var string
	 */
	private $tableName = '';
	/**
	 * Array associativo che mette in relazione ciascun campo del database con una serie di possibili valori
	 * @var array
	 */
	private $bindings;
	/**
	 * Array associativo con l'elenco di tutti i valori predefiniti
	 * @var array
	 */
	private $defaultValues;
	/**
	 * Identifica l'elenco dei campi che costituiscono la chiave primaria di accesso
	 * al record
	 * @var array
	 */
	private $primaryKey;
	/**
	 * Condizione di filtro generata per l'accesso al record specifico
	 * @var string
	 */
	private $whereCondition = '';
	/**
	 * Se esiste un campo con autoincremento viene identificao e riportato in questa variabile
	 * @var string
	 */
	private $autoIncrementField = '';
	/**
	 * Se l'accesso alle informazioni è legata a una query specifica questo valore è popolato
	 * @var string
	 */
	private $_querySource = '';
	/**
	 * è l'elenco di tutti i campi disponibili nella tabella interrogata
	 * @var array
	 */
	private $tableFields = null;
	/**
	 * è un array associativo che mantiene informazioni sui valori immagazzinati nel record corrente.
	 * @var array
	 */
	public  $currentRecord = null;

	
	private $connector;
	
	function __construct($connectorObjectName = 'connector'){
		$this->connector = $connectorObjectName;
	}
	
	/**
	 * Imposta la sorgente dati di scrittura/lettura alla tabella indicata.
	 * Quando viene settato questo valore, viene interrogata la base dati per
	 * ottenere informazioni dettagliate su ciascuno dei campi del DB
	 * @see classes/interfaces/iBinder#setSource()
	 */
	function setSource($tableName){
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		$this->tableName = $tableName;
		$c = ClassFactory::get($this->connector);

		$fields = $c->describeTable($tableName);
		$keyFields = array();
		$firstField = '';
		$this->tableFields = null;
		
		foreach($fields as $index => $field){
			$fieldName =  $field[DB_DESCRIPTOR_COLUMN_FIELD];
			if($firstField=='') $firstField = $fieldName;
			if($field[DB_DESCRIPTOR_COLUMN_KEY]==DB_DESCRIPTOR_COLUMN_KEY_PRIMARY) $keyFields[] = $fieldName;
			if($field[DB_DESCRIPTOR_COLUMN_DEFAULT_VALUE]!=null) $this->setDefaultValue($fieldName, $field[DB_DESCRIPTOR_COLUMN_DEFAULT_VALUE]);
			if($field[DB_DESCRIPTOR_COLUMN_EXTRA_INFO] == DB_DESCRIPTOR_COLUMN_EXTRA_INFO_AUTOINCREMENT) $this->autoIncrementField = $fieldName;
			$this->tableFields[$fieldName] = '';
		}
		if(count($keyFields)==0) $keyFields[] = $firstField;
		$this->primaryKey = $keyFields;
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}

	public function querySource($query){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		$this->_querySource=$query;
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}

	/**
	 * associa il campo del db "field" con una delle chiavi presenti nella struttura ("GET", "POST", "SESSION", "COOKIE" ...) del linguaggio
	 *
	 * @param string $field
	 * @param string $structure
	 * @param string $key
	 */
	public function bind($field, $structure, $key){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		if(!isset($this->bindings[$field]) || $this->bindings[$field]==null) $this->bindings[$field] = array();
		$i = count($this->bindings[$field]);
		$this->bindings[$field][$i] = array('structure'=>$structure, 'key' => $key);
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}
	/**
	 *
	 * @param $structure
	 * @param $array
	 */
	public function bindFromStructure($structure, $array){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		foreach($array as $key => $value){
			if(isset($this->tableFields[$key])){
				$this->bind($key, $structure, $key);
			}
		}
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}
	public function setDefaultValue($field, $value){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		if(!isset($this->bindings[$field]) || $this->bindings[$field]==null) $this->bindings[$field]=array('UNSET');

		$this->defaultValues[$field] = addslashes($value);
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}
	public function setValue($field, $value){
		$this->bindings[$field][] = array('VALUE'=> $value);
	}
	
	public function unbindAll(){
		unset($this->bindings);
		unset($this->currentRecord);
	}

	/**
	 * Rimuove l'associazione da un campo.
	 *
	 * @param unknown_type $field
	 * @param unknown_type $structure
	 * @param unknown_type $key
	 */
	public function unbind($field, $structure = '', $key = ''){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		if($structure==''){
			unset($this->bindings[$field]);
		}else{
			$items = $this->bindings[$field];
			for($i=0; $i<count($items); $i++){
				if(
				($items[$i]['structure']==$structure || $structure=='') &&
				($items[$i]['key'] == $key || $key == '') ){
					unset($items[$i]);
						
				}
			}
			$this->bindings[$field] = $items;
		}
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}
	/**
	 * Cerca in base ai dati collegati (tramite {@link bind} e {@link bindFromStructure})
	 * uno o più record nella tabella associata (tramite {@link setSource}) restituendo
	 * in output o un array di records o un singolo record
	 * @param boolean $usePrimaryKey Indica se la ricerca deve essere effettuata solo sulla chiave primaria, se FALSE, la ricerca prenderà tutti i dati disponibili e formulerà una query opportunamente costruita.
	 * @param boolean $multiple se impostata a true, viene restituito un elenco di records corrispondenti al criterio di ricerca specificato.
	 * @return mixed
	 * @see classes/interfaces/iBinder#find()
	 */
	public function find($usePrimaryKey = true, $multiple = false){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		$filter = null;
		if($usePrimaryKey){
			for($i = 0; $i<count($this->primaryKey); $i++){
				$key = $this->primaryKey[$i];
				$value = $this->getBindedValue($key);
				if($value==null) $value ='';
				$filter[$key] = $value;
			}
		}else{
			if($this->bindings!=null){

				foreach($this->bindings as $key => $value){
					$value = $this->getBindedValue($key, false);
					if($value!=null){
						$filter[$key] = $value;
					}
				}
			}
		}
		
		if($this->_querySource!=''){
			$tn = '(' . $this->_querySource . ') qs';
		}else{
			$tn = $this->tableName;
		}

		$sql = 'select * from ' . $tn;

		$whereCond = '';
		# Modifica di Diego del 2010-04-06
		$c = ClassFactory::get($this->connector);
		# Fine Modifica
		if($filter!=null){
			foreach($filter as $key => $value){
				if($whereCond!='') $whereCond .= ' and ';
				# Modifica di Diego del 2010-04-06
				# $whereCond .= $key . ' =  \'' . $value . '\'';
				
				$processed = $c->processDataValue($value, $key, $this->tableName);
				$whereCond .= $key . ' =  ' . $processed['before'].$processed['value'].$processed['after'] ;
				# Fine modifica
				 
			}
			$this->whereCondition = $whereCond;
		}
		# Modifica di Diego del 2010-04-06
		#$c = ClassFactory::get($this->connector);
		# Fine Modifica
		if($whereCond!=''){
			$sql .= ' where ' . $whereCond;
		}
		
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		if($multiple){
			$c->query($sql);
			$output = $c->allResults();
			$c->release();
		}else{
			
			$this->currentRecord = $c->getFirstRecord($sql);
			$output = $this->currentRecord;
		}
		return $output;
	}
	public function findAll($usePrimaryKey= true){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		$result = $this->find($usePrimaryKey, true);

		$c = ClassFactory::get($this->connector);
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		return $result;
	}


	public function getBindedValue($field, $getDefaultIfNull=true){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		if(!isset($this->bindings[$field])) 
			$items = null;
		else
			$items = $this->bindings[$field];
		
		if($items==null){
			$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
			if($getDefaultIfNull){
				if(isset($this->defaultValues[$field])) return $this->defaultValues[$field];
				return null;
			}else{
				return null;
			}
		}
		
		for($i=0; $i<count($items); $i++){
			$item = $items[$i];
			
			if($item!='UNSET'){
				#($item['VALUE'])
				if(array_key_exists('VALUE', $item)){
					return $item['VALUE'];
				}else{
					
					$structure = "\$structure = \$_" . $item['structure'] . ';';
					
					$key = $item['key'];
	
					@eval($structure);
					
					if(isset($structure[$key]) ) return $structure[$key];
				}
			}else{
				if(count($items)-1==$i && isset($this->currentRecord) && isset($this->currentRecord[$field])) return $this->currentRecord[$field];   
				
			}
		}
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		if($getDefaultIfNull)
			return $this->defaultValues[$field];
		else
			return null;
	}

	public function save($forceNew=true){
		
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __CLASS__ . '>' , __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		if(!$forceNew) $this->find();
		if($forceNew) $this->currentRecord = null;
		$newRs = $this->getDifferences($this->currentRecord==null);
		if($this->currentRecord==null){
			// Sono in fase di insert
			$sql = 'insert into ' . SQL_TABLE_PREFIX . $this->tableName . SQL_TABLE_POSTFIX;
			$keys = '';
			$values = '';
			$c = ClassFactory::get($this->connector);
			foreach($newRs as $key => $value){
				if($keys!='') 	$keys	.=',';
				if($values!='') $values	.=',';
				/**
				 * Bugfix: during update query building SQL_FIELD_PREFIX e SQL_FIELD_SUFFIX were not considered.
				 */
				$keys .=  SQL_FIELD_PREFIX .  $key . SQL_FIELD_POSTFIX;
				
				$value = $c->processDataValue($value, $key, $this->tableName);
				if($value['before'] . $value['value'] .$value['after']==''){
					$values .= 'null ';
				}else{
				
					$values .= $value['before'] . $value['value'] .$value['after'] . ' ';
					
					
				}
			}
			$sql .= '(' . $keys .') VALUES (' . $values . ')';
			$c->query($sql, true);
			$err = $c->getLastErrorObject();
			if($err==null){
				if($this->autoIncrementField!=''){
					$this->unbind($this->autoIncrementField);
					$this->setDefaultValue($this->autoIncrementField, $c->getId() );
				}
				$l = ClassFactory::get('Logger');
				
				$l->write('Aggiunto nuovo record', $this->tableName, $c->getId());
			}else{
				$e = ClassFactory::get('ErrorManager');
				$e->setText('<h2>'. $err['message'] .'</h2><p>' . $err['details'] .'</p>', false);
			}
		}elseif($newRs!=null){
			// Sono in fase di update
			
			$l = ClassFactory::get('Logger');
			$sql = 'update ' . SQL_TABLE_PREFIX . $this->tableName . SQL_TABLE_POSTFIX . ' set ';
			$values = '';
			$idRecord =	$this->getBindedValue($this->autoIncrementField, true);
			$c = ClassFactory::get($this->connector);
			foreach($newRs as $key => $value){
				
				if($values!='') $values.=',';
				$value = $c->processDataValue($value, $key, $this->tableName);
				
				$values .= SQL_FIELD_PREFIX . $key .SQL_FIELD_POSTFIX.'=';
				if($value['before'] . $value['value'] .$value['after']==''){
					$values .= 'null ';
				}else{
					$values .= $value['before'] . $value['value'] .$value['after'] . ' ';
				}
				$l->write('Modificato contenuto', $this->tableName, $idRecord, $key,$this->currentRecord[$key], $value['value']);
			}
			if($values!=''){
				
				$sql .=$values;
				$sql .='where ' .$this->whereCondition;
		
				$c->query($sql, true);
				
				if($c->lastErrorObject==''){
					$l->write('Record salvato', $this->tableName, $idRecord);
				}else{
					$e = ClassFactory::get('ErrorManager');
					$e->setText(print_r($c->lastErrorObject,true), false);
				}
			}
		}
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);

	}

	public function saveFromObject($object, $table, $updateCondition = ''){
		$sql = '';
		if($updateCondition!=''){
				
			foreach($object as $key =>$value){
				if($sql!='') $sql .= ', ';
				# Issue 39 resolution: only the last field from the class were saved.
				# Damned not typed `.`!!!
				$sql .= SQL_FIELD_PREFIX .$key . SQL_FIELD_POSTFIX. ' = \'' . addslashes($value) . '\'';
				
			}
			$sql = 'update ' . SQL_TABLE_PREFIX . $table . SQL_TABLE_POSTFIX. ' set ' . $sql . ' where '  . $updateCondition;
		}else{
			$fields = '';
			$values = '';
			foreach($object as $key =>$value){
				if($fields!='') $fields .= ', ';
				$fields .= SQL_FIELD_PREFIX.$key . SQL_FIELD_POSTFIX;

				if($values != '') $values .= ', ';
				$values .= '\''.addslashes($value).'\'';
			}
			$sql = 'insert into ' . SQL_TABLE_PREFIX. $table . SQL_TABLE_POSTFIX.'(' . $fields .') VALUES (' . $values .')';
		}
		$c = ClassFactory::get($this->connector, true, 'tmp_connector');
		$c->query($sql, true);
		if($updateCondition==''){
			$updateCondition = SQL_FIELD_PREFIX .'id'.SQL_FIELD_POSTFIX.'=' . $c->getId() . '';
		}
		$sql = 'select * from ' . SQL_TABLE_PREFIX.$table . SQL_TABLE_POSTFIX.' where ' . $updateCondition;

		$rs = $c->getFirstRecord($sql);
		foreach($rs as $key => $value){
			if(is_array($object)){
				$object[$key] = $value;
			}else{
				$object->$key = $value;
			}
		}

		return $object;
	}

	private function getDifferences($getDefaultIfNull = true){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		foreach($this->bindings as $key => $value){
			$value = $this->getBindedValue($key,$getDefaultIfNull);
			if(str_replace("''","'",$this->currentRecord[$key])!=str_replace("''","'",stripslashes( $value))){
				$result[$key] = $value;
			}
		}
		if(!isset($result)) $result = null;
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		return $result;
	}

}
?>