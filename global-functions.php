<?php
/**
 * @name ALPHA - Global Functions
 * @version 1.0
 * @package ALPHA
 * @author Diego La Monica <me@diegolamonica.info>
 * @desc Tutte le funzioni di utilizzo globale sono contenute in questo file
 */

/**
 * @ignore
 */
$constantsToBeDefined = array();

require_once dirname(__FILE__) . '/helpers/error.php';
require_once dirname(__FILE__) . '/helpers/datetime.php';
require_once dirname(__FILE__) . '/helpers/mail.php';
require_once dirname(__FILE__) . '/helpers/sql.php';
require_once dirname(__FILE__) . '/helpers/constant.php';
require_once dirname(__FILE__) . '/helpers/array.php';

function object2XML($obj, $tabs ='', $container = ''){
	$buffer = '';
	$nextTabs =$tabs;
	if($container!='') $buffer = "$tabs<$container>\n";
	$nextTabs = $tabs ."\t"; 
	foreach($obj as $key => $value){
		$buffer .= "$nextTabs<$key>";
		if(is_object($value) || is_array($value)){
			
			
			$buffer .= "\n".object2XML($value,$tabs."\t") . $nextTabs;
			
		}else{
			
			if(preg_match('/[^a-z0-9 \-_:;]/i', $value)){
				$buffer .= "\n\t" . $nextTabs.'<![CDATA[' . $value . ']]>' . "\n$nextTabs";	
			}else{
				$buffer .= trim($value);
			}
		}
		$buffer .= "</$key>\n";
	}
	if($container!='') $buffer .= "$tabs</$container>\n";
	return $buffer;
}

function findPathRecursive($files){
	if(!is_array($files)) $files = array($files);
	$tmpScriptPath = CURRENT_SCRIPT_PATH;
	$path = preg_split('/\//',$tmpScriptPath);
	
	while( count($path)>0 ){
		$myPath = join('/',$path);
		if(substr($myPath,strlen($myPath)-1,1)!='/')$myPath.='/';
		$found = true;
		foreach($files as $file => $f ){
			$matchedFile = $myPath . $f;
			$found = (file_exists($matchedFile) && $found);
		}
		if($found){
			$myPath = str_replace($_SERVER['DOCUMENT_ROOT'],'', $myPath  );
			return $myPath;
		}
		unset($path[count($path)-1]);
	}
	
	return '';
}

/**
 * @ignore
 */
function includeConstantFrom($fileName){

	require_once CORE_ROOT .'constants/' . $fileName;
}

function includeFrom($subFolder, $fileName, $append ='', $includeAll = false){

	if(!defined('CURRENT_SCRIPT_PATH')){
		require_once CORE_ROOT. 'constants.php';
		 
	}
	$pathToSplit = CURRENT_SCRIPT_PATH .$append;
	
	$path = preg_split('/\//',$pathToSplit);
	for($i=0; count($path)>0; $i++){
		
		$myPath = join('/',$path);
		if(substr($myPath,strlen($myPath)-1,1)!='/')$myPath.='/';
		if(file_exists($myPath . $subFolder . $fileName)){
			if(
				(substr(strrev($myPath),0,1)=='/') && 
				(substr($subFolder,0,1)=='/' || ($subFolder=='' && substr($fileName,0,1)=='/'))) $myPath = substr($myPath,0, strlen($myPath)-1); 
			require_once($myPath . $subFolder . $fileName);
			if(!$includeAll) return;
		}
		unset($path[count($path)-1]);
	}
	
	if(file_exists($subFolder . $fileName)){
		require_once($subFolder . $fileName);
		return;
	}
}



/**
 * Implementa una vista di default con l'elenco dei contenuti
 * @param string $viewToUse nome della vista da utilizzare (comunque è il nome del file presente nella cartella views senza l'estensione.htm).
 * @param string $querySQL la query sql che dovrà restituire un elenco di risultati
 * @param string $itemListNameOnTemplate il nome della variabile utilizzata sul template. 
 * @param $useAuthentication se l'elenco è di consultazione pubblica, bisogna impostare questo parametro a <i>false</i> altrimenti è possibile ometterlo
 */
