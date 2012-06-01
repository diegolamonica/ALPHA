<?php
/**
 * @package ALPHA
 * @subpackage AJAX
 * @name exec
 * @version 2.0
 * @author Diego La Monica
 * 
 * @desc Executes a query on the database. The only parameter that this script requires is the "q" argument given by querystring.  
 * @desc Due to a security issue, this script cannot be invoked directly.
 */


# If REDIRECT_URL is not defined maybe there was some srange behavior (injection?) and I must block it.
!isset($_SERVER['REDIRECT_URL']) && $_SERVER['REDIRECT_URL'] =__FILE__;

$thisFileName = basename(__FILE__);
$thisDirectory = basename(dirname(__FILE__));

$thisUrlName = basename($_SERVER['REDIRECT_URL']);
$thisUrlPath = basename(dirname($_SERVER['REDIRECT_URL']));

# Avoid users to call directly this script
if($thisDirectory==$thisUrlPath && $thisFileName == $thisUrlName){
	core::send404();
	exit();
}

require_once(CORE_ROOT. "/classes/ClassFactory.php");

!defined('AJAX_EXEC_UNAUTHENTICATED_USER_MESSAGE')	&& define('AJAX_EXEC_UNAUTHENTICATED_USER_MESSAGE', "utente non autenticato");
!defined('AJAX_EXEC_INVALID_CALL_MESSAGE')			&& define('AJAX_EXEC_INVALID_CALL_MESSAGE',"chiamata non corretta");
!defined('AJAX_EXEC_OPERATION_DONE')				&& define('AJAX_EXEC_OPERATION_DONE', "informazione rimossa" );
 
function ajaxExec(){
	header('Content Type: text/javascript');
	$a = ClassFactory::get('Authentication');
	
	$jsonObject = new stdClass();
	$jsonObject->error = true;
	if(!$a->isAuthenticated()){
		
		$jsonObject->message = AJAX_EXEC_UNAUTHENTICATED_USER_MESSAGE;
	}else{
		extract($_POST);
		
		if(!isset($q) || $q==''){
			$jsonObject->message = AJAX_EXEC_INVALID_CALL_MESSAGE;
		}else{
			
			$c = ClassFactory::get('connector');
			
			$sql = stripslashes($q);
			
			$c->query($sql, true);
			$jsonObject->message = AJAX_EXEC_OPERATION_DONE;
			$jsonObject->error = false;
			
		}
	}
	$j = ClassFactory::get('Json');
	echo $j->fromObject($jsonObject);
}

ajaxExec();
?>