<?php
/**
 * @name Costanti
 * @author Diego La Monica <me@diegolamonica.info>
 * @version 1.0
 * @desc Costanti necessarie ai fini applicativi
 * @package ALPHA
 */



require_once CORE_ROOT. 'includes/debugging.php';

$tmpScriptPath = $_SERVER['DOCUMENT_ROOT']. $_GET['__url']; //$_SERVER['SCRIPT_FILENAME'];
$tmpScriptPath = preg_replace('/\/[^\/]+$/i','/', $tmpScriptPath);

define('CURRENT_SCRIPT_PATH', $tmpScriptPath);
# Applicaton Paths Constants
includeConstantFrom('path.php');

# Applicaton Settings Constants
includeConstantFrom('application.php');
# Applicaton Settings Constants
includeConstantFrom('ldap.php');
# Database settings constants
includeConstantFrom('db.php');

# Authentication settings constants
includeConstantFrom('authentication.php');

# Model constants - DO NOT CHANGHE!!! -
includeConstantFrom('model.php');
# Debugg constants
includeConstantFrom('debug.php');
# Pagination constants
includeConstantFrom('pagination.php');
# Search constants
includeConstantFrom('searcher.php');

# Error presentation constants
includeConstantFrom('error.php');

# Error presentation constants
includeConstantFrom('filemanager.php');

#includeFrom('', 'local.functions.php');

?>