function implementDefaultView($viewToUse, $querySQL = '', $itemListNameOnTemplate = '', $useAuthentication =true, $paginationNamedObject = null, $beforeProcessMethod = null){
	$a = ClassFactory::get('Authentication');
	
	if(!$a->isAuthenticated() && $useAuthentication){
		$a->forceLogin();
		exit();
	}
	
	if($a->isAuthenticated()){
		$userData = $a->getUserData();
		if(isset($userData['id'])){
			$userData['userId'] = $userData['id'];
			unset($userData['id']);
		}
	}
	
	$m = ClassFactory::get('Model');
	// Aggiunta di Diego  del 2010-08-09
	
	if(isset($_POST['__ajaxMode']) && $_POST['__ajaxMode']=='y'){
		$viewToUse = defined('AJAX_SUBMIT_TEMPLATE')?AJAX_SUBMIT_TEMPLATE:'ajax-default';
	}
	if(isset($_GET['viewToUse'])) $viewToUse = $_GET['viewToUse'];
	// Fine aggiunta
	
	$m->setView($viewToUse);
	
	if($a->isAuthenticated()){
		$ud = $a->getUserData();
		$m->setMultipleVar($ud);
		$m->setMultipleVar($ud['userData'],'alpha.userInfo');
	}
	if($querySQL!=''){
		
		if($paginationNamedObject!=null){
			$p = ClassFactory::get('Paging');
			$p->setPagingObjectName($paginationNamedObject);
		}
		
		$c = ClassFactory::get('connector');
		$c->query($querySQL);
		
		$recordset = $c->allResults();
		
		$m->setVar($itemListNameOnTemplate, $recordset);
	}
	if($beforeProcessMethod!=null){
		$beforeProcessMethod();
	}
	$m->setMultipleVar($_GET);
	$m->process();
	$m->render();
}

function applicationError($title, $description, $source = '', $tip = ''){
	$m = ClassFactory::get('Model');
	$m = new Model();
	$m->clearAllVar();
	$m->resetHeader();
	$m->setView('runtime-error');
	$m->setVar('err.title',			$title);
	$m->setVar('err.description', 	$description);
	$m->setVar('err.source', 		$source);
	$m->setVar('err.tip',			$tip);
	$m->setVar('err.uri',			$_SERVER['REQUEST_URI']);
	$m->setVar('err.file',			$_SERVER['PHP_SELF']);
	$m->process();
	$m->render();
	exit();
}

function implementDefaultForm($redirectInvalidId,$viewToUse, $dataSource, $itemNameOnTemplate='resultset', $useAuthentication = true, $beforeProcessMethod = null, $beforeSaveMethod =null, $afterSaveMethod = null, $allowCache=false){
	if(isset($_GET['ID']) && (!isset($_GET['id']) || !is_numeric($_GET['id'] )))  $_GET['id'] = $_GET['ID']; 
	if((!isset($_GET['id']) || !is_numeric($_GET['id'])) && $dataSource!=''){
		header('Location: ' . $redirectInvalidId);
	}else{
		# Storage Introduction does not require session_Start anymore.
		#if(!isset($_SESSION)) session_start();
		require_once(CORE_ROOT. "classes/ClassFactory.php");
		if($useAuthentication){
			$a = ClassFactory::get('Authentication');
			if(!$a->isAuthenticated()) $a->forceLogin();
			$userData = $a->getUserData();
			if(isset($userData['id'])){
				$userData['userId'] = $userData['id'];
				unset($userData['id']);
			}
					
		}
		$m = ClassFactory::get('Model');
		if($useAuthentication) $m->setMultipleVar($userData['userData'],'alpha.userInfo');
		if(isset($_POST['__ajaxMode']) && $_POST['__ajaxMode']=='y'){
			
			$viewToUse = defined('AJAX_SUBMIT_TEMPLATE')?AJAX_SUBMIT_TEMPLATE:'ajax-default';
		}
		if(isset($_GET['viewToUse'])) $viewToUse = $_GET['viewToUse'];
		$m->setView($viewToUse);
		if($useAuthentication) $m->setMultipleVar($a->getUserData());
		
		$c = ClassFactory::get('connector');
		$err = null;
		
		if($dataSource!=''){
			$idCorso = 0;
			if(isset($_POST) && count($_POST)>0){
				$returnValue = true;
				if($beforeSaveMethod!=null){
					$returnValue = $beforeSaveMethod();
					if(!isset($returnValue) || $returnValue === null) $returnValue = true;
					
				}
				if($returnValue===true){
					$b = ClassFactory::get('Binder');
					$b->setSource($dataSource);
					$b->bindFromStructure('POST',$_POST);
					$b->save(false);
					$err = $c->getLastErrorObject();
					if($err==null){
						if(isset($_POST['ID'])){
							$id = $b->getBindedValue('ID');
						}else{
							 $id = $b->getBindedValue('id');
						}
						//if($id == '') $id = $b->getBindedValue('ID');
						$_GET['id'] = $id;
						$_POST['id'] = $id;
						if($afterSaveMethod!=null) $afterSaveMethod();
						
					}else{
						$e = ClassFactory::get('ErrorManager');
						$m->setVar('alpha.error', $e->getHTMLError());
						$m->setMultipleVar($_POST,$itemNameOnTemplate);
					}
					
				}
			}
			$sql = "select * from $dataSource where id=" . $_GET['id'];
			
			$resultset = $c->getFirstRecord($sql);
			
			$l = ClassFactory::get('Logger');
			$m->setVar('logEntry', $l->getEntries('', $dataSource, $_GET['id'],'',30));

			$m->setVar('alpha.itemDeleted','n');
			$m->setVar('alpha.itemId',0);
			if(isset($_GET['id'])) $m->setVar('alpha.itemId',$_GET['id']);
			if($resultset!=null){
				if($err==null){
					
					$m->setMultipleVar($resultset,$itemNameOnTemplate);
				}
				#print_r($resultset);
				if(isset($resultset['ELIMINATO']) )	$m->setVar('alpha.itemDeleted',$resultset['ELIMINATO']);
				if(isset($resultset['ID']) )		$m->setVar('alpha.itemId',$resultset['ID']);
			}
		}else{
			$m->setMultipleVar($_POST);
		}
		if($beforeProcessMethod!=null) $beforeProcessMethod();
		$m->setVar('alpha.dataSource', $dataSource);
		$m->setMultipleVar($_GET);
		$m->process();
		$m->render();
	}
}

