<?php
class Searcher extends Debugger{
	
	private $fieldToSearch = '';
	private $searchValue = '';
	private $placeholder = SEARCHER_DEFAULT_PLACEHOLDER;
	private $applyToTables = null;
	private $searchEnabled = false;
	function Searcher(){
		
		if(isset($_GET) && count($_GET)>0 && isset($_GET[SEARCHER_FORM_IDENTIFIER])){
			
			$this->fieldToSearch = stripslashes( $_GET[SEARCHER_FORM_FIELD_NAME] );
			$this->searchValue 	= stripslashes( $_GET[SEARCHER_FORM_FIELD_VALUE] );
			if(isset($_GET[SEARCHER_FORM_PLACEHOLDER])){
				$this->placeholder = stripslashes( $_GET[SEARCHER_FORM_PLACEHOLDER] );
			} 
			
			if(isset($_GET[SEARCHER_FORM_APPLY_TABLES]) && $_GET[SEARCHER_FORM_APPLY_TABLES]!='*'){
				$this->applyToTables = preg_split('/,/', $_GET[SEARCHER_FORM_APPLY_TABLES]);
			}
			
			$this->searchEnabled = true;
		}
		
	}	
	
	private function getTableIntoSQL($sql){
		if(preg_match('/from\s+(.+)(\s+where|\s+order by|\s+group by|\s*;|\s*$)/is',$sql, $tabella)){
			$tabella = $tabella[1];
			
			$tabella = preg_split('/,/', $tabella);
			foreach($tabella as $key => $value){
				$tabella[$key] = preg_replace('/\s.*/','',$value);
			}
		}else{
			$tabella = '';
			
		}
		return $tabella;
		
	}
	
	function getFilter($sql){
		
		if($this->searchEnabled){
			// Apply the filter only for the specified tables
			$apply = true;
			if(isset($this->applyToTables) && $this->applyToTables!=null){
				$tabelle = $this->getTableIntoSQL($sql);
				
				if(!oneOfIsIn($tabelle, $this->applyToTables)){
					$apply = false;
				} 
				
			}
			if($apply){
				
				$sqli = strtolower($sql);
				$from =  strpos('from ', $sqli);
				$where = strpos('where ', $sqli);
				$group = strpos('group by', $sqli);
				$order = strpos('order by', $sqli);
				
				switch(true){
					case($where!==false && $order!==false && $group!==false):
						$posIndex = ($order<$where)?$order:$where;
						$posIndex = ($group<$posIndex)?$group:$posIndex;
						break;
					case($where!==false ):
						$posIndex = $where;
						break;
					case($group!==false):
						$posIndex = $group;
						break;
					case($order!==false):
						$posIndex = $order;
						break;
					default:
						$posIndex = 0;
				}
				
				
				$buffer = $this->placeholder;
				
				$searchValue = str_replace('\'','\'\'', $this->searchValue);
				
				$buffer = str_replace('[0]', $this->fieldToSearch, $buffer);
				$buffer = str_replace('[1]', $searchValue, $buffer);
				
				if($posIndex == 0){
					# No filter yet applied
					$sql .= ' where ' . $buffer;
				}else{
					
					# I must replace the where clause or extend the query with where 
					if($posIndex===$where){
						# enclose the whole filter between rounded brackets and apply the new filter
						switch(true){
							
							case ( $group !== false && $order !== false):
								$posNext = ($group<$order)?$group:$order;
								break;
							case ( $group !== false ):
								$posNext = $group;
								break;
							case ( $order !== false ):
								$posNext = $order;
								break;
							default:
								$posNext = strlen($sql);
							
						}
						
						$whereFitler = substr($sql, $posIndex+5, ($posNext-$posIndex)-5);
						
						$sql = substr($sql, 0, $posIndex) . 
							'WHERE (' . $whereFitler .') and ' . $buffer .
							substr($sql, $posNext);
					}
				}
			}

			return $sql;
		}else{
			return $sql;
		}
	}
}
?>