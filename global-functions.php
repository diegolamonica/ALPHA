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


function echoTime(){
	
	global $echoTimeIndex, $lastMicrotime;
	if(!isset($echoTimeIndex)) $echoTimeIndex = 1;
	$currentMT =time();
	if(!isset($lastMicrotime)) $lastMicrotime = $currentMT;
	
	echo( $echoTimeIndex++ . ' - ' . $currentMT . ' &delta; '. ($currentMT-$lastMicrotime) . '<br />');
}


function _define($key, $value, $overwrite= false, $referencesConstant = false, $immediate = false){
	global $constantsToBeDefined;
	if(!isset($constantsToBeDefined[$key] ) || $overwrite){
		if($immediate){
			_defineApplySingle($key, array($value, $referencesConstant));
		}else{
			$constantsToBeDefined[$key] = array($value, $referencesConstant);
		}
	}
}

function _defineApplySingle($key, $value){
	if($value[1]){
		if(!defined($value[0])){
			
			global $constantsToBeDefined;
			_defineApplySingle($value[0], $constantsToBeDefined[$value[0]]);
		}
		$value[0]=constant($value[0]);
	}
	if(!defined($key)) define($key, $value[0]);
}

function _defineApplyAll(){
	global $constantsToBeDefined;
	
	foreach($constantsToBeDefined as $key => $value){
		if(!defined($key)){
			_defineApplySingle($key, $value);
		}
	}
	
}

