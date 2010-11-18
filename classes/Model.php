<?php
/**
 * 
 * @author Diego La Monica
 * @version 1.0
 * @name Model
 * @package ALPHA
 * @since 1.0
 * @uses Debugger
 */

// {var:([a-z]+)(\|(@(from)\s+"([^"\\]*(?:\\.[^"\\]*)*)"\s?)?(\s?@(to)\s+"([^"\\]*(?:\\.[^"\\]*)*)")?)?}


class Model extends Debugger {
	private $viewFileName = '';
	private $viewName = DEFAULT_VIEW_NAME;
	private $buffer = '';
	private $storedFromCache = false;
	private $_doNotSendHeader = false;
	/**
	 * @var <b>array</b> è un array associativo che contiene tutte le variabili da applicare sul tempalte 
	 */
	# Modifica del 26-02-2010 di Diego La Monica
	#public $variables = array();
	static $variables = array();
	static $disallowedEscapeOn = array();
	/**
	 * @var <b>boolean</b> indica se al modello corrente è applicabile la cache 
	 */
	public $isPlugin = false;	
	/**
	 * @var <b>array</b> contiene tutti gli header da passare al client
	 */
	public static $headers = array();
	/*
	 * @var <b>array</b> Contiene gli script da inserire nel blocco di startup
	 */
	public static $startupScripts = array();
	/**
	 * 
	 * @var <b>array</b> contiene tutti gli script da inserire nell'header
	 */
	public static $headerScripts = array();
	
	/**
	 * 
	 * @var <b>boolean</b> indica se la classe è stata istanziata all'interno di un loop <code>{foreach}...{loop}</code> 
	 */
	public $inLoop = false;
	
	
	/**
	 * Questo metodo indica al Modello di non inviare al browser nessun header
	 * @return null
	 */
	function doNotSendHeader(){
		$this->_doNotSendHeader = true;	
	}
	
	function disallowEscapeOn($varName){
		
		self::$disallowedEscapeOn[] = "{var:$varName}";
		
	}
	
	/**
	 * Costruttore di classe, viene richiamato in automatico dall'oggetto ClassFactory 
	 * quando viene istanziata la classe.<br />
	 * Il metodo si preoccupa di istanziare alcune variabili di base sul modello:<br />
	 * <ul><li>URI = $_SERVER['REQUEST_URI']</li>
	 * <li>REFERER = $_SERVER['HTTP_REFERER'] oppure una stringa vuota se non è presente il referer</li>
	 * <li>URIE = l'encode di URI utilizzabile quindi nei link</li> 
	 * @return null
	 */
	public function Model(){
		parent::__construct();
		$this->setMultipleVar( array(
			'URI'=> $_SERVER['REQUEST_URI'],
			'REFERER'=> isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'',
			'URIE' => rawurlencode(rawurlencode($_SERVER['REQUEST_URI']))
		), 'alpha');
		
	}
	
	/**
	 * Aggiunge il codice Javascript nel blocco di startup se <b>$startup = true</b> altrimenti 
	 * lo inserisce come codice javascript generale nella pagina (inserito sempre nel blocco head della pagina).
	 * @param $value <b>string</b> Codice Javascript
	 * @param $startup <b>boolean</b> <code>default è false</code> se impostato a true la stringa viene considerato codice Javascript da inserire nel blocco di startup
	 * @return null
	 */
	public static function appendScripts($value, $startup = false){
		
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		if($startup){
			$h = Model::$startupScripts;
		}else{
			$h = Model::$headerScripts;
		}
		$value = trim($value);
		if($value!=''){
			$found = false;
			for($i=0; $i<count($h); $i++){
				if($h[$i] == $value ){
					$found = true;
					break;
				}
			}
			if(!$found) $h[] = $value;
		}
		
		if($startup){
			Model::$startupScripts = $h;
		}else{
			 Model::$headerScripts = $h;
		}	
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}
	
	/**
	 * Aggiunge la riga di inclusione per integrare fogli di stile, javascript o meta tag customizzati.
	 * Se la riga già esiste negli headers non viene inclusa.  
	 * @param $headers <b>string</b> è la riga da aggiungere nel blocco <code>head</code>
	 * @return null
	 */
	
	public static function appendHeaders($headers){
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		$h = Model::$headers;
		
		foreach($headers as $key => $value){
			
			$value = trim($value);
			if($value!=''){
				$found = false;
				for($i=0; $i<count($h); $i++){
					if($h[$i] == $value ){
						$found = true;
						break;
					}
				}
				if(!$found) $h[] = $value;
			}
		}
		Model::$headers = $h;
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		
	}
	
	
	/**
	 * Imposta il modello HTML/CUTEML da utilizzare sulla pagina.
	 * Il nome della vista deve coincidere con il percorso a partire dalle indicazioni
	 * della costante <code>APPLICATION_VIEW_BASEDIR</code> senza l'estensione.
	 * Una vista deve avere estensione <strong>.htm</strong>
	 * 
	 * @param $viewName <b>string</b> il nome della vista da utilizzare 
	 * @return null
	 */
	function setView($viewName){
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		$this->viewName = $viewName;
		$this->viewFileName = APPLICATION_VIEW_BASEDIR .'/'. $viewName .'.htm';
		$h = fopen($this->viewFileName, "r");
		$this->buffer = fread($h, filesize($this->viewFileName));
		fclose($h);
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}
	
	
	/**
	 * Imposta il modello da HTML/CUTEML da utilizzare adottando la stringa $buffer.
	 * @param $buffer <b>string</b> Il buffer del modello da utilizzare 
	 * @return null
	 */
	function setViewFromBuffer($buffer){
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		$this->buffer = $buffer;
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}
	
	/**
	 * Imposta una variabile sul modello. Se la variabile già esiste, viene sovrascritta con il nuovo valore.
	 * @param $key <b>string</b> è la chiave con il quale si farà riferimento sul modello utilizzando la sintassi CUTEML 
	 * @param $value <b>mixed</b> è il valore da associare alla variabile
	 * @return null
	 */
	
	function setVar($key, $value){
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		# Modifica del 26-02-2010 di Diego La Monica
		# $this->variables[$key] = $value;
		$key = preg_replace('/\%([0-9A-F]{2})/ie', 'chr(hexdec("\\1"))', $key);
		self::$variables[$key] = $value;
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}
	
