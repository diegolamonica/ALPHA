<?php

define('FILEMANAGER_ERROR_NO_ERROR', 			0);
define('FILEMANAGER_ERROR_NO_RIGHT_NAME', 		1);
define('FILEMANAGER_ERROR_NO_RIGHT_FILE_NAME', 	2);
define('FILEMANAGER_ERROR_NO_RIGHT_EXTENSION', 	3);
define('FILEMANAGER_ERROR_INVALID_MIN_SIZE', 	4);
define('FILEMANAGER_ERROR_INVALID_MAX_SIZE', 	5);

define('FILEMANAGER_PROGRESSIVE_UPLOAD_SESSION_VAR', 'fmpusv');
class FileRule{
	
	public $fsName = null;
	public $fileName = null; 
	public $sizeMin = null;
	public $sizeMax = null;
	public $extension = null;
	
}

class FileManager extends Debugger{
	/**
	 * 
	 * @var unknown_type
	 */
	private $errors = array(
		FILEMANAGER_ERROR_NO_ERROR				=> '',
		FILEMANAGER_ERROR_NO_RIGHT_NAME 	 	=> 'Il nome del file specificato non è corretto',
		FILEMANAGER_ERROR_NO_RIGHT_FILE_NAME 	=> 'Il nome del file specificato non è valido',
		FILEMANAGER_ERROR_NO_RIGHT_EXTENSION 	=> 'L\'estensione indicata non è consentita',
		FILEMANAGER_ERROR_INVALID_MIN_SIZE 		=> 'La dimensione del file è troppo piccola',
		FILEMANAGER_ERROR_INVALID_MAX_SIZE 		=> 'La dimensione del file è troppo grande'
	);
	private $folder;
	private $fileName;
	private $currentFileStructure;
	private $lastError;
	
	private $fileInfo;
	
	private $watcher = '';
	private $watcherFileName = '___FileManagerUploadMonitor.dat';
	private $currentUploadingFile = false;
	private $_rules = array();
	/**
	 * 
	 * @return unknown_type
	 */
	function getLastError(){
		return $this->errors[$this->lastError];
	}
	
	/**
	 * 
	 * @return unknown_type
	 */
	function getUploadingFileSize() {
		return ($this->currentUploadingFile === false ? false : filesize($this->currentUploadingFile));
	}
	
