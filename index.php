<?php
/**
 * 
 * CORE 1.2
 * @author Diego La Monica
 * @version 1.2
 * @package Alpha Core 1.2
 * ----------------------------
 * Changelog:
 * 
 * - scripts/JAST-stepwizard.src.js
 * 		gestione dell'attivazione automatica di tab nidificati identificandoli dall'hash della pagina
 * 
 * - Classes/Debug.php
 * - Classes/Xml2array.php
 * 		aggiunto controllo per evitare errori di ridefinizione della classe Debug (se già esistente)
 * 
 * ----------------------------
 */

if(!defined('OUTPUT_FILE_INFO')) define('OUTPUT_FILE_INFO', true);

if(!class_exists('core12')){
	class core12{
		static function doParseXML($file){
			
			$xml = new Xml2array();
			$xml->fromFile($file);
			$a = $xml->parse();
			$method = $a['method'];
			if(!is_array($method['param'])) $method['param'] = array($method['param']);
			if(isset($method['param'])){
				
				foreach($method['param'] as $key => $value){
					
					if(is_array($value) && isset($value['attributes']) && isset($value['attributes']['type'])){
						switch($value['attributes']['type']){
							
							case 'bool':
								$value= (strtolower($value['value'])=='true');
								break;
							case 'int':
								$value= intval($value['value']);
								break;
							case 'float':
								$value= floatval($value['value']);
								break;
							case 'const':
								$value=constant($value['value']);
								break;
							case 'string':		// Di default è una stringa
							default:
								$value = $value[1];
						}
						$method['param'][$key] = $value;
					}
					
				}
			}
			
			
			
			isset($method['param']) && call_user_func_array($method['name'], $method['param']);
			!isset($method['param']) && call_user_func($method['name']);
		}
		static function doRequireOnce($baseDir, $subDir, $url){
	
					
			$headers = array(
			
				'css'=>array('text/css',true),
				'js'=>array('text/javascript',true),
				'xml'=>array('text/xml',false),
				'htm'=>array('text/html',false),
				'html'=>array('text/html',false),
				'jpg' =>array('image/jpg', false),
				'png' =>array('image/png', false),
				'gif' =>array('image/gif', false),
				'mp3' =>array('audio/mpeg', false),
				'otf' =>array('application/vnd.ms-opentype', false),
				'eot' =>array('application/vnd.ms-fontobject', false),
				'swf' =>array('application/x-shockwave-flash', false),
				'doc' =>array('application/octet-stream', false)
			);
			foreach($headers as $header => $mime){
				if( preg_match('/\.' . $header . '/', $url)){
					$mimeType = $mime[0];
					header('Content-type: '.$mime[0].'; charset=UTF-8') ;
					$debugOutput = $mime[1];
					break;
				}
			}
			$debugOutput = $debugOutput && OUTPUT_FILE_INFO ;
			$mime[0] = '';
			$mime[1] = '';
			
			$fullPath =$baseDir. $subDir. '/'.$url;
			if(!file_exists($fullPath) && preg_match('/\.php$/', $fullPath)){
				$XMLFullPath = $fullPath .'.xml';
				if(file_exists($XMLFullPath)){
					$fullPath = $XMLFullPath;
					self::doParseXML($fullPath);
					return;
				}
			}
			isset($debugOutput) && $debugOutput && print('/* previsional path: ' . $fullPath . "*/\r\n");
			
			if(!file_exists($fullPath)){
				# Provo a cercare nelle directory del core
				$baseDir = CORE_ROOT;
				
				$fullPath =$baseDir. $url;
				
			}
			
			isset($debugOutput) && $debugOutput && print('/* defined path: ' . $fullPath . "*/\r\n");
			
			if(file_exists($fullPath)){
				if(isset($mimeType)) $mimeType = split($mimeType,'/');
				if(!isset($mimeType) || $mimeType[0]=='text'){
					require_once($fullPath);
										
				}else{
					$f = fopen($fullPath, 'r');
					$buffer = fread($f, filesize($fullPath));
					fclose($f);
					echo $buffer;
				}
			}else{
				header('HTTP/1.0 404 Not Found');
				if(defined('APPLICATION_DEBUG_MODE') && APPLICATION_DEBUG_MODE === true) {
					?>
					Impossibile reperire le informazioni sul file da includere
					<strong><?php echo $fullPath?></strong><br />
					<p>
					<?
					
					$result = print_r(func_get_args(), true);
					echo( nl2br(htmlspecialchars($result)));
				}
			}
		}
	}
}


