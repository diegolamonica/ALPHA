<?php
class HelperSQL{
	
	/**
	 * 
	 * Perform a query to the database and returns result into a buffered string
	 * It can be used to export entire table data as CSV text.
	 * 
	 * @param string $query the query sql to perform on the current connection
	 * @param string $rowSeparator (optional default `,`) the separator between rows 
	 * @param string $fieldSeparator (optional default ` `) the separator between fields
	 * @param bool $onlyFirstRecord (optional default `true`) if true only the first row will be processed else all rows will be processed.
	 * @param bool $onlyFirstField (optional default `true`) if true only first column will be processed else all columns will be processed.
	 * @return string the decoded informations.
	 */
	public static function decodeItem($query, $rowSeparator = ',', $fieldSeparator = ' ', $onlyFirstRecord = true, $onlyFirstField = true){
		$c = ClassFactory::get('connector',true,'HelperSQLConnector');
		if($onlyFirstRecord){
			$rs = $c->getFirstRecord($query);
	
			$results = array($rs);
			
		}else{
			$c->disablePagination();
			$c->query($query);
			$results = $c->allResults();
		}
		$v = '';
		if($results!=null){
			for($i=0;$i<count($results); $i++){
				$rs = $results[$i];
				if($rs==null) break;
				if($v!='') $v.= $rowSeparator;
				foreach($rs as $key => $value){
					if($v!='') $v.= $fieldSeparator;
					$v .= $value;
					if($onlyFirstField) break;
				}
				if($onlyFirstRecord) break;
			}
		}
		ClassFactory::destroy('HelperSQLConnector', false);
		return $v;
	}
	
	/**
	 * 
	 * Populate a variable into Model using the result returned by the execution of SQL in the current connector
	 * @param string $variableName the variable name on the template
	 * @param string $querySQL the query sql
	 */
	public static function populateList($variableName, $querySQL ){
		$c = ClassFactory::get('connector',true,'HelperSQLConnector');
		$m = ClassFactory::get('Model');
		
		$c->query($querySQL);
		$m->setVar($variableName,$c->allResults());
		ClassFactory::destroy('HelperSQLConnector', false);
	}
	
	
	/**
	 * 
	 * Creates an sql query with `IN` contiditon using nested SELECT query.
	 * @param string $mainTable the table where to extract data
	 * @param string $idFieldName the name of the field in the $mainTable to filter
	 * @param string $relatedIdField the field of the nested query table to extract
	 * @param string $relatedIdTable the nested query table which contain the related filter value
	 * @param string $idFilterField the name of the field to filter in the nested query
	 * @param string $id the name of the field or the value (using quotes in case of string) to compare with the $idFilterField
	 * @param bool $exclude (optional default = false) if `true` the `IN` si converted `NOT IN`. 
	 * @param string $additionalFilter (optional default = '' ) an extra sql query
	 * @param string $sortField (optional default = '') the field to use to sort the $mainTable sql query
	 */
	public static function generateIdInSQL($mainTable, $idFieldName, $relatedIdField, $relatedIdTable, $idFilterField, $id, $exclude = false, $additionalFilter='', $sortField = ''){
		
		/*
		 * Defining the nested query
		 */
		$nestedQuery ="select $relatedIdField from $relatedIdTable where $idFilterField = $id";
		 
		$sql = "select * from $mainTable";
		$sql .=" where $idFieldName " .  ($exclude?'NOT':'') . " in ($nestedQuery)";
		/*
		 * Applying additional filter (if defined)
		 */
		$sql .= $additionalFilter;
		
		/*
		 * Applying sort
		 */
		$sql .= (($sortField!='')?" ORDER BY $sortField":'');
		
		return $sql;	
	}
	
	
}