<?php
/**
 * USAGE:
 * 
 * Run php -f <directory-to-this-script>/make.php <new-application-name>
 * 
 * 
 * 
 */

function writeErrorMessage($msg, $displayUsage = false){
	if($msg!='')
	fwrite(STDOUT, "
-----------------------------------------------------------
Error: $msg
");
	
	if($displayUsage){
		fwrite(STDOUT, "
-----------------------------------------------------------
Usage:
php -f " . __FILE__ . " application-name  
");
	}	
	if($displayUsage || $msg!='')
fwrite(STDOUT, "-----------------------------------------------------------
");
	
}

function createDirectory($under, $dirName){
	$theDirectory = "$under/$dirName";
	fwrite(STDOUT, "Creating directory $dirName in $under...");
	mkdir($theDirectory);
	fwrite(STDOUT, "and setting permission...");
	chmod($theDirectory, 0777);
	fwrite(STDOUT, "Done\n");
	
}

function createFileFromTemplate($destinationFile, $templateFile, $permissions = 0777){
	global $arguments;
	fwrite(STDOUT, "Creating file $destinationFile ");
	if($templateFile!=null){
		$buffer= file_get_contents($templateFile);
		foreach($arguments as $i => $value){
			$buffer = str_replace( "%PARAM$i%",  $value, $buffer);
		}
	}else{
		$buffer = '';
	}
	file_put_contents($destinationFile, $buffer);
	fwrite(STDOUT, "and setting permission...");
	chmod($destinationFile, $permissions);
	fwrite(STDOUT, "Done\n");
}

if($argc!=2){
	
	writeErrorMessage('Missing arguments', true);
	
}else{
	
	$scriptDir = dirname(__FILE__);
	$parentDir = dirname($scriptDir);
	
	fwrite(STDOUT, "Please enter the core framework working dir [leave empty if: $parentDir]: ");
	$workingDir = trim(fgets(STDIN));
	if($workingDir!= ''){

		$parentDir = $workingDir;
	}
	
	$arguments = array();
	foreach($argv as $key=>$value)
		$arguments[$key] = $value;
	$arguments[] = $scriptDir; // 2 
	$arguments[] = $parentDir; // 3
	
	$appName = $argv[1];
	
	if(!preg_match('/[a-z0-9\-_\+]/i',$appName)){
		writeErrorMessage('Invalid argument only alphanumeric and "_-+" chars are allowed');
		exit();
	}
	if(!isset($_SERVER['PWD'])){
		fwrite(STDOUT, "Please enter the working dir (upper directory in which build the application): ");
		$workingDir = trim(fgets(STDIN));
		if(!file_exists($workingDir)){
			writeErrorMessage('Invalid Working dir, directory does not exists');
			exit();
		}
	}else{
		$workingDir = $_SERVER['PWD'];
	}	
	
	createDirectory($workingDir, $appName);
	createDirectory("$workingDir/$appName", 'app');
	createDirectory("$workingDir/$appName/app", 'controller');
	createDirectory("$workingDir/$appName/app", 'views');
	createDirectory("$workingDir/$appName/app", 'input');
	createDirectory("$workingDir/$appName/app", 'functions');
	createDirectory("$workingDir/$appName/app", 'cache');
	
	createFileFromTemplate("$workingDir/$appName/.htaccess", $scriptDir .'/.htaccess');
	createFileFromTemplate("$workingDir/$appName/application.xml", $scriptDir .'/application.xml');
	createFileFromTemplate("$workingDir/$appName/index.php", $scriptDir .'/index.php');
	createFileFromTemplate("$workingDir/$appName/app/local.functions.php", null);
	createFileFromTemplate("$workingDir/$appName/app/controller/index.php", $scriptDir .'/helloworld.php');
	createFileFromTemplate("$workingDir/$appName/app/views/example.htm", $scriptDir .'/example.htm');
}
?>