if(!defined('APPLICATION_CORE_VERSION')){

	// Identifica quale core applicativo utilzizare e cosa fare
	
	if(substr($_GET['__url'],strlen($_GET['__url'])-1)=='/'){
		$_GET['__url'] .= 'index.php';
	}
	if(substr($_GET['__fn'],strlen($_GET['__fn'])-1)=='/'){
		$_GET['__fn'] .='index.php';
	}
	$fileNameOnServer = $_GET['__fn'];
	if($_GET['__url']!=''){
		$requestedUrl = $_GET['__url'];
	}else{
		$_GET['__url'] = '/index.php';
		$requestedUrl = $_GET['__url'];
	}
	#echo($fileNameOnServer);

	$baseDir = dirname($fileNameOnServer) .'/';
	
	define('ROOT', $baseDir);
	#echo($fileNameOnServer);
	require_once('classes/Xml2array.php');
	
	$xml = new Xml2array();
	$xml->fromFile(ROOT.'application.xml');
	$a = $xml->parse();

	
	isset($a['application']['core']) &&  $a['application']['core']!='' && define('APPLICATION_CORE_VERSION', 'core'.$a['application']['core']);
	!defined('APPLICATION_CORE_VERSION') && define('APPLICATION_CORE_VERSION', 'core10');
	
	/* LINK IN OTHER DIRECTORY */
	$serverType = $_SERVER['SERVER_SOFTWARE'];
//	echo $serverType;
	$isWin = preg_match('/\(win\d*\)/i', $serverType);
//	echo($isWin?'y':'n');
	$pathSeparator = ($isWin?'\\':'/');
	
	$baseDir = dirname(__FILE__);
	$baseDir = split(addslashes($pathSeparator), $baseDir);
//	print_r($baseDir);
	
	array_pop($baseDir);
	$baseDir = join($baseDir, $pathSeparator).$pathSeparator;
	define('APPLICATION_SUBCORE_DIRECTORY', $baseDir);
	
	
	//echo(APPLICATION_SUBCORE_DIRECTORY.APPLICATION_CORE_VERSION.'/index.php');
	include (APPLICATION_SUBCORE_DIRECTORY.APPLICATION_CORE_VERSION.'/index.php');
	
	
}else{
	if(isset($_GET['core_info']) && $_GET['core_info'] == 'php_info'){
		
		phpinfo();
		exit();
	}
	$url = $_GET['__url'];
	if($url!=''){
		$url = preg_replace("/([^\/]+\/)/",'../', $url);
		if(substr($url,-1)!='/') $url = preg_replace("/([^\/]*)$/",'', $url);
		if($url =='/') $url = '';
	}
	
	define('APPLICATION_URL', $url);
	
	#echo APPLICATION_URL .' - ' . $_GET['__url'] .'<br />';
	#session_start();
	
	//$coreRoot = __FILE__;
	//$coreRoot = substr($coreRoot,0, strlen($coreRoot)-strlen('index.php')); 
	//define('CORE_ROOT' , $coreRoot);
	
	define('CORE_ROOT' , APPLICATION_SUBCORE_DIRECTORY.APPLICATION_CORE_VERSION.'/');
	require_once CORE_ROOT. 'global-functions.php';
	require_once CORE_ROOT. 'classes/Xml2array.php';
	
	$xml = new Xml2array();
	$xml->fromFile(ROOT.'application.xml');
	$a = $xml->parse();
	
	if(file_exists(ROOT.'rules.xml')){
		$xml = new Xml2array();
		$xml->fromFile(ROOT.'rules.xml');
		$rules = $xml->parse();
	}else{
		$rules = array();
	}
	
	
	# Costanti necessarie senza queste il sistema non funzionerà!
	isset($a['application']['paths']['controller']) && define('APPLICATION_CONTROLLER_BASEDIR',	ROOT. $a['application']['paths']['controller']	);
	isset($a['application']['paths']['view']) 		&& define('APPLICATION_VIEW_BASEDIR',  		ROOT. $a['application']['paths']['view']	);
	isset($a['application']['paths']['repository']) && define('FILEMANAGER_DEFAULT_FOLDER',		ROOT. $a['application']['paths']['repository']);
	
	# Costanti per la customizzazione dei widget e dei metodi
	isset($a['application']['paths']['input']) 		&& define('APPLICATION_CUSTOM_INPUT_BASEDIR',	ROOT. $a['application']['paths']['input']	);
	isset($a['application']['paths']['functions']) 	&& define('APPLICATION_CUSTOM_FUNCTION_BASEDIR',ROOT. 	$a['application']['paths']['functions']	);
	
	# Costanti per la gestione del Frontend 
	isset($a['application']['paths']['css']) 		&& define('CSS_BASEDIR', 			$a['application']['paths']['css']);
	isset($a['application']['paths']['ajax']) 		&& define('AJAX_BASEDIR', 			$a['application']['paths']['ajax']);
	isset($a['application']['paths']['scripts']) 	&& define('SCRIPTS_BASEDIR', 		$a['application']['paths']['scripts']);
	
	# Costante per la gestione della Cache
	isset($a['application']['paths']['cache'])		&& define('CACHE_DEFAULT_FOLDER',			ROOT. $a['application']['paths']['cache']);
	
	
	# Creazione delle costanti applicative
	foreach($a['application']['constants'] as $constant => $value){
		
		if(is_string($value)){
			_define($constant, $value);
		}else{
			if(!isset($value['value'])) $value['value'] = '';
			if(!isset($value['attributes'])) $value['attributes'] = array('reference' => 'false');
			_define($constant, $value['value'], true, $value['attributes']['reference']=='true');
		}
	}
	
	require_once CORE_ROOT.'constants.php';
	_defineApplyAll();
	require_once CORE_ROOT.'classes/ClassFactory.php';
	ob_start();
	if(count($rules)>0){
		print_r( $rules ); echo "\n";
		$rules=$rules['rules']['rule'];
		if(isset($rules['regexp'])) $rules = array($rules);
		for($i = 0; $i < count($rules); $i++){
			$rule = $rules[$i];
			$startsWith = $rule['startswith'];
			$endsWith = $rule['endswith'];
			$regExp = $rule['regexp'];
			$redirectTo = $rule['redirectto'];

			echo "starts with: $startsWith \n";
			echo "ends with: $endsWith \n";
			echo "regular expression: $regExp \n";
			echo "Redirect to: $redirectTo \n\n";
			
			echo "Verify url: ". $_GET['__url'] . "\n";
			if(substr( $_GET['__url'],0, strlen($startsWith))  == $startsWith &&
						substr( $_GET['__url'],strlen($_GET['__url'])- strlen($endsWith))  == $endsWith){
				echo "  Url matches start + end\n";
				if(preg_match_all('@'.$regExp . '@', $_GET['__url'], $matches)){
					echo("  RegEx matched the given url\n");
					
					$_GET['__url'] = $redirectTo;
					if(isset($rule['params'])){
						$params = $rule['params']['param'];
						if(isset($params['attributes'])) $params = array($params);
						for($j = 0; $j<count($params); $j++){
							$param = $params[$j]['attributes'];
							if(isset($param['item'])){
							$_GET[$param['key']] = $matches[$param['item']][0];
							}else{
								$_GET[$param['key']] = $param['value'];
							}
							echo('Setting $_GET[' . $param['key'] . '] =' . $matches[$param['item']][0] . "\n");
						}
					}
				}
			}
			echo ("Verification complete!\n---------------------------\n\n");
		}
		
	}
	$rulesTesting = ob_get_clean();
	if(OUTPUT_FILE_INFO){
		file_put_contents(ROOT.'rules-log.txt', $rulesTesting, FILE_APPEND );
	}
	// Verifica il tipo di file per applicare i criteri di ricerca e inclusione
	$subBaseDir = '';
	if(preg_match('/\.(.+)$/',$_GET['__url'], $ext)){
		
		$ext = $ext[1];
		
		switch($ext){
			case 'php':
				
				if(isset($a['application']['debug'])){

					$debugLevel = 0;
					$dbg = $a['application']['debug'];
					if(isset($dbg['class_construct']) && strtoupper($dbg['class_construct']['attributes']['set'])=='ON') 	$debugLevel |= DEBUG_REPORT_CLASS_CONSTRUCTION; 
					if(isset($dbg['class_destruct']) && strtoupper($dbg['class_destruct']['attributes']['set'])== 'ON') 	$debugLevel |= DEBUG_REPORT_CLASS_DESTRUCTION; 
					if(isset($dbg['function_info']) && strtoupper($dbg['function_info']['attributes']['set'])== 'ON') 		$debugLevel |= DEBUG_REPORT_CLASS_FUNCTION_INFO; 
					if(isset($dbg['class_info']) && strtoupper($dbg['class_info']['attributes']['set'])== 'ON') 			$debugLevel |= DEBUG_REPORT_CLASS_INFO; 
					if(isset($dbg['function_enter']) && strtoupper($dbg['function_enter']['attributes']['set'])== 'ON') 	$debugLevel |= DEBUG_REPORT_FUNCTION_ENTER; 
					if(isset($dbg['function_params']) && strtoupper($dbg['function_params']['attributes']['set'])== 'ON') 	$debugLevel |= DEBUG_REPORT_FUNCTION_PARAMETERS; 
					if(isset($dbg['function_exit']) && strtoupper($dbg['function_exit']['attributes']['set'])== 'ON') 		$debugLevel |= DEBUG_REPORT_FUNCTION_EXIT; 
					if(isset($dbg['other_data']) && strtoupper($dbg['other_data']['attributes']['set'])== 'ON') 			$debugLevel |= DEBUG_REPORT_OTHER_DATA; 
					define('DEBUG_REPORT_LEVEL', $debugLevel);

					
					if(isset($dbg['skip']) && count($dbg['skip'])>0){
						$d = ClassFactory::get('Debug');
						$skip = $dbg['skip'];
						foreach($skip as $key => $skipDebugger){
							$d->skipGroup($skip[$key]);
						}
					}

				
				}
				
				$defaultBaseDir = APPLICATION_CONTROLLER_BASEDIR;
				#echo(substr($_GET['__url'], strpos('/', $_GET['__url'])));
				if(substr($_GET['__url'], strpos('/', $_GET['__url']))=='ajax'){
					$subBaseDir = '/'.AJAX_BASEDIR;
					$_GET['__url'] = substr($_GET['__url'], 5);
				}
				break;
			default:
				
				$defaultBaseDir = APPLICATION_VIEW_BASEDIR;
				#if($ext=='css') $subBaseDir = '/'.CSS_BASEDIR;
				if($ext=='js') 	$subBaseDir = '/'.SCRIPTS_BASEDIR;
		}
	
		
	}else{
		$defaultBaseDir = APPLICATION_CONTROLLER_BASEDIR;
		$subBaseDir = '';
	} 
		
	if(isset($a['application']['required'])){
		$requiredFiles = $a['application']['required'];
		if(!is_array($requiredFiles)) $requiredFiles = array($requiredFiles);
		foreach($requiredFiles as $key => $requiredFile){
			if(file_exists(ROOT.$requiredFile)){
				require_once ROOT.$requiredFile;
			}else{
				echo 'not found: '. ROOT.$requiredFile;
			}
		}
	}
	call_user_func(array(APPLICATION_CORE_VERSION,'doRequireOnce'),$defaultBaseDir, $subBaseDir, $_GET['__url']);
	
}

?>