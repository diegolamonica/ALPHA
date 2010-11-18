<?php
/**
 * @package ALPHA
 * @subpackage AJAX
 * @name exec
 * @version 1.0
 * @author Diego La Monica
 * 
 * @desc provvede all'esecuzione di un record passando le informazioni di accesso tramite <b>$_GET</b>.
 * I parametri da usare in get sono:
 * - <b>t</b>: la tabella da cui cancellare il record
 * - <b>f</b>: nome del campo chiave
 * - <b>k</b>: valore della chiave per l'accessp al record da rimuovere
 */

/**
 * 
 */
require_once(CORE_ROOT. "/classes/ClassFactory.php");
function ajaxExec(){
	header('Content Type: text/javascript');
	$a = ClassFactory::get('Authentication');
	
	if(!$a->isAuthenticated()){
		echo('{error:true, message:"utente non autenticato"}');
	}else{
		extract($_POST);
		
		if(!isset($q) || $q==''){
			echo('{error:true, message:"chiamata non corretta"}');
			exit();
		}
		
		$c = ClassFactory::get('connector');
		
		$sql = stripslashes($q);
		
		$c->query($sql, true);
		echo('({error:false, message:"informazione rimossa"})');
	}
}

ajaxExec();
?>