	/**
	 * 
	 * @return unknown_type
	 */
	function watchProgressiveUpload(){
		$tempDir = ini_get('upload_tmp_dir');
		$this->watcher = $tempDir.$this->watcherFileName;
		if(isset($_SESSION[FILEMANAGER_PROGRESSIVE_UPLOAD_SESSION_VAR]) && file_exists($_SESSION[FILEMANAGER_PROGRESSIVE_UPLOAD_SESSION_VAR]))
			$this->currentUploadingFile = $_SESSION[FILEMANAGER_PROGRESSIVE_UPLOAD_SESSION_VAR];
		else
			$this->currentUploadingFile = $this->getCurrentUploadingFile($tmpfolder, '/[p][h][p]*');
		if($this->currentUploadingFile !== false)
				$_SESSION[FILEMANAGER_PROGRESSIVE_UPLOAD_SESSION_VAR] = $this->currentUploadingFile;
		
	}
	/**
	 * 
	 * @param unknown_type $tmpfolder
	 * @param unknown_type $pattern
	 * @return unknown_type
	 */
	private function getCurrentUploadingFile($tmpfolder, $pattern) {
		$found = false;
		if(is_dir($tmpfolder)) {
			$phptempfiles = glob($tmpfolder.$pattern);
			if(count($phptempfiles) === 1) {
				if(@$fp = fopen($this->watcherFileName, 'w')) {
					@flock($fp, LOCK_EX);
					fwrite($fp, serialize($phptempfiles));
					@flock($fp, LOCK_UN);
					fclose($fp);
				}
				$found = $phptempfiles[0];
			}
			else
				$found = $this->checkUploadWatcher($phptempfiles);
		}
		return $found;
	}
	/**
	 * 
	 * @param $phptempfiles
	 * @return unknown_type
	 */
	function checkUploadWatcher(&$phptempfiles) {
		$found = false;
		if(file_exists($this->watcher)) {
			$fsize = filesize($this->watcher);
			if(@$fp = fopen($this->watcher, 'r+')) {
				@flock($fp, LOCK_EX);
				$tmpfiles = unserialize(fread($fp, $fsize));
				$tmpfound = array_diff_assoc($phptempfiles, $tmpfiles);
				if(is_array($tmpfound) && count($tmpfound) === 1) {
					foreach($tmpfound as $k => $v)
						$found = &$v;
					rewind($fp);
					fwrite($fp, serialize($phptempfiles));
				}
				@flock($fp, LOCK_UN);
				fclose($fp);
			}
		}
		return $found;
	}
	/**
	 * 
	 * @param unknown_type $file_post
	 * @return unknown_type
	 */
	function rearrangeFileObject(&$file_post) {
	
	    $file_array = array();
	    $file_count = count($file_post['name']);
	    $file_keys = array_keys($file_post);
		if(is_array($file_post['name'])){
		    for ($i=0; $i<$file_count; $i++) {
		        foreach ($file_keys as $key) {
		            $file_array[$i][$key] = $file_post[$key][$i];
		        }
		    }
		}else{
			$file_array = $file_post;
		}
	    return $file_array;
	}
	/**
	 * 
	 * @return unknown_type
	 */
	public function FileManager(){
		/*
		 * Initializing the default variables
		 */
		$this->folder = FILEMANAGER_DEFAULT_FOLDER;
		$this->fileName = FILEMANAGER_DEFAULT_FILENAME_EXPRESSION;
	}
	/**
	 * 
	 * @param unknown_type $file
	 * @param unknown_type $rules
	 * @return unknown_type
	 */
	public function defineRule($file, $rules){
		/*
		 * Create a rule for the file upload validation
		 */
		if($file==null) $file = '.#.GLOBAL.#.';
		if(!isset($this->_rules[$file])){
			$this->_rules[$file] = array($rules);
		}else{
			$this->_rules[$file][] = $rules;
		}
	}
	/**
	 * 
	 * @param unknown_type $rule
	 * @param unknown_type $value
	 * @param unknown_type $caseSensitive
	 * @return unknown_type
	 */
	public function checkRule($rule, $value, $caseSensitive = true){
		if($rule == null || $rule =='') return true; 
		if(!is_array($rule)) $rule = array($rule);
		for($i =0; $i < count($rule); $i++){
			
			if( ($rule[$i] == $value) || 
				(!$caseSensitive && (strtoupper($rule[$i]) == strtoupper($value)) )) return true;
			
		}
		return false;
		
	}
	public function check($file, $f){
		if(!isset($this->_rules[$file])) $file ='.#.GLOBAL.#.';
		if(!isset($this->_rules[$file])) return true;
		$errorCode = FILEMANAGER_ERROR_NO_ERROR;
		foreach($this->_rules[$file] as $rule){
			
			
			if(!$this->checkRule($rule->fsName,		$f['name'])) 			$errorCode = FILEMANAGER_ERROR_NO_RIGHT_NAME;
			if(!$this->checkRule($rule->fileName, 	$f['fileName'])) 	$errorCode = FILEMANAGER_ERROR_NO_RIGHT_FILE_NAME;
			if(!$this->checkRule($rule->extension,	$f['extension'], false)) 	$errorCode = FILEMANAGER_ERROR_NO_RIGHT_EXTENSION;
			
			if($rule->sizeMin	!=null 	&& $rule->sizeMin>$f['size']) 			$errorCode = FILEMANAGER_ERROR_INVALID_MIN_SIZE;
			if($rule->sizeMax	!=null 	&& $rule->sizeMax<$f['size']) 			$errorCode = FILEMANAGER_ERROR_INVALID_MAX_SIZE;
			if($errorCode!=FILEMANAGER_ERROR_NO_ERROR){
				$this->lastError = $errorCode;
				return false;
			}
		}
		return true;
	}
	