	/**
	 * Applica una trasformazione <em>Perl RegExp</em> immediata alla chiave specificata adottando la sintassi  
	 * @param $key <b>string</b> il nome della variabile sul modello
	 * @param $from <b>string</b> il pattern di ricerca 
	 * @param $to <b>string</b> il pattern di sostituzione
	 * @return null
	 */
	function setReplacementRule($key, $from, $to){
		# Modifica del 26-02-2010 di Diego La Monica
		# $this->variables[$key] = preg_replace($from, $to, $this->variables[$key]);

		self::$variables[$key] = preg_replace($from, $to, self::$variables[$key]);
	}

	/**
	 * Metodo interno per aggiustare la selezione del tag html è utilizzato internamente.
	 * dal metodo pubblico <code>process</code>
	 *  
	 * @param $buffer <b>string</b> 
	 * @param $item <b>string</b>
	 * @return null
	 */
	private function getHtmlTag($buffer, $item){
		$i = strpos($buffer, $item);
		$i = $i+strlen($item);		// Mi posiziono alla fine del tag di apertura
		
		if (preg_match('/<([a-z]+)[^>]*>/s', $item, $tag)) {
			$tag = $tag[1];
		} else {
			return array($item, '');
		}
		
		$j = strpos($buffer, '</' . $tag . '>', $i);
		$k = strpos($buffer, '<' . $tag, $i);
		while($k<$j && $k>$i){
			// c'è un elemento intermedio, quindi devo cercare un tag di chiusura successivo.
			
			$j = strpos($buffer, '</' . $tag . '>', $j+1);
			$k = strpos($buffer, '<' . $tag, $k+1);
			if($j===false){
				// c'è un markup mal formattato
				return array($item, '');
			}
		}
		$item = $item . substr($buffer, $i, ($j-$i)) . '</' . $tag .'>';
		$html = substr($buffer, $i, ($j-$i) -1);
		return array($item, $html);
	}
	
	/**
	 * Preleva il rendering della vista dalla cache
	 * @return string
	 */
	function retrieveFromCache($buffer){
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());

