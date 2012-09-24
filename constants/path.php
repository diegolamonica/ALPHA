<?php
/**
 * Folder sul webserver sulla quale risiede l'applicazione
 * @var String
 */


$appRoot = findPathRecursive(array('appCreate.php', 'appSwitch.php', 'constants.php', 'default.php'));
HelperConstant::define('APPROOT',$appRoot,false,false,true);

/**
 * Percorso root del framework
 * @var String
 */
HelperConstant::define('ROOT',				$_SERVER['DOCUMENT_ROOT'] . APPROOT,false,false,true);

/**
 * Percorso dei modelli di interfaccia
 * @var String
 */
HelperConstant::define('VIEWROOT',			ROOT .'views/',false,false,true);
/**
 * Percorso per le funzioni custom utilizzate nei templates
 * @var String
 */
HelperConstant::define('FUNCTIONSROOT',		CORE_ROOT.'functions/',false,false,true);
/**
 * Percorso dei widget utilizzati nei templates
 * @var String
 */
HelperConstant::define('INPUTROOT',			CORE_ROOT.'input/',false,false,true);
/**
 * Percorso della cache utilizzato dalla procedura
 * @var String
 */
HelperConstant::define('CACHEROOT',			ROOT.'cache/',false,false,true);
?>
