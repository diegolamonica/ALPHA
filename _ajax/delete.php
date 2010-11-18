<?php
/**
 * @package ALPHA
 * @subpackage AJAX
 * @name delete
 * @version 1.0
 * @author Diego La Monica
 * 
 * @desc provvede alla cancellazione di un record passando le informazioni di accesso tramite <b>$_GET</b>.
 * I parametri da usare in get sono:
 * - <b>t</b>: la tabella da cui cancellare il record
 * - <b>f</b>: nome del campo chiave
 * - <b>k</b>: valore della chiave per l'accessp al record da rimuovere
 */

/**
 * 
 */
require_once(CORE_ROOT. "/classes/ClassFactory.php");
function delete(){
	header('Content Type: text/javascript');
	$a = ClassFactory::get('Authentication');
	
	if(!$a->isAuthenticated()){
		echo('{error:true, message:"utente non autenticato"}');
	}else{
		extract($_GET);
		
		if(!isset($t) || !isset($f) || !isset($k)){
			echo('{error:true, message:"chiamata non corretta"}');
			exit();
		}
		
		$c = ClassFactory::get('connector');
		
		$sql = 'delete from ' . SQL_TABLE_PREFIX . $t . SQL_TABLE_POSTFIX .  ' where ' . SQL_FIELD_PREFIX .  $f . SQL_FIELD_POSTFIX. '=\'' . $k . '\'';
		$c->query($sql, true);
		if(isset($ajax) && $ajax=='y'){
			echo('({error:false, message:"informazione rimossa",table:"'.$t.'",field:"'.$f.'",value:"'.$k.'"})');
		}else{
			header('Location: ' . $_SERVER['HTTP_REFERER']);
		}
	}
}

delete();
?>