		if (preg_match('/<!--\\s+CACHE MANAGER(.*?)-->\s*/si', $buffer, $defaultCacheBuffer)) {
			$buffer = '';
			// Esiste un controllo per la cache 
			
			// 1. estraggo i parametri che fanno il controllo della cache
			# La sintassi dell'header di cache è:
			# <!-- CACHE MANAGER
			# @flushon=			POST
			# @modelKeyVar=		PROPDOCUM,elencoProvAbilitate
			# @expiresAfter=	1W
			# @cacheFileName= 	impianti
			# @removeOnFlush=	elenco degli elementi di cache da rimuovere (separati da virgola)
			# -->
			$defaultCacheBuffer= $defaultCacheBuffer[1];
			$cacheDirectory = CACHE_DEFAULT_FOLDER.'/';
			preg_match_all('/@(.*?)=\\s*(.*?)\\r?\\n/si', $defaultCacheBuffer, $defaultCacheAttributes, PREG_PATTERN_ORDER);
			for($attributesLoop = 0; $attributesLoop<count($defaultCacheAttributes[0]); $attributesLoop++){
				$defaultCacheKey 	= strtolower($defaultCacheAttributes[1][$attributesLoop]);
				$defaultCacheValue 	= $defaultCacheAttributes[2][$attributesLoop];
				$m = new Model();
				$m->doNotSendHeader();
				$m->isPlugin=true;
				$m->setViewFromBuffer($defaultCacheValue);
				$cacheAttribs[$defaultCacheKey] = $m->render(true);
				$m = null;
			}
			
			// 2. verifico se mi trovo in una condizione di flush
			$fromCache = true;
			if(isset($cacheAttribs['flushon'])){
				$flushOns = split(',',$cacheAttribs['flushon']);
				foreach($flushOns as $cacheIndex => $flushOn){
					eval('$obj = $_'.$flushOn.';');
					
					if(isset($obj) && count($obj)>0){
						$fromCache = false;
						foreach(glob("$cacheDirectory{$cacheAttribs['cachefilename']}-*.*") as $file){
							unlink($file);
						}
						$removeOnFlush = split(',',$cacheAttribs['removeonflush']);
						foreach($removeOnFlush as $key => $filePrefix)
							foreach(glob("$cacheDirectory$filePrefix-*.*") as $file)
								unlink($file);
						
						
						break; 
					}
				}
			}
			// 3. cerco il file nella directory opportuna
			if($fromCache){
				
				
				# La sintassi del file è:
				# 	- prefisso definito negli attributi di cache (o nulla) 
				# 	- microtime (di generazione della cache)
				# 	- .cache
				$ok = false;
				foreach(glob("$cacheDirectory{$cacheAttribs['cachefilename']}-*.cache") as $file){
					
					if(file_exists($file)){
						
						$myFile = file($file);	
						if(count($myFile)>0){
							# La prima riga contiene la data di expires
							if(date('Y-m-d H:i:s')>$myFile[0]){
								$expired = true;
								#print_r($myFile);
								unlink($myFile[count($myFile)-1]);
								unlink($file);
								
							}
							
							# Dalla seconda riga ci sono 
							# i valori serializzati delle variabili che ho indicato come chiavi
							 
							if(!$expired){
								$ok = true;
								$modelKeyVars = split(',',$cacheAttribs['modelkeyvar']);
								foreach($modelKeyVars as $cacheIndex => $keyVar){
									if(substr($myFile[$cacheIndex+1],0,-1) != serialize($this->getVar($keyVar))) $ok = false;
									
									if(!$ok){
										break;
									}
								}
								if($ok) break;
							}
							
						}
					}
				}
				// 4. se disponibile ($ok = true) restituisco il buffer di cache giusto
				if($ok){
					# Il file è quello giusto, posso utilizzarlo come cache
					$buffer = file_get_contents($myFile[count($myFile)-1]);
					
					$this->storedFromCache = true;
				}
			}
		}else{
			$buffer = '';
		}
		
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		
		return $buffer;
	}
	
	/**
	 * Salva l'output della vista corrente nella cache.
	 * @param $buffer <b>string</b> è il buffer della vista da salvare nel file di cache
	 * @return null
	 */
	function saveCache($buffer){
		
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		if (preg_match('/<!--\\s+CACHE MANAGER(.*?)-->\s*/si', $buffer, $defaultCacheBuffer)) {
			// Esiste un controllo per la cache 
			
			// 1. estraggo i parametri che fanno il controllo della cache
			# La sintassi dell'header di cache è:
			# <!-- CACHE MANAGER
			# @flushon=			POST
			# @modelKeyVar=		PROPDOCUM,elencoProvAbilitate
			# @expiresAfter=	1W
			# @cacheFileName= 	impianti
			# -->
			$defaultCacheBuffer= $defaultCacheBuffer[1];
			preg_match_all('/@(.*?)=\\s*(.*?)\\r?\\n/si', $defaultCacheBuffer, $defaultCacheAttributes, PREG_PATTERN_ORDER);
			for($attributesLoop = 0; $attributesLoop<count($defaultCacheAttributes[0]); $attributesLoop++){
				$defaultCacheKey 	= strtolower( $defaultCacheAttributes[1][$attributesLoop] );
				$defaultCacheValue 	= $defaultCacheAttributes[2][$attributesLoop];
				$cacheAttribs[$defaultCacheKey] = $defaultCacheValue;
			}
			
			$buffer = preg_replace('/<!--\\s+CACHE MANAGER(.*?)-->\s*/si','', $buffer);
			
			// Identifico il nome corretto (e univoco) del nuovo file di cache da generare
			$cachedName = CACHE_DEFAULT_FOLDER .'/'. $cacheAttribs['cachefilename'] . '-' . date('YmdHis') . '-';
			$nextIndex = 1;
			foreach(glob($cachedName.'*') as $file){
				
				if(preg_match('/\-.*-(.*)\.cache/i',$file, $items)){
					
					$nextIndex = $items[1]+1;
					
				}
					
			}
			// Definisco il nome del file di cache e del file indice di cache
			$htmlCachedName = $cachedName . $nextIndex .'.html'; 
			$cachedName .= $nextIndex .'.cache';
			$expireUnit = substr($cacheAttribs['expiresafter'],-1);
			$expireValue = substr($cacheAttribs['expiresafter'],0,-1);
			
			// Scrivo le righe per il file di cache in un array
			$cacheFile = array();
			$cacheFile[] = date('Y-m-d H:i:s', dateAdd($expireUnit, $expireValue, date('Y-m-d H:i:s')));
			
			$modelKeyVars = split(',',$cacheAttribs['modelkeyvar']);
			foreach($modelKeyVars as $cacheIndex => $keyVar){
				$cacheFile[] = serialize($this->getVar($keyVar));
				
			}
			$cacheFile[] = $htmlCachedName;
			
			// Trasformo l'array in un buffer
			$cacheFile = implode("\n", $cacheFile);
			file_put_contents($cachedName, $cacheFile);
			file_put_contents($htmlCachedName, $buffer);
			
		}
		 
		
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}
	
	/**
	 * Effettua il rendering del plugin sul modello
	 * @param $mBuffer <b>string</b> è il buffer corrispondente al contenuto del file di plugin.   
	 * @param $attribs <b>array</b> è l'elenco di attributi acquisiti dal tag plugin sulla vista
	 * @return string
	 */
	private function renderPlugin($mBuffer, $attribs){
		
		foreach($attribs as $key => $value){
			if(substr($value, 0, strlen(MODEL_KEYWORD_VAR)+2) == '{' . MODEL_KEYWORD_VAR .':'){
				$result = $this->parseVar($value);
				$attribs[$key]= $result[1];
			} 
			
		}

		
		$m = new Model();
		$m->doNotSendHeader();
		$m->isPlugin = true;
		$m->setViewFromBuffer($mBuffer);
		# Modifica del 26-02-2010 di Diego La Monica
		#$m->setMultipleVar($this->variables);
		# Fine Modifica
		$m->clearVar('input');
		$m->setMultipleVar($attribs,'input');
		$mBuffer = $m->render(true);
		$mBuffer = $m->process($mBuffer);
		
		# Modifica del 26-02-2010 di Diego La Monica
		$m->clearVar('input');
		# Fine Modifica
		return $mBuffer;
	}
	
	
	
	/**
	 * Elabora la vista sostituendo tutte le occorrenze del codice CUTEML con gli opportuni valori
	 * @param $buffer <b>string</b> <code>default null</code> se non impostato elabora il buffer corrispondente alla vista corrente altrimenti elabora il buffer passato come parametro
	 * @return string restituisce l'elaborazione del buffer
	 */
	function process($buffer = null){
		
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		if($buffer == null) $buffer = $this->buffer;
		if(!$this->storedFromCache) $tempBuffer = $this->retrieveFromCache($buffer);
		if($tempBuffer !=''){
			$buffer = $tempBuffer;
		}else{
			/*
			 * Gestisce tutti gli input di type="custom" 
			 */
			
			$count = preg_match_all('/<(\w+[^>]*?type="custom".*?)\/?>/si', $buffer, $input, PREG_PATTERN_ORDER);
			$input = $input[0];
			for($i=0; $i<count($input); $i++){
				if(strpos($buffer, $input[$i])!==false){
					$theInput = str_replace("\n",' ', $input[$i]);
					preg_match_all('/([a-z_\-0-9]+)="([^"\\\]*(?:\\\.[^"\\\]*)*)"/i', $theInput, $attributes, PREG_PATTERN_ORDER);
					unset($attribs);
					for($j=0; $j<count($attributes[0]); $j++){
						$attribs[$attributes[1][$j]] = str_replace('\"','"', $attributes[2][$j]);
					}
					
					
					if(substr($theInput,-2,2)!='/>'){
						// Devo prendere anche il contenuto e metterlo nell'attributo html
						
						$inputResult = $this->getHtmlTag($buffer, $input[$i]);
						$input[$i] = $inputResult[0];
						$attribs['html'] = $inputResult[1];
					}
					if(!isset($attribs['inloop']) && !$this->inLoop || (($attribs['inloop']=="true") && $this->inLoop)){
					
						// Cerca prima se esiste un input customizzato rilasciato con l'applicazione
						
						if(defined('APPLICATION_CUSTOM_INPUT_BASEDIR')){
							$nomeFile = APPLICATION_CUSTOM_INPUT_BASEDIR . '/'.$attribs['model'] . '.htm';
							if(!file_exists($nomeFile)){
								$nomeFile = INPUTROOT. $attribs['model'] . '.htm';
							}
						}else{
							$nomeFile = INPUTROOT. $attribs['model'] . '.htm';
						}
						
						$renderingArea = '';
						
						if(file_exists($nomeFile)){
	
							$mBuffer = file_get_contents($nomeFile);
							
							// Acquisisco gli attributi di default
							unset($defaultAttributes);
							if (preg_match('/<!--\\s+DEFAULT ATTRIBUTES(.*?)-->/si', $mBuffer, $defaultAttributesBuffer)) {
								$defaultAttributesBuffer= $defaultAttributesBuffer[1];
								preg_match_all('/@(.*?)=\\s*(.*?)\\r?\\n/si', $defaultAttributesBuffer, $defaultAttributes, PREG_PATTERN_ORDER);
								for($attributesLoop = 0; $attributesLoop<count($defaultAttributes[0]); $attributesLoop++){
									$defaultKey 	= $defaultAttributes[1][$attributesLoop];
									$defaultValue 	= $defaultAttributes[2][$attributesLoop];
									if(!isset($attribs[$defaultKey])) $attribs[$defaultKey] = $defaultValue;
								}
							} else {
								unset($defaultAttributes);
							}
							
							// Renderizzo l'oggetto
							$chiaveLastKey = 'input.lastkey' . md5(date('Y-m-d h:i:s')) . '_' . sha1(date('Y-m-d h:i:s'));
							$mBuffer .='{var:'.$chiaveLastKey. '}';
							$this->setVar($chiaveLastKey, '');
							$mBuffer = $this->renderPlugin($mBuffer, $attribs);
							
							/*
							 * A questo punto devo prendere solo le porzioni di codice che mi servono
							 */
							
							if (preg_match('/<!-- RENDER:BEGIN -->(.*)<!-- RENDER:END -->/s', $mBuffer, $renderingArea)){
								$renderingArea = $renderingArea[1];
							}else{
								$renderingArea = '';
							}
	
							if(preg_match('/<!-- HEADER:BEGIN -->(.*)<!-- HEADER:END -->/s', $mBuffer, $headingArea)){
								$headingArea = $headingArea[1];
							}else{
								$headingArea = '';
							}
							if (preg_match('/\/[*] script:begin [*]\/(.*)\/[*] script:end [*]\//s', $mBuffer, $headingScripts)) {
								$headingScripts = $headingScripts[1];
							} else {
								$headingScripts = "";
							}
							
							if(preg_match('/\/\\* init-script:begin \\*\/(.*)\/\\* init-script:end \\*\//si', $mBuffer, $startupScripts)){
								$startupScripts = $startupScripts[1];
							}else{
								$startupScripts = '';
							}
							$headingArea = split("\n",$headingArea);
							Model::appendHeaders($headingArea);
							Model::appendScripts($headingScripts);
							Model::appendScripts($startupScripts, true);
						}else{
							$input[$i] = '<strong>custom model for ' . $attribs['model'] . ' does not exists</strong>';
						}
						
						$buffer = str_replace($input[$i], $renderingArea, $buffer, $cnt);
					}
				}
			}
		}			
		$this->buffer = $buffer;
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		return $buffer;
	}
	/**
	 * Restituisce il buffer associato alla vista corrente
	 * @return string
	 */
	function getBuffer(){
		return $this->buffer;
	}
	/**
	 * Cerca tutti i token nel buffer HTML e le converte nei valori opportuni
	 * @param $bufferedOutput <b>boolean</b> <code>default false</code> se impostato a true il metodo restituirà in output il buffer elaborato  
	 * @return <b>string</b> solo se <code>$bufferedOutput = true</code> altrimenti null
	 */
	function render($bufferedOutput=false){
		if(!$this->_doNotSendHeader && !headers_sent()){
			header('Content-type: text/html; charset=UTF-8') ;
		}
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		$buffer = $this->buffer;
		if(!$this->storedFromCache) $tempBuffer = $this->retrieveFromCache($buffer);
		if($tempBuffer !='') $buffer = $tempBuffer;
		if($this->storedFromCache){
			
			if($bufferedOutput) return $buffer;
			echo($buffer);
			return $buffer;
		}
		
		$savedBuffer = '';
		$first_position = 0;
		$last_first_position = 0;
		# 2008-11-29: Ho scoperto un errore: l'ultima variabile {var:...} non viene considerata
		# quindi per consentirne l'elaborazione aggiungo per ora una variabile con indicazione
		# random del suo nome
		
		#$chiaveLastKey = 'lastkey' . md5(date('Y-m-d h:i:s')) . '_' . sha1(date('Y-m-d h:i:s'));
		
		#$buffer .='{var:'. $chiaveLastKey . '}';
		#$this->setVar($chiaveLastKey,'');
		#print_r(array($buffer,preg_match('/{([a-z\-_]+):(.*)}/', $buffer, $items, null, $first_position), $first_position, $items));
		while(( $result = preg_match('/{([a-z\-_]+):(.*)}/', $buffer, $items, null, $first_position))!=0){
			$last_first_position = strpos($buffer, $items[0]);
			if($first_position>$last_first_position) $last_first_position = $first_position;
			if($first_position==$last_first_position){
				$dbg->write('last first position:' . $first_position);
				$dbg->write('first position:' . $first_position);
				$lastItem = $items[0];
				 
				$result = preg_match('/{([a-z\-_]+):(.*)}/', $buffer, $items, null, $first_position);
				
				if($result==0){
					break;
				}else{
				#	$first_position = $last_first_position+1;
				}
				$dbg->write('match on: ' . $items[0]);
			}
			$first_position = $last_first_position+1;
			$keyword = $items[1];
			$value = $items[2];
			if(preg_match('/{([a-z\-_]+):(.*)}/', $value)!==false){
				
				$tm = new Model();
				$tm->doNotSendHeader();
				# Modifica del 26-02-2010 di Diego La Monica
				#$tm->setMultipleVar($this->variables);
				#Fine Modifica
				$tm->setViewFromBuffer($value);
				$value = $tm->render(true); 
				unset($tm);
			}
			
			switch($items[1]){
				case MODEL_KEYWORD_REDIRECT:
					
					
					$fileToRedirect = APPLICATION_URL.$value;
					header('Location: ' . $fileToRedirect, true);
					exit();
				case MODEL_KEYWORD_INCLUDE:
					$fileToInclude = ROOT.$value;
					ob_start();
					include $fileToInclude;
					$tmpBuffer = ob_get_clean();
					# Aggiutna del 24-05-2010 di Diego La Monica
					$im = ClassFactory::get('Model', true, 'includeModel');
					$im->setViewFromBuffer($tmpBuffer);
					$im->process();
					$tmpBuffer = $im->render(true);
					ClassFactory::destroy('includeModel');
					# Fine Aggiutna
					$buffer = str_replace($items[0], $tmpBuffer,$buffer);
					break;
				case MODEL_KEYWORD_INCLUDE_STATIC:
						
					if(!preg_match('/^https?\:\/\//',$value)){
						$fileToInclude = ROOT.$value;
					}else{
						$fileToInclude = $value;
					}
					$tmpBuffer = file_get_contents($fileToInclude);
					$buffer = str_replace($items[0], $tmpBuffer,$buffer);
					break;
				case MODEL_KEYWORD_LOOP_START:
					
					$tmpBuffer = '';
					
					$loopBlock = $this->endBlockSearch($buffer, $items[0],MODEL_KEYWORD_LOOP_START, MODEL_KEYWORD_LOOP_END);
					$blockName = $value;
					$idJSBlock = microtime();
					
					
					
					if(preg_match('/SQL\(([^\)]+)\)::(.*)/',$blockName, $sqlResults )){
						$c = ClassFactory::get('connector');
						
						$c->query($sqlResults[2]);
						$this->setVar($sqlResults[1], $c->allResults());

						$blockName = $sqlResults[1];
					}
					# Modifica del 26-02-2010 di Diego La Monica
					# if(!isset($this->variables[$blockName]) && array_search($blockName, array('$_GET','$_POST','$_COOKIE', '$_ENV', '$_FILES', '$_REQUEST', '$_SERVER', '$_SESSION'))!==false){
					# 	eval( '$temporaryObject='. $blockName . ';');
					#	$this->variables["$blockName"] =$temporaryObject;
					# }
					if(!isset(self::$variables[$blockName]) && array_search($blockName, array('$_GET','$_POST','$_COOKIE', '$_ENV', '$_FILES', '$_REQUEST', '$_SERVER', '$_SESSION'))!==false){
						eval( '$temporaryObject='. $blockName . ';');
						self::$variables["$blockName"] =$temporaryObject;
					}
					# Fine Modifica
					
					$tempResult = $this->getVar($blockName);
					$lastVariables = ''; 
					if($tempResult!='' && $tempResult!=null){
						
						$m = new Model();
						$m->doNotSendHeader();
						$m->inLoop = true;
						$i = 0;
						
						$tempIterator 		= $m->getVar('iterator');
						$tempIteratorKey 	= $m->getVar('iterator.key');
						$tempIteratorValue 	= $m->getVar('iterator.value');
						$tempIteratorLast 	= $m->getVar('iterator.last');
						$tempIteratorPrev 	= $m->getVar('iterator.prev');
						
						foreach($tempResult as $key => $value){
							
							
							$m->setViewFromBuffer($loopBlock);
							# Modifica del 26-02-2010 di Diego La Monica
							# $m->setMultipleVar($this->variables);
							# Fine Modifica del 26-02-2010 di Diego La Monica
							
							
							$i+=1;
														
							$m->setVar('iterator', $i);
							$m->setVar('iterator.key', $key);
							$m->setVar('iterator.value', $value);
							$m->setVar('iterator.last', ($i == count($tempResult)));
							$m->setVar('iterator.prev', $lastVariables);
							
							
							$m->setMultipleVar($value, $blockName);
							$m->process();
							$tmpBuffer .= $m->render(true);
							
							$lastVariables = $value;
							# Modifica del 26-02-2010 di Diego La Monica
							# $m->clearAllVar();
							$m->clearVar('iterator');
							# Fine Modifica del 26-02-2010 di Diego La Monica
							
						}
						$m->inLoop = false;
						$m->setMultipleVar(
							array(
								'iterator'=> $tempIterator,
								'iterator.key' => $tempIteratorKey,
								'iterator.value' => $tempIteratorValue,
								'iterator.last' => $tempIteratorLast,
								'iterator.prev' => $tempIteratorPrev)
						);
						unset($m);
						
						
					}else{
						$tmpBuffer = '';
					}
					$buffer = str_replace($items[0].$loopBlock.'{' . MODEL_KEYWORD_LOOP_END.'}', $tmpBuffer, $buffer);
					
					break;
				
				case MODEL_KEYWORD_IF_START:
					
					$block = $this->endBlockSearch($buffer, $items[0],MODEL_KEYWORD_IF_START, MODEL_KEYWORD_IF_END);
					$replacement = $items[0].$block.'{'.MODEL_KEYWORD_IF_END .'}';
					# Modifica del 26-02-2010 di Diego La Monica
					# if($this->evaluate($value, $this->variables)){
					if($this->evaluate($value, self::$variables)){
					# Fine Modifica
						$buffer = str_replace($replacement, $block, $buffer);
						
					}else{
						$buffer = str_replace($replacement, '', $buffer);
						
					};
					break;
				case MODEL_KEYWORD_PHP_BLOCK_START:
					
					$block = $this->endBlockSearch($buffer, $items[0],MODEL_KEYWORD_PHP_BLOCK_START, MODEL_KEYWORD_PHP_BLOCK_END);
					$replacement = $items[0].$block.'{'.MODEL_KEYWORD_PHP_BLOCK_END .'}';
					
					
					ob_start();
					eval($block);
					$block = ob_get_clean();
					if($value!='EMPTY'){
						$this->setVar($value, $block);
						$block = '';
					}
						$buffer = str_replace($replacement, $block, $buffer);

					break;
				case MODEL_KEYWORD_IFVAR_START:
					
					$block = $this->endBlockSearch($buffer, $items[0],MODEL_KEYWORD_IFVAR_START, MODEL_KEYWORD_IF_END);
					$replacement = $items[0].$block.'{'.MODEL_KEYWORD_IF_END .'}';
					$value = trim($value);
					$evaluation = split(' ',$value); 
					$ifvVar1 = $this->parseVar($evaluation[0]);
					$ifvVar2 = $this->parseVar($evaluation[2]);
					$replaceSuccess = false;
					switch($evaluation[1]){
						case 'equal-to':
							$replaceSuccess = ($ifvVar1 == $ifvVar2); 
							break;
						case 'less-than':
							$replaceSuccess = ($ifvVar1 < $ifvVar2);
							break;
						case 'greater-than':
							$replaceSuccess = ($ifvVar1 > $ifvVar2);
							break;
						case 'less-or-equal':
							$replaceSuccess = ($ifvVar1 <= $ifvVar2); 
							break;
						case 'greater-or-equal':
							$replaceSuccess = ($ifvVar1 >= $ifvVar2); 
							break;
						case 'different-to':
							$replaceSuccess = ($ifvVar1 != $ifvVar2); 
							break;
						case 'exists':
							# Modifica del 26-02-2010 di Diego La Monica
							# $replaceSuccess = isset($this->variables[$evaluation[0]]);
							$replaceSuccess = isset(self::$variables[$evaluation[0]]);
							# Fine Modifica
							break;
					}
					
					if($replaceSuccess){
						$buffer = str_replace($replacement, $block, $buffer);
					}else{
						$buffer = str_replace($replacement, '', $buffer);
						
					};
					break;
				case MODEL_KEYWORD_PHP:
					preg_match('/{php:\\s*"([^"\\\]*(?:\\\.[^"\\\]*)*)"\\s*}/is', $buffer, $items);
					$value = $items[1];
					# Modifica del 26-02-2010 di Diego La Monica 
					# $value = $this->evaluate($value, $this->variables);
					$value = $this->evaluate($value, self::$variables);
					# Fine Modifica
					$items[0] = preg_replace('/[^a-z0-9]/i', '\\\\\0', $items[0]);
					
					$buffer = preg_replace('/' . $items[0] . '/', $value,$buffer);
					break;
				case MODEL_KEYWORD_VAR:
					$result = $this->parseVar($buffer);
					
					# Modifica di Diego del 05-03-2010
					if($result[0]=='\{var\:input\.html\}')
						$buffer = preg_replace('/' . $result[0] . '/', print_r($result[1], true) ,$buffer,1);
					else
						$unescaped = stripslashes($result[0]);
						if(array_search($unescaped, self::$disallowedEscapeOn)!==false){
							$buffer = preg_replace('/' . $result[0] . '/', print_r($result[1], true),$buffer,1);
						}else{
							$buffer = preg_replace('/' . $result[0] . '/', str_replace('"', '&quot;', print_r($result[1], true) ),$buffer,1);
						}
					# Fine modifica
					
					break;
				case MODEL_KEYWORD_SETVAR:
					$result = $this->setVarRuntime($buffer);
					$buffer = preg_replace('/' . $result[0] . '/', (is_array($result[1]))?print_r($result[1], true):$result[1],$buffer,1);
					break;
				case MODEL_KEYWORD_FUNCTION:
					$tmpValue = $value;
					$value = trim($value);
					
					$c = preg_match('/([a-z0-9]+)\\((.*)\\)$/i', $value, $subItems);
					$error =false;

					# Se la funzione si trova in linea con altri parametri potrebbe causare un errore di runtime, 
					# quindi provo a correggere l'errore ma se non riuscissi a risolverlo notifico con un messaggio
					# user-friendly quanto accaduto e come è possibile risolvere il problema.
				
					if(count($subItems)<2){
						$error = true; 
						while($i = strrpos($tmpValue, '}')){
							$tmpValue = substr($tmpValue,0, $i);
							$value = trim($tmpValue);
							
							$c = preg_match('/([a-z0-9]+)\\((.*)\\)/i', $value, $subItems);
							if(count($subItems)<2){
								$error = true;
								
							}else{
								$items[0] = '{' . MODEL_KEYWORD_FUNCTION . ':' . $value .'}';
								
								$error =false;
								
							}
							
						}
						
					}
					
					if(!$error){
						$fnName = $subItems[1];
						$params = $subItems[2];
						$items[0] = preg_replace('/[^a-z0-9]/i', '\\\\\0', $items[0]);
						$value = $this->parseFunction($fnName, $params);
						$buffer = preg_replace('/' . $items[0] . '/', $value,$buffer);
					}else{
						applicationError(
							'Definizione errato della chiamata al metodo', 
							'&Egrave; stata definita nel modello una chiamata ad una funzione custom in modo inappropriato.', 
							$items[0], 
							'Le cause di questo errore possono essere diverse:</p>
							<ul>
								<li>La riga del modello su cui è richiamata la funzione è troppo compelssa</li>
								<li>Una funzione deve cotnenere (anche in assenza di parametri) le parentesi tonde aperte e chiuse</li>
								 
							</ul>.');
				
					}
					break;
					
			}
		};
		
		if(count(Model::$headers)>0 && !$this->isPlugin){
			$h = implode(Model::$headers,"\n") . "\n";
			
			$s = '	<script type="text/javascript"><!--' ."\n";
			$s .= implode(Model::$headerScripts,"\n") . "\n";
			
			$s .='		_.extend(\'alpha-startup\', {
							startup: function(){';
			$s .= implode(Model::$startupScripts,"\n") . "\n";
			$s .= "} });\n\n";
			$s .= '--></script>';
			
			$h .= $s;
			
			$buffer = str_replace('</head>', $h . "\n</head>", $buffer );
		}
		
		$buffer = str_replace('href="/', 'href="' . APPLICATION_URL,  $buffer);
		$buffer = str_replace('src="/', 'src="' . APPLICATION_URL,  $buffer);
		
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		
		
		$buffer = str_replace('@@ROOT/', APPROOT, $buffer);
		
		// Salvo il buffer in cache
		
		$this->saveCache($buffer);
		
		if($bufferedOutput) return $buffer;
		echo($buffer);
		
	}
	private function replaceNestedVar($value){
		if(preg_match_all('/\[\*([a-z0-9_\-\.]+)\*\]/i',  $value, $subItems)){
			for($i = 0; $i<count($subItems); $i++){
				# Modifica del 26-02-2010 di Diego La Monica
				#$value = str_replace($subItems[0][$i], $this->variables[$subItems[1][$i]], $value);
				$value = str_replace($subItems[0][$i], self::$variables[$subItems[1][$i]], $value);
				# Fine Modifica
				
			}
		}
		
		return $value;
		
	}
	private function setVarRuntime($buffer){
		
		if(preg_match('/{' .MODEL_KEYWORD_SETVAR.':([a-z0-9_\-\.]+) ([^{}]+)}/i', $buffer, $items)){

			$newVar = $items[1];
			$value = $items[2];
			$value = $this->replaceNestedVar($value);
			$result = $this->getVar($value);

			if($result!='') $value = $result;
			# Modifica del 26-02-2010 di Diego La Monica
			# $this->variables[$newVar] =  $value;
			self::$variables[$newVar] =  $value;
			# Fine Modifica
			$result = array(
				preg_replace('/[^a-z0-9]/i', '\\\\\0', $items[0]), 
				''
			) ;
			
		}else{
			
			$result = '';
		}
		return $result;
	}
	
	public function getVar($key){
		# Modifica del 26-02-2010 di Diego La Monica
		#if(isset($this->variables[$key])){
		#	$value = $this->variables[$key];
		if(isset(self::$variables[$key])){
			$value = self::$variables[$key];
		# Fine Modifica
		}else{
			
			$value = '';
			$var = split('\.', $key);
			# Modifica del 26-02-2010 di Diego La Monica
			# $tmpVar = $this->variables;
			$tmpVar = self::$variables;
			# Fine Modifica
			$tmpVarName = '';
			if(count($var)>1){
				for($i=0;$i<count($var);$i++){
					if($tmpVarName!='') $tmpVarName.='.';
					$tmpVarName .= $var[$i];
					if(isset($tmpVar[$var[$i]])){
						
						$tmpVar = $tmpVar[$var[$i]];
					}else{
						# Modifica del 26-02-2010 di Diego La Monica
						# if(isset($this->variables[$tmpVarName])){
						#	$tmpVar = $this->variables[$tmpVarName];
						# }else{
						#	unset($tmpVar);
						#	break;
						# }
						
						if(isset(self::$variables[$tmpVarName])){
							$tmpVar = self::$variables[$tmpVarName];
						}else{
							unset($tmpVar);
							break;
						}
						# Fine Modifica
					}
				}
				if(isset($tmpVar)) $value = $tmpVar;
				
			}
		}
		return $value; 
	}
	
	/**
	 * Cerca la prima occorrenza di variabile CUTEML nel buffer
	 * applicando (se specificata) la trasformazione dal valore "@from" al valore "@to"
	 * @example {var:myVariable|@from "[A-Z]" @to "\1"}  
	 * @param $buffer <b>string</b> è il buffer corrispondente alla vista corrente
	 * @return array [0] la trasformazione della variabile riscontrata in un formato accettato dalla sintassi Perl RegEx [1] il valore da sostituire
	 */
	private function parseVar($buffer){
		global $formatArray;
		

		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		preg_match('/{' .MODEL_KEYWORD_VAR.':([a-z0-9_\-\.]+)(\\|(@(from)\\s+"([^"\\\]*(?:\\\.[^"\\\]*)*)"\\s?)?(\\s?@(to)\\s+"([^"\\\]*(?:\\\.[^"\\\]*)*)")?)?}/i', $buffer, $items);
		$value = $items[1];
		$value = $this->getVar($value);
		
		$from =''; $to = '';					
		if(count($items)>2){
			if($items[4]=='from'){
				$from = $items[5];
				if($items[7]=='to'){
					$to = $items[8];
				}else{
					$to = '';
				}
			}else{
				$from = '';
				if($items[4]=='to'){
					$to = $items[5];
				}else{
					$to = '';
				}
			}
		}
		if($from!='' && $to!=''){

			global $formatArray;
			
			if(key_exists($from, $formatArray)) $from = $formatArray[$from];
			if(key_exists($to,   $formatArray)) $to = $formatArray[$to];

			$value = preg_replace('/'. $from.'/', $to, $value);
		}
		
		if($value==null || !isset($value)) $value ='';
		$result = array(
					preg_replace('/[^a-z0-9]/i', '\\\\\0', $items[0]), 
					$value
				);

		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		return 	$result;
	}
	
	/**
	 * Elabora l'oggetto funzione CUTEML e ne restituisce il risultato
	 * @param $fnName <b>string</b> nome della funzione da eseguire presente nel path delle funzioni custom (o nelle funzioni di sistema se la custom non è presente)
	 * @param $params <b>array</b> è un array (non associativo) di parametri da fornire alla funzione
	 * @return string il risultato della funzione
	 */
	private function parseFunction($fnName, $params){
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		preg_match_all('/("[^"\\\]*(?:\\\.[^"\\\]*)*")|(\'[^\'\\\]*(?:\\\.[^\'\\\]*)*\')|([^,]+)/i', $params, $params);
		$params = $params[0];
						
		for($i=0; $i<count($params);$i++){
			
			$params[$i] = trim($params[$i]);
			if(substr($params[$i],0, strlen(MODEL_KEYWORD_FUNCTION)+1) == MODEL_KEYWORD_FUNCTION .':'){
				// è una funzione devo rielaborarla ricorsivamente
				$params[$i] = substr($params[$i], strlen(MODEL_KEYWORD_FUNCTION)+1);
				$value='';
				$nestingLevel = 0;
				for($j = $i; $j<count($params);$j++){
					if($value!='' && str_replace(')','',$params[$j])!='') $value.=',';
					$value.= trim($params[$j]);
					if(substr($params[$j],0, strlen(MODEL_KEYWORD_FUNCTION)+1) == MODEL_KEYWORD_FUNCTION .':') $nestingLevel+=1;
					if(substr($value,-1,1)==')'){
						$checkClosures = strrev($value);
						while(substr($checkClosures,0,1)==')'){ 
							$nestingLevel-=1;
							$checkClosures = substr($checkClosures,1);
						}
						if($nestingLevel<=0){
							# devo ridurre il numero di parametri per la funzione principale
							$tmpArray = array();
							for($k=0; $k<count($params); $k++){
								if($k<$i || $k>$j) $tmpArray[] = $params[$k];
								if($k==$i) $tmpArray[] = $value;
							}
							$params = $tmpArray; #array_splice($params, $i+1, $j-$i);
							break;
						}

					}
					
				}
				$c = preg_match('/([a-z0-9]+)\\((.*)\\)$/s', $value, $subItems);
				$subFnName = $subItems[1];
				$subParams = $subItems[2];
				
				$params[$i]= $this->parseFunction($subFnName, $subParams);
			}else if(
					substr($params[$i], 0, 1)=='"' || 
					substr($params[$i], 0, 1)=='\'' ||
					preg_match('/^\d+$/', $params[$i])){
						#Forse da qui non è necessario passare, verificare
						if(
							(substr($params[$i], 0, 1)=='"' &&  ((substr( strrev($params[$i]),0, 1)!= '"') || substr( strrev($params[$i]),1, 1)=='\\')) ||
							(substr($params[$i], 0, 1)=='\'' && ((substr( strrev($params[$i]),0, 1)!='\'') || substr( strrev($params[$i]),1, 1)=='\\')) ||
							(substr( strrev($params[$i]),0, 1)=='\\') ){
								if((substr( strrev($params[$i]),0, 1)=='\\')){
									$params[$i] =  substr($params[$i], 0, strlen($params[$i])-1) . ','.$params[$i+1];
									$params[$i+1] = null;
									$i = $i-1;
								}else{
									if(isset($params[$i+1])){
										$params[$i] .= $params[$i+1];
										$params[$i+1] = null;
										$i = $i-1;
									}
								}
								$tmpParams = $params;
								$params = array();
								$offset=0;
								for($j = 0; $j<count($tmpParams); $j++){
									
									if($tmpParams[$j]==null && $j>$i)			$offset += 1; 
													else						$params[$j-$offset] = $tmpParams[$j];
								}
								
							} 
						
				// è una stringa o un numero quindi devo passare il suo valore tramite eval
				if($params[$i]=='')$params[$i] = '""';
				$params[$i] = '$params[$i] = ' . $params[$i] . ';';
				eval($params[$i]);
				
			}else{
				$params[$i] = $this->getVar($params[$i]);
			}
		}
		
		$fnRoot = APPLICATION_CUSTOM_FUNCTION_BASEDIR .'/';
		if(!file_exists($fnRoot.$fnName . '.php')) $fnRoot = FUNCTIONSROOT;
		
		if(file_exists($fnRoot.$fnName . '.php')){
			$dbg->write('Including ' . $fnRoot.$fnName . '.php', DEBUG_REPORT_OTHER_DATA);
			require_once $fnRoot.$fnName . '.php';
			
			$dbg->write('Creating the class ' . $fnName . '()', DEBUG_REPORT_OTHER_DATA);
			$f = new $fnName();
			for($i=0;$i<count($params) ; $i++){
				$dbg->write('Adding parameter #' .$i . ': ' . $params[$i], DEBUG_REPORT_OTHER_DATA);
				$f->addParameter($params[$i]);
			}
				$dbg->write('Executing method '  .$fnName . '->execute();', DEBUG_REPORT_OTHER_DATA);
			$result = $f->execute();
			
		}else{
			applicationError(
				'Il metodo chiamato non è disponibile nelel funzioni custom o nelle funzioni core', 
				'&Egrave; stata definita nel modello una chiamata ad una funzione non esistente.', 
				$fnName, 
				'Verificare se il file di funzione è presente nel core (' . CORE_ROOT .'/functions/) oppure
				nella cartella dell\'applicazione (' . APPLICATION_CUSTOM_FUNCTION_BASEDIR . ')');
		}
		
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		return $result;
	}
	/**
	 * Imposta una serie di variabili sul modello corrente da un array associativo
	 * @param $array <b>array</b> array associativo corrispondente all'elenco di variabili da impostare 
	 * @param $prefix <b>string</b> <code>default = ''</code> se specificato alle variabili sarà applicato il prefisso nella forma <code>"prefisso.chiave"</code>  
	 * @return null
	 */
	function setMultipleVar($array, $prefix=''){
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		if($prefix!='') $prefix.='.';
		if(is_array($array)){
			foreach($array as $key => $value){
				$this->setVar($prefix.$key, $value);
			}
		}
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}
	/**
	 * Rimuove tutti gli header che dovranno essere passati al modello
	 * @return null
	 */
	function resetHeader(){
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		Model::$headers = array();
		Model::$headerScripts = array();
		Model::$startupScripts =array();
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}
	/**
	 * Rimuove tutte le variabili da fornire al modello
	 * @return unknown_type
	 */
	function clearAllVar(){
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		# Modifica del 26-02-2010 di Diego La Monica
		# $this->variables = null;
		self::$variables = null;
		# Fine Modifica
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}
	/**
	 * Rimuove tutte le variabili che hanno uno specifico prefisso. Se prefisso non viene specificato la funzione corrisponderà ad un alias del metodo <code>clearAllVar()</code>
	 * @param $prefix <b>string</b> il prefisso delle variabili da rimuovere
	 * @return null
	 */
	function clearVar($prefix = ''){
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		if($prefix=='') return $this->clearAllVar();
		# Modifica del 26-02-2010 di Diego La Monica
		# foreach($this->variables as $key => $value){
		#	if(substr($key,0, strlen($prefix)+1 )== $prefix .'.') unset($this->variables[$key]);
		# }
		foreach(self::$variables as $key => $value){
			if(substr($key,0, strlen($prefix)+1 )== $prefix .'.') unset(self::$variables[$key]);
		}
		# Fine Modifica
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}
	
	/**
	 * Metodo interno per la valutazione di una condizione
	 * @param $condition <b>string</b> codice PHP della condizione da valutare
	 * @param $var <b>any<b> Non usato: valore deprecato
	 * @return unknown_type
	 */
	private function evaluate($condition, $var){
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		if($condition!=''){
			$c = "\$___ModelEvaluationCondition= ($condition);";
			@eval($c);
		}
		
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		return isset($___ModelEvaluationCondition)?$___ModelEvaluationCondition:false;
		
	}
	
	/**
	 * Cerca la chiusura di un blocco strutturato CUTEML 
	 * @param $buffer <b>string</b> il buffer della vista corrente
	 * @param $item <b>string</b> l'elemento di inizio blocco dal quale partire
	 * @param $keyword_start <b>string</b> Keyword che identifica l'apertura del blocco 
	 * @param $keyword_end <b>string</b> Keyword che identifica la chiusura del blocco
	 * @return <b>string</b> il contenuto del blocco esclusi gli elementi di apertura e chiusura 
	 */
	private function endBlockSearch($buffer, $item , $keyword_start, $keyword_end){
		$dbg = ClassFactory::get('Debug');
		$dbg->setGroup(__CLASS__);
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		$i = strpos($buffer, $item);
		$j = strpos($buffer, '{' . $keyword_end . '}');
		$i = $i+ strlen($item);
		
		$block = substr($buffer, $i, $j- $i);
		$endLoopIndex	= preg_match_all('/{' . $keyword_end .'}/', $block, $matches);
		$startLoopIndex = preg_match_all('/{' . $keyword_start .':([^}]+)}/', $block, $matches);
		
		while($startLoopIndex!=$endLoopIndex){
			$j = strpos($buffer, '{' . $keyword_end . '}',$j+1);
			$block = substr($buffer, $i, $j- $i);
			$endLoopIndex	= preg_match_all('/{' . $keyword_end .'}/', $block, $matches);
			$startLoopIndex = preg_match_all('/{' . $keyword_start .':([^}]+)}/', $block, $matches);
		}
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		return $block;
	}
}

?>