/**
 * Check if user has one of the roles given to the function as list of string parameters or a single array argument.The optional last parameter will set the condition to check for all given roles, or at least one of the given roles (default behavior)
 * @param paramarray $role
 * @param bool $atLeastOne [default = true] 
 * @return bool
 */
function userHasRole(){
	$atLeastOne = true;
	$tempRoles = func_get_args();
	if(is_bool($tempRoles[count($tempRoles)-1])){
		$atLeastOne = $tempRoles[count($tempRoles)-1];
		array_pop($tempRoles);
	}
	$roles = $tempRoles;
	
	if(!is_array($roles)) $roles = array($roles);
	$a = ClassFactory::get('Authentication');
	$u = $a->getUserData();
 	$userRoles = $u['userRoles'];
 	
	if($atLeastOne){	
		return oneOfIsIn($userRoles, $roles, true);
	}else{
	 	$intersect = array_intersect($userRoles, $roles);
	 	$diff = array_diff($roles, $intersect );
	 	#print_r($diff);
	 	return (count($diff)==0);
	}
}

/**
 * Prende una chiave di relazione dal model ($key), elabora la query SQL fornita ($query) e scrive tra le variabili del model( $outputKey ) la decodifica.<br /> <strong>NOTA</strong>: è necessario che la query SQL restituisca almeno un valore in un campo denominato 'DECODED' altrimenti non verrà generata alcuna informazione di output.<br/>In <strong>$query</strong> verranno sostituite tutte el occorrenze di %key% con la chiave fornita.  
 * @param String $key la chiave da decodificare dal Model
 * @param String $query la query SQL
 * @param String $outputKey
 * @return void
 */
function decodeItem($key, $query, $outputKey, $rawOutput = false){
	$m =ClassFactory::get('Model');
	if(isset(Model::$variables[$key])){
		$sql = str_replace('%key%', Model::$variables[$key], $query);
		if($rawOutput){
			return HelperSQL::decodeItem($sql);
		}else{
			$m->setVar($outputKey, HelperSQL::decodeItem($sql));
		}
	}
}


function toDottedNumber($value){
	$value = str_replace(',','.',$value);
	if(substr($value,0,1)=='.') $value = '0'.$value;
	return $value;
	
}
/**
 * @deprecated 
 * @see HelperDateTime::formatDateTime
 */
function formattaDateTime($valore){
	
	HelperError::methodDeprecated(__FUNCTION__, "1.3.2", "HelperDateTime::formatDateTime()");
	return HelperDateTime::formatDateTime($valore);
	
}

