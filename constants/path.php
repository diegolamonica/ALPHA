<?php
/**
 * Folder sul webserver sulla quale risiede l'applicazione
 * @var String
 */


$appRoot = findPathRecursive(array('appCreate.php', 'appSwitch.php', 'constants.php', 'default.php'));
_define('APPROOT',$appRoot,false,false,true);

/**
 * Percorso root del framework
 * @var String
 */
_define('ROOT',				$_SERVER['DOCUMENT_ROOT'] . APPROOT,false,false,true);

/**
 * Percorso dei modelli di interfaccia
 * @var String
 */
_define('VIEWROOT',			ROOT .'views/',false,false,true);
/**
 * Percorso per le funzioni custom utilizzate nei templates
 * @var String
 */
_define('FUNCTIONSROOT',		CORE_ROOT.'functions/',false,false,true);
/**
 * Percorso dei widget utilizzati nei templates
 * @var String
 */
_define('INPUTROOT',			CORE_ROOT.'input/',false,false,true);
/**
 * Percorso della cache utilizzato dalla procedura
 * @var String
 */
_define('CACHEROOT',			ROOT.'cache/',false,false,true);
?>
