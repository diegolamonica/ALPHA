<?php
$storage = ClassFactory::get('Storage');
#$s = $_SESSION['authentication_user_token'];
#$s = unserialize($s);
$s = $storage->read('authentication_user_token');

if(!isset($s['userRolesDeactivated'])) $s['userRolesDeactivated'] = Array();

if(isset($_GET['add']) || isset($_GET['del'])){
	if(isset($_GET['add'])){
		array_push($s['userRoles'], $_GET['add']);
		$idx = array_search($_GET['add'], $s['userRolesDeactivated']);
		unset($s['userRolesDeactivated'][$idx]);
		$s['userRolesDeactivated'] = array_values($s['userRolesDeactivated']);
	}
	
	if(isset($_GET['del'])){
		array_push($s['userRolesDeactivated'], $_GET['del']);
		$idx = array_search($_GET['del'], $s['userRoles']);
		unset($s['userRoles'][$idx]);
		$s['userRoles'] = array_values($s['userRoles']);
	}
	
	$appId = $s['applicationID'];
	$storage->write('authentication_user_token',$s );
	$multiAppsLogon = $storage->read('multi_apps_logon');
	$multiAppsLogon[$appId] = serialize($s);
	$storage->write('multi_apps_logon',$multiAppsLogon );
	
	#$_SESSION['authentication_user_token'] = serialize($s);
	#$_SESSION['multi_apps_logon'][$appId] = serialize($s);
	
}
if(isset($_GET['destroy'])){
	$storage->destroy();
	#session_destroy();
	#unset($_SESSION);
}
$j = ClassFactory::get('Json');
$roles = Array(
	$s['userRoles'],
	$s['userRolesDeactivated']
);

echo $j->fromObject($roles);



?>
