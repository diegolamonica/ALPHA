<?php
/**
 * @package ALPHA
 * @subpackage AJAX
 * @name post
 * @version 1.0
 * @author Diego La Monica
 * 
 * @desc provvede al salvataggio di informazioni fornite tramite POST e restituisce un oggetto JSON.
 * @desc La tabella di destinazione sarà ottenuta tramite post nel parametro <b>_table</b>.
 * @desc Esistono inoltre una serie di parole riserrvate: _table, _key_field, _skip_key.
 * @todo implementare controllo di autenticazione prima di salvare le informazioni.
 */

/**
 * 
 */
require_once(CORE_ROOT. "/classes/ClassFactory.php");
!defined('AJAX_POST_AUTOCALL') 		&& define('AJAX_POST_AUTOCALL' ,	 true);
!defined('AJAX_POST_RETURN_OBJECT') && define('AJAX_POST_RETURN_OBJECT', false);

/**
 * @return string un buffer in formato JSON.
 */

function post_toJSONObject($rs){
	$buffer = '';
	foreach($rs as $key => $value){
		if($buffer!='') $buffer .=",\n";
		
		$value = str_replace('\\', '\\\\', $value);
		$value = str_replace("'","\\'", $value);
		/*# Aggiunto da Federico il 19-04-2010
		$value = utf8_urldecode($value);
		# Fine aggiunta*/
		
		$buffer .= $key . ': \'' . $value . '\'';
	}
	return '[{' . $buffer . '}][0]';
	
}

function post(){
	$table 		= $_POST['_table'];
	
	unset($_POST['_table']);
	unset($_POST['_key_field']);
	unset($_POST['_skip_key']);
	
	foreach($_POST as $key => $value){
		
		$_POST[$key] = utf8_encode($value);
		
	}
	
	$b = ClassFactory::get('Binder');
	$b->setSource($table);
	$b->bindFromStructure('POST',$_POST);
	$b->find();
	$b->save(false);
	$b->find();
	$rs = ($b->currentRecord!=null)?$b->currentRecord:array();
	
	if(AJAX_POST_RETURN_OBJECT){
		return $rs;
	}else{
		$buffer = post_toJSONObject($rs);
		header('Content Type: text/javascript; Encoding: UTF-8');
		echo $buffer;
	}
}

		
if(AJAX_POST_AUTOCALL) post();
?>