<?php
/**
 * 
 * CORE 1.3
 * @author Diego La Monica
 * @version 1.3
 * @package Alpha Core 1.3
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
 * V 1.3:
 * - Added APPLICATION_ABSOLUTE_URL constant
 * ----------------------------
 */

if(!defined('OUTPUT_FILE_INFO')) define('OUTPUT_FILE_INFO', true);
if(!defined('OUTPUT_DEBUG_INFO')) define('OUTPUT_DEBUG_INFO', true);
require_once('classes/Xml2array.php');

if(!class_exists('core')){
	class core{
		const QUERYSTRING_URL_PARAMETER 		= '__url';
		const QUERYSTRING_FILENAME_PARAMETER	= '__fn';
		static function startup(){
			
			if(function_exists('xapache_get_modules')){
				/*
				 * Issue 0000002: http://alpha.diegolamonica.info/issues/view.php?id=2
				 */
				$ext = apache_get_modules();
				if(!array_search('mod_rewrite', $ext)){
					echo('Apache Module: <strong>mod_rewrite</strong> not loaded.');
					exit();
				}
			}else{
				# Issue 0000021: Check for the module mod_rewrite (when apache_get_modules does not exists)
				// Check if the request is coming from a rewrite rule or is direct.
				if(
					isset($_SERVER['REDIRECT_STATUS']) &&
					isset($_SERVER['REDIRECT_QUERY_STRING']) &&
					isset($_SERVER['REDIRECT_URL'])
				){
					// OK: The request is correctly performed
				}else{
					// KO: no mod rewrite is used or the mod rewrite is misconfigured
					echo('Apache Module: <strong>mod_rewrite</strong> not loaded, does not works or is misconfigured.');
					exit();
				}
			}
			/*
			 * Security issue, blocking to override the __fn and the __url $_GET variables
			 * solution taken from: http://www.php.net/manual/en/function.parse-str.php#76792
			 * 
			 * We should migrate that in the .htaccess, much more secure.
			 */
			$str = $_SERVER['QUERY_STRING'];
			$arr = array();

			# split on outer delimiter
			$pairs = explode('&', $str);

			# loop through each pair
			foreach ($pairs as $i) {
				# split into name and value
				
				// If some querystring elements does not have any value (eg. ?param1&param2=123)
				// the following instruction causes a PHP Notice
				// list($name, $value) = explode('=', $i, 2);
				$pair = explode('=', $i, 2);
				!isset($pair[1]) && $pair[1] = '';
				$name = $pair[0]; 
				$value = $pair[1];
				# if name already exists
				if( isset($arr[$name]) ) {
					# stick multiple values into an array
					if( is_array($arr[$name]) ) {
						$arr[$name][] = $value;
					} else {
						$arr[$name] = array($arr[$name], $value);
					}
				}else {
					# otherwise, simply stick it in a scalar
					$arr[$name] = $value;
				}
			}
			if(is_array($arr[self::QUERYSTRING_URL_PARAMETER]))	$_GET[self::QUERYSTRING_URL_PARAMETER] = $arr[self::QUERYSTRING_URL_PARAMETER][0];
			if(is_array($arr[self::QUERYSTRING_FILENAME_PARAMETER])) 	$_GET[self::QUERYSTRING_FILENAME_PARAMETER] = $arr[self::QUERYSTRING_FILENAME_PARAMETER][0];
			
			define('CORE_ROOT' , dirname(__FILE__).'/');
			
			/*
			 * Correct some possible unexpected behavior of the url
			 */
			if(!isset($_GET[self::QUERYSTRING_URL_PARAMETER])) $_GET[self::QUERYSTRING_URL_PARAMETER] = '/';
			if(!isset($_GET[self::QUERYSTRING_FILENAME_PARAMETER])) $_GET[self::QUERYSTRING_FILENAME_PARAMETER] =$_SERVER['SCRIPT_FILENAME'];
			if(substr($_GET[self::QUERYSTRING_URL_PARAMETER],strlen($_GET[self::QUERYSTRING_URL_PARAMETER])-1)=='/') $_GET[self::QUERYSTRING_URL_PARAMETER] .= 'index.php';
			if(substr($_GET[self::QUERYSTRING_FILENAME_PARAMETER],strlen($_GET[self::QUERYSTRING_FILENAME_PARAMETER])-1)=='/')	$_GET[self::QUERYSTRING_FILENAME_PARAMETER] .='index.php';
			$fileNameOnServer = $_GET[self::QUERYSTRING_FILENAME_PARAMETER];
			
			if($_GET[self::QUERYSTRING_URL_PARAMETER]!=''){
				$requestedUrl = $_GET[self::QUERYSTRING_URL_PARAMETER];
			}else{
				$_GET[self::QUERYSTRING_URL_PARAMETER] = '/index.php';
				$requestedUrl = $_GET[self::QUERYSTRING_URL_PARAMETER];
			}
			
			/*
			 * Removing __fn from the QueryString to avoid
			 * security issues
			 */
			unset($_GET[self::QUERYSTRING_FILENAME_PARAMETER]);
			unset($_GET[self::QUERYSTRING_URL_PARAMETER]);
			/* ----------------------------------------  */
			$baseDir = dirname($fileNameOnServer) .'/';
			define('ROOT', $baseDir);
			
			$xml = new Xml2array();
			$xml->fromFile(ROOT.'application.xml');
			$a = $xml->parse();
		
			/* LINK IN OTHER DIRECTORY */
			$serverType = $_SERVER['SERVER_SOFTWARE'];
			$isWin = preg_match('/\(win\d*\)/i', $serverType);
			$pathSeparator = ($isWin?'\\':'/');
			
			$baseDir = dirname(__FILE__);
			$baseDir = preg_split('/'. preg_quote($pathSeparator,'/') .'/', $baseDir);
			array_pop($baseDir);
			$baseDir = join($baseDir, $pathSeparator).$pathSeparator;
			define('APPLICATION_SUBCORE_DIRECTORY', $baseDir);
			
			$url = $requestedUrl;
			if($url!=''){
				$url = preg_replace("/([^\/]+\/)/",'../', $url);
				if(substr($url,-1)!='/') $url = preg_replace("/([^\/]*)$/",'', $url);
				if($url =='/') $url = '';
			}
			/*
			 * Removing any querystring from the url
			 */
			$absUrl = preg_replace('#\?.*$#','', $_SERVER['REQUEST_URI']);
			
			if(preg_match('#[^/]$#', $absUrl)){
				$absUrl = preg_replace('#/[^/]*$#', '/', $absUrl);
			}
			$requestedFolder = dirname( $requestedUrl ) .'/';
			
			$absUrl = preg_replace('#' . preg_quote($requestedFolder,'#') .'$#', '', $absUrl );
			define('REQUESTED_URL',					$requestedUrl);
			define('APPLICATION_URL', 				$url);
			define('APPLICATION_ABSOLUTE_URL', 		$absUrl);
			
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
			
			# Costante per la gestione della Cache
			isset($a['application']['paths']['cache'])		&& define('CACHE_DEFAULT_FOLDER',				ROOT. $a['application']['paths']['cache']);
			
			# Issue #8 resolution: Using ClassFactory with custom classes requires too many code
			# Custom classes configuration setting
			isset($a['application']['paths']['classes'])  && define('APPLICATION_CUSTOM_CLASS_BASEDIR',  ROOT.  $a['application']['paths']['classes']);
			# ---
		
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
			
			
			/*
			 * ===================================================================================
			*/
				
			if(isset($_GET['core_info']) && OUTPUT_DEBUG_INFO){
				switch($_GET['core_info']){
					case 'php_info':
						phpinfo();
						exit();
						break;
					case 'unit-test':
						if(isset($_GET['class'])){
							$unitTestFileName = preg_replace("#[^a-z]#i", '', $_GET['class']);
							require_once("unit-test/$unitTestFileName.php");
							exit();
						}
						break;
				}
			}
				
			
			// Gestione delle regole di rewriting dell'indirizzo
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
					
					echo "Verify url: ". $requestedUrl . "\n";
					if(substr( $requestedUrl,0, strlen($startsWith))  == $startsWith &&
								substr( $requestedUrl,strlen($requestedUrl)- strlen($endsWith))  == $endsWith){
						echo "  Url matches start + end\n";
						if(preg_match_all('@'.$regExp . '@', $requestedUrl, $matches)){
							echo("  RegEx matched the given url\n");
							
							$requestedUrl = $redirectTo;
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
			if(OUTPUT_FILE_INFO && $rulesTesting!='' && file_exists(DEBUG_FILE_PATH)){
				file_put_contents(DEBUG_FILE_PATH.'rules-log.txt', $rulesTesting, FILE_APPEND);
			}
			// Verifica il tipo di file per applicare i criteri di ricerca e inclusione
			$subBaseDir = '';
			if(preg_match('/\.(.+)$/',$requestedUrl, $ext)){
				
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
							if(!defined('DEBUG_REPORT_LEVEL')) define('DEBUG_REPORT_LEVEL', $debugLevel);
							
							if(isset($dbg['skip']) && count($dbg['skip'])>0){
								$d = ClassFactory::get('Debug');
								$skip = $dbg['skip'];
								if(!is_array($skip)) $skip = array($skip);
								foreach($skip as $key => $skipDebugger){
									$d->skipGroup($skip[$key]);
								}
							}
		
						
						}
						
						$defaultBaseDir = APPLICATION_CONTROLLER_BASEDIR;
						if(substr($requestedUrl, strpos('/', $requestedUrl))=='ajax'){
							$subBaseDir = '/'.AJAX_BASEDIR;
							$requestedUrl = substr($requestedUrl, 5);
						}
						break;
					default:
						
						$defaultBaseDir = APPLICATION_VIEW_BASEDIR;
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
			
			self::doRequireOnce($defaultBaseDir, $subBaseDir, $requestedUrl);

					
		}
		
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
			
				'css'=>array('text/css; charset=UTF-8',true, 60*60),
				'js'=>array('text/javascript; charset=UTF-8',true, 60*60),
				'xml'=>array('text/xml; charset=UTF-8',false),
				'htm'=>array('text/html; charset=UTF-8',false),
				'html'=>array('text/html; charset=UTF-8',false),
				'jpg' =>array('image/jpg', false, 60*60*24*365),
				'png' =>array('image/png', false, 60*60*24*365),
				'gif' =>array('image/gif', false, 60*60*24*365),
				'mp3' =>array('audio/mpeg', false),
				'otf' =>array('application/vnd.ms-opentype', false, 60*60*24*365),
				'eot' =>array('application/vnd.ms-fontobject', false, 60*60*24*365),
				'swf' =>array('application/x-shockwave-flash', false),
				'doc' =>array('application/octet-stream', false)
			);
			
			foreach($headers as $header => $mime){
				if( preg_match('/\.' . $header . '/', $url)){
					$mimeType = $mime[0];
					header('Content-type: '.$mime[0]) ;
					if(isset($mime[2])){
						// Expires "Tue, 2 Mar 2010 20:00:00 GMT"
						header("Pragma: public",true);
						header('Last-Modified: Mon, 13 Sep 2011 10:37:55 GMT', true);
						header("Cache-Control: maxage=".+$mime[2],true);
						header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$mime[2]) . ' GMT',true);
						
					}
					$debugOutput = $mime[1];
					break;
				}
			}
			if(!isset($debugOutput)) $debugOutput = false;
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
				# If the file does not exists in the application directory i try to find it in the core directory
				$baseDir = CORE_ROOT;
				$fullPath =$baseDir. $url;
				
			}

			
			isset($debugOutput) && $debugOutput && print('/* defined path: ' . $fullPath . "*/\r\n");
			
			if(file_exists($fullPath)){
				if(is_dir($fullPath)){
					// This will prevent Framework location error if you are pointing 
					// to a directory instead of a page link.
					if($fullPath[strlen($fullPath)-1] != '/') {
						self::redirectToRightResource($url);
					}
					$fullPath .= 'index.php';
				}
				if(isset($mimeType)) $mimeType = preg_split('/\//',$mimeType);
				if(!isset($mimeType) || $mimeType[0]=='text'){
					
					require_once($fullPath);
										
				}else{
					$f = fopen($fullPath, 'r');
					$buffer = fread($f, filesize($fullPath));
					fclose($f);
					echo $buffer;
				}
			}else{
				$args = func_get_args();
				self::send404($fullPath, $args);
			
			}
		}
		/**
		 * Correct the url location and redirect the user agent to the right resource.
		 * @param string $url
		 */
		static function redirectToRightResource($url){
			$url .= '/';
			$qs = $_SERVER['QUERY_STRING'];
			// Removing special variables __fn and __url from query string
			// before building the new querystring.
			$qs = preg_replace('#(' .
					preg_quote(self::QUERYSTRING_FILENAME_PARAMETER) . 	# REMOVE __fn
					'|' .												# OR
					preg_quote(self::QUERYSTRING_URL_PARAMETER).		# REMOVE __url
					')\=.*?(&|$)#i', '', $qs);
			if($qs != '') $url .= "?$qs";
			header("Location: $url");
			// Redirecting and stop execution
			exit();
		}
		
		/**
		 * Send a 404 Response to the client
		 * @param string $fullPath
		 * @param any $otherData
		 */
		static function send404($fullPath = '', $otherData = ''){
			header('HTTP/1.0 404 Not Found');
			if(defined('APPLICATION_DEBUG_MODE') && APPLICATION_DEBUG_MODE === true) {
				if($fullPath!=''){
					?>
					<h1>Unable to find the resource to include</h1>
					<p><?php echo $fullPath?></p>
					<?php 
				}
				if($otherData!=''){
					?>
					<pre>
						<?php
						$result = print_r($otherData, true);
						echo( nl2br(htmlspecialchars($result)));
						?>
					</pre>
					<?php
				}
			}
						
		}
			
	}
}

core::startup();

?>