function findPathRecursive($files){
	if(!is_array($files)) $files = array($files);
	$tmpScriptPath = CURRENT_SCRIPT_PATH;
	$path = split('/',$tmpScriptPath);
	
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
	
	$path = split('/',$pathToSplit);
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
 * Invia una email
 * @param string $from
 * @param string $to
 * @param string $cc
 * @param string $bcc
 * @param string $subject
 * @param string $body
 * @param string $replyTo
 * @return void
 */
function mailSend($from, $to, $cc, $bcc, $subject, $body, $replyTo=null){
	if(function_exists('customMailSend')) return customMailSend($from, $to, $cc, $bcc, $subject, $body, $replyTo);
	# ---
	# issue 0000025 resolution: Use of undefined constant sendmail_from - assumed 'sendmail_from'
	# Note:
	# I've forgotten to enclose sendmail_from between the quotes
	# ini_set(sendmail_from, $from);   
	ini_set('sendmail_from', $from);
	# ---
	if($replyTo==null) $replyTo = $from;
	$smtp = $_SERVER['SERVER_NAME']; 
	//if(function_exists('imap_8bit')) $body = imap_8bit($body);
	$header = "MIME-Version: 1.0\r\n";
	$header .= "Content-type: text/plain; charset=utf-8\r\n";
	//$header .= "Content-Transfer-Encoding: quoted-printable\r\n";
	$header .= "From: $from\r\n";
	$header .= ($cc!=''?"Cc:$cc\r\n":'');
	$header .= ($bcc!=''?"Bcc:$bcc\r\n":''); 
	$header .= "Reply-To: $replyTo\r\n" ;
	$header .= "Errors-To: $replyTo\r\n" ;
	$header .= "X-Sender: $replyTo\r\n";
	$header .= "Date: " . date('D, d M Y H:i:s O') . "\r\n";
	$header .= "Message-Id: <".md5(uniqid(rand())).".alpha@localhost>\r\n";
	$header .= "X-Mailer: ALPHA-Mail-PHP/" . phpversion();

	 mail($to,
		$subject,
		//$body,
		str_replace('=','=3D',$body),
		$header);
}
/**
 * Aggiunge una quantità di tempo specifico ad una data
 * @param string $interval è l'intervallo di riferimento (anno, mese, giorno, settimane, ore, minuti, secondi)
 * @param integer $number è il valore di riferimento per la traslazione della data. Può assumere anche un valore negativo 
 * @param string $date data nel formato <i>Y-m-d H:i:s</i> 
 * @return string La nuova data nel formato <i>Y-m-d H:i:s</i>
 */
function dateAdd($interval, $number, $date) {

    $date_time_array = preg_split('/[^0-9]+/', $date);
    
    $seconds = $date_time_array[5];
    $minutes = $date_time_array[4];
    $hours = $date_time_array[3];
    $day = $date_time_array[2];
    $month = $date_time_array[1];
    $year = $date_time_array[0];
    
    switch ($interval) {
    
        case "yyyy":
            $year+=$number;
            break;
        case "q":
            $year+=($number*3);
            break;
        case "m":
            $month+=$number;
            break;
        case "y":
        case "d":
        case "w":
            $day+=$number;
            break;
        case "ww":
        case 'W':
            $day+=($number*7);
            break;
        case "h":
            $hours+=$number;
            break;
        case "n":
            $minutes+=$number;
            break;
        case "s":
            $seconds+=$number; 
            break;            
    }
    $timestamp= mktime($hours,$minutes,$seconds,$month,$day,$year);
    return $timestamp;
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
		#echo('2.1 '.date('Y-m-d H:i:s') .'<br />');
		$c->query($querySQL);
		#echo('2.2 '.date('Y-m-d H:i:s') .'<br />');
		
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
		if(!isset($_SESSION)) session_start();
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

function oneOfIsIn($arraySrc, $arrayDst, $ifDestIsEmptyReturn = true){
	if(count($arrayDst)==0 || (count($arrayDst)==1 && $arrayDst[0]=='') ) return $ifDestIsEmptyReturn;
	for($i = 0; $i<count($arraySrc );$i++){
		
		if($arraySrc[$i]!=''){
			if(array_search($arraySrc[$i],$arrayDst,true)!==false){

				return true;
			}
		}
		
	}

	return false;
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

function formattaDateTime($valore){
	$valore = str_replace('00:00:00', '', $valore);
	$valore = trim($valore);
	if(strpos($valore, ' ') !== false ){
		$valori = split(" ", $valore);
		$valori[0] = formattaDateTime( $valori[0] );
		$valore = $valori[0] . ' ' . $valori[1];
	}else{
		$isDate = ( strlen( $valore ) == 10 );			// L'orario può essere scritto nel formato 00.00.00 oppure
		if($isDate){
			$valori = split("-", $valore);
			
			$valore = $valori[2] . "/" . $valori[1] . "/" . $valori[0];
		}
	}
	return $valore;
}

function unsetFields(&$array, $items){
	foreach($items as $key=>$value){
		unset($array[$value]);
	}
}

/**
 * xml2array() will convert the given XML text to an array in the XML structure.
 * Link: http://www.bin-co.com/php/scripts/xml2array/
 * Arguments : $contents - The XML text
 *                $get_attributes - 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
 *                $priority - Can be 'tag' or 'attribute'. This will change the way the resulting array sturcture. For 'tag', the tags are given more importance.
 * Return: The parsed XML in an array form. Use print_r() to see the resulting array structure.
 * Examples: $array =  xml2array(file_get_contents('feed.xml'));
 *              $array =  xml2array(file_get_contents('feed.xml', 1, 'attribute'));
 */
function xml2array($contents) {
	require_once('classes/Xml2array.php');
	$x = new Xml2array();
	$x->fromString($contents);
	return $x->parse();
}


function populateList($variableName, $querySQL ){
	$c = ClassFactory::get('connector');
	$m =ClassFactory::get('Model');
	
	$c->query($querySQL);
	$m->setVar($variableName,$c->allResults());
	
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
			return _decodeItem($sql);
		}else{
			$m->setVar($outputKey, _decodeItem($sql));
		}
	}
}

function _decodeItem($query, $rowSeparator = ',', $fieldSeparator = ' ', $onlyFirstRecord = true, $onlyFirstField = true){
	$c = ClassFactory::get('connector',true,'tmpConnector');
	$c->disablePagination();
	if($onlyFirstRecord){
		$rs = $c->getFirstRecord($query);

		$results = array($rs);
		
	}else{
		
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
	ClassFactory::destroy('tmpConnector', false);
	return $v;
}

function generateIdInSQL($mainTable, $relatedIdField, $relatedIdTable, $idFilterField, $id, $sortField = '', $exclude = false, $additionalFilter=''){
	$sql = "select * from $mainTable where ID " .  ($exclude?'NOT':'') . " in (select $relatedIdField from $relatedIdTable where $idFilterField= $id ) $additionalFilter " . (($sortField!='')?"ORDER BY $sortField":'');
	return $sql;	
}

		
function toDottedNumber($value){
	$value = str_replace(',','.',$value);
	if(substr($value,0,1)=='.') $value = '0'.$value;
	return $value;
	
}
?>