	public function setFolder($folder){
		$this->folder = $folder;
	} 
	
	public function setFileName($fileName){
		$this->fileName = $fileName;
		#echo("setting file name to: $fileName");
	}
	
	public function save($beforeSaveMethod = '', $afterSaveMethod = ''){
		/*
		 * F%xxx%; = $_FILES['xxx']
		 * G%xxx%; = $_GET['xxx']
		 * P%xxx%; = $_POST['xxx']
		 * C%xxx%; = $_COOKIE['xxx']
		 * S%xxx%; = $_SESSION['xxx']
		 * X%ext%; = Original File Extension (full data after last dot)
		 * X%name%; = Original File Name Without extension (full data before last dot)
		 * X%now%;	= data e ora corrente nel formato YYYYMMDDHHMMSS
		 */
			
		foreach($_FILES as $item => $structure){
			/*
			 * Rebulid each $_FILES item to have a much more usable 
			 * structure. It comes useful when we are managing array of files
			 */
			$structure = $this->rearrangeFileObject($structure);
			$this->currentFileStructure = $structure;
			$fileName= strrev( $structure['name'] );
			$i = strpos($fileName,'.');
			$fileExtension = strrev(substr($fileName, 0, $i));
			$fileName = strrev(substr($fileName, $i+1));
			/*
			 * Here we add two extra information to the default file structure:
			 * extension (pdf) and the file name without extension and path
			 */
			$structure['extension'] = $fileExtension;
			$structure['fileName'] = $fileName;
			
			/*
			 * Only if the validation against the specified rules for that file passes
			 * we will proceed to the saving 
			 */
			if($this->check($item, $structure)){
				$save = true;
				/**
				 * if the $beforeSaveMethod is passed to the function and
				 * it exists then we must call it.
				 */
				if($beforeSaveMethod!='' && function_exists($beforeSaveMethod)) $save = $beforeSaveMethod($item, $structure);
				/**
				 * We expect that the $beforeSaveMethod returns a boolean value (true or false)
				 */
				if($save){
					/*
					 * We build the fileInfo array and using it with the replacement callback
					 * to generate the right filename for that uploaded file. 
					 */
					$this->fileInfo = array('ext' => $fileExtension, 'name' => $fileName, 'now'=>date('YmdHis'));
					$fn = preg_replace_callback('/(C|F|G|P|S|X)\%([^%]+)\%\;/i', array(&$this,'replace'),  $this->fileName );
					/*
					 * If no filename was given we set the filename to its original file name.
					 */
					if($fn=='') $fn = $fileName;
					/*
					 * Then moving the uploaded file to the right folder
					 */
					move_uploaded_file($structure['tmp_name'], $this->folder . $fn);
					
					/*
					 * And after we call the $afterSaveMethod if passed to the function (and exists).
					 */
					if($afterSaveMethod!='' && function_exists($afterSaveMethod)) $afterSaveMethod($item, $structure, $fn); 
				}
			}
		
		}
	}
	
	public function replace($matches){
		#echo('matches: ');print_r($matches); echo('<br />');
		switch(strtoupper($matches[1])){
			case 'F':				$structure = $this->currentFileStructure; 	break;
			case 'G':				$structure = $_GET; 						break;
			case 'P':				$structure = $_POST; 						break;
			case 'C':				$structure = $_COOKIES; 					break;
			case 'S':				$structure = $_SESSION; 					break;
			case 'V':				$structure = $_SERVER; 						break;
			case 'X':				$structure = $this->fileInfo; 				break;
		}
		#echo('structure: ');print_r($structure);echo('<br />');
		
		#echo('resultset: ');print_r($structure[$matches[2]]); echo('<br />');
		
		return $structure[$matches[2]];
		
	}
	
}
?>