/**
 * @deprecated
 * @see HelperSQL::populateList
 */
function populateList($variableName, $querySQL ){
	
	HelperError::methodDeprecated(__FUNCTION__, "1.3.2", "HelperSQL::populateList()");
	HelperSQL::populateList($variableName, $querySQL);
	
}

/**
 * @deprecated
 * @see HelperSQL::decodeItem
 */
function _decodeItem($query, $rowSeparator = ',', $fieldSeparator = ' ', $onlyFirstRecord = true, $onlyFirstField = true){
	
	HelperError::methodDeprecated(__FUNCTION__, "1.3.2", "HelperSQL::decodeItem()");
	return HelperSQL::decodeItem($query, $rowSeparator, $fieldSeparator, $onlyFirstRecord, $onlyFirstField);

}

/**
 * @deprecated
 * @see HelperSQL::generateIdInSQL
 */
function generateIdInSQL($mainTable, $relatedIdField, $relatedIdTable, $idFilterField, $id, $sortField = '', $exclude = false, $additionalFilter=''){
	
	
	HelperError::methodDeprecated(__FUNCTION__, '1.3.2', "HelperSQL::generateIdInSQL()");
	return HelperSQL::generateIdInSQL($mainTable, 'ID', $relatedIdField, $relatedIdTable, $idFilterField, $id, $exclude, $additionalFilter, $sortField);
	# $sql = "select * from $mainTable where ID " .  ($exclude?'NOT':'') . " in (select $relatedIdField from $relatedIdTable where $idFilterField= $id ) $additionalFilter " . (($sortField!='')?"ORDER BY $sortField":'');
}


/**
 * @deprecated
 * @see HelperMail::mailSend
 */
function mailSend($from, $to, $cc, $bcc, $subject, $body, $replyTo=null){
	
	HelperError::methodDeprecated(__FUNCTION__, "1.3.1", "HelperMail::mailSend()");
	HelperMail::mailSend($from, $to, $cc, $bcc, $subject, $body, $replyTo);
	
}
/**
 * @deprecated
 * @see HelperDateTime::dateAdd
 */
function dateAdd($interval, $number, $date) {
	HelperError::methodDeprecated(__FUNCTION__, "1.3.1", "HelperDateTime::dateAdd()");
	return HelperDateTime::dateAdd($interval, $number, $date);
	
}

/**
 * @deprecated
 * @see HelperDateTime::echoTime
 */
function echoTime(){
	
	HelperDateTime::echoTime();
	
}


/**
 * @deprecated
 * @see HelperConstant::define
 */
function _define($key, $value, $overwrite= false, $referencesConstant = false, $immediate = false){
	
	HelperError::methodDeprecated(__FUNCTION__, '1.3.2', 'HelperConstant::define()');
	HelperConstant::define($key, $value, $overwrite, $referencesConstant, $immediate);
	
}
/**
 * @deprecated
 * @see HelperConstant::defineApplySingle
 */
function _defineApplySingle($key, $value){
	
	HelperError::methodDeprecated(__FUNCTION__, '1.3.2', 'HelperConstant::applySingle()');
	HelperConstant::applySingle($key, $value);
	
}

/**
 * @deprecated
 * @see HelperConstant::defineApplySingle
 */
function _defineApplyAll(){
	
	HelperError::methodDeprecated(__FUNCTION__, '1.3.2', 'HelperConstant::applyAll()');
	HelperConstant::applyAll();
	
}

/**
 * @deprecated
 * @see HelperArray::unsetFields
 */
function unsetFields(&$array, $items){
	
	HelperError::methodDeprecated(__FUNCTION__, '1.3.2', 'HelperArray::unsetFields()');
	HelperArray::unsetFields($array, $items);
	
}

/**
 * @deprecated
 * @see HelperArray::oneOfIsIn
 */
function oneOfIsIn($arraySrc, $arrayDst, $ifDestIsEmptyReturn = true){
	
	HelperError::methodDeprecated(__FUNCTION__, '1.3.1', 'HelperArray::oneOfIsIn');
	return HelperArray::oneOfIsIn($arraySrc, $arrayDst, $ifDestIsEmptyReturn);
}

/**
 * @deprecated
 * @see HelperArray::xml2array
 */
function xml2array($contents) {
	
	HelperError::methodDeprecated(__FUNCTION__, '1.3.2', 'HelperArray::xml2array()');
	return HelperArray::xml2array($contents);
	
}

?>