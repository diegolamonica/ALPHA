<?php
/**
 * @package ALPHA
 * @subpackage AJAX
 * @name get
 * @version 1.0
 * @author Diego La Monica
 * 
 * @desc provvede all'interrogazione di una tabella e restituisce un risultato JSON dell'elenco di record corrispondenti al filtro indicato.
 * @desc I dati per l'interrogazione vengono passati tramite $_GET e sono:
 * I parametri da usare in get sono:
 * - <b>f</b>: elenco dei campi da riportare separati da virgola
 * - <b>t</b>: la tabella da cui estrarre i records
 * - <b>w</b>: campo sul quale effettuare la ricerca
 * - <b>q</b>: valore da ricercare
 * - <b>l</b>: numero massimo di elementi
 * - <b>cmp</b>: simbolo di confronto se non definito è "like"
 * - <b>a</b>: postfisso alla stringa di ricerca se non definito è "%"
 * - <b>b</b>: prefisso alla stringa di ricerca se non definito è vuoto
 * - <b>xw</b>: indica con "y" che il campo di confronto è una formula. Se omesso o con valore differente da y indica che il campo di confronto è un campo della tabella. 
 * - <b>cw</b>: indica che la where condition è specificata per esteso nel parametro "w"
 * - <b>orderby</b>: indica il campo di ordinamento
 * @todo implementare controllo di autenticazione prima di fornire le informazioni agli utenti.
 */

/**
 * definire la costante AJAX_SQL_QUERY per fare l'override della query 
 */
#session_start();
require_once(CORE_ROOT. "/classes/ClassFactory.php");

header('Content Type: text/javascript; Encoding: utf-8');
/**
 * 
 * @return string
 */
function get(){
	extract($_GET);
	if(defined('AJAX_SQL_QUERY') ){

		$sql = AJAX_SQL_QUERY;
	}else{
		if(!isset($q)) return '';
		if(!isset($t)) return '';
		if(!isset($f)) return '';
		if(!isset($w)) return '';
		if(!isset($cmp)) $cmp = 'like';
		if(!isset($a)) $a = '%';
		if(!isset($b)) $b = '';
		if(!isset($distinct)) $distinct = 'distinct';
		
		switch(APPLICATION_CONNECTOR_MODULE){
			case APPLICATION_CONNECTOR_MODULE_MYSQL:
				#$q = addslashes($q);
				break;
			case APPLICATION_CONNECTOR_MODULE_ORACLE:
				$q = str_replace("'","''",$q); 
				break;
		
		}
		
		$f = stripslashes($f);
		$sql = "select $distinct $f from ".SQL_TABLE_PREFIX.$t.SQL_TABLE_POSTFIX." where ";
		if(isset($xw) && $xw=='y'){
			$w = stripslashes($w);
			if(isset($cw) && $cw=='y'){
				$sql .= $w;
			}else{
				$sql .= "$w $cmp '$b$q$a'";
			}
		}else{
			$sql .= SQL_FIELD_PREFIX.$w.SQL_FIELD_POSTFIX." $cmp '$b$q$a'";
		}
		if(defined('GET_EXTRA_FILTER')) $sql .= ' and (' . GET_EXTRA_FILTER.')';
		if(isset($orderby)){
			$sql .= ' order by ' . $orderby;
		}
	}
	
	
	# MySQL Connetor	
	# if(isset($l) ) $sql .= ' limit ' . $l;
	# Oracle Connector
	if(isset($l) )  {
		switch(APPLICATION_CONNECTOR_MODULE){
			case APPLICATION_CONNECTOR_MODULE_MYSQL:
				$sql .= ' limit ' . $l;
				break;
			case APPLICATION_CONNECTOR_MODULE_ORACLE:
				$sql .= 'and rownum <' . ($l+1); 
				break;
		
		}
	}
	$c = ClassFactory::get('connector');
	$c->query($sql);
	
	$results = $c->allResults();
	
	if(count($results)>0){
		foreach($results as $key => $value){
			$results[$key] = array_values($value);
		}
	}
	#print_r($results);
	$json = ClassFactory::get('Json');
	#$result = 	json_encode($results);
	
	$result = $json->fromObject($results);
	if($result == '') $result = '[]';
	return $result;
}

echo get();

?>