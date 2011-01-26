<?php
/**
 * @name Costanti
 * @author Diego La Monica <me@diegolamonica.info>
 * @version 1.0
 * @desc Costanti necessarie ai fini applicativi
 * @package ALPHA
 */


if(!defined('OUTPUT_DEBUG_INFO') || OUTPUT_DEBUG_INFO) require_once CORE_ROOT. 'includes/debugging.php';


/**
 * Changed $_GET['__url'] in REQUESTED_URL introduced in /index.php
 */
$tmpScriptPath = $_SERVER['DOCUMENT_ROOT']. REQUESTED_URL; //$_SERVER['SCRIPT_FILENAME'];
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

# issue #23
# - Temporary location for firephp constants. -
# In a next release those constants need to be relocated out of there.
define('FirePHP_WARN', 'WARN');
define('FirePHP_DUMP', 'DUMP');
define('FirePHP_ERROR', 'ERROR');
define('FirePHP_EXCEPTION', 'EXCEPTION');
define('FirePHP_INFO', 'INFO');
define('FirePHP_LOG', 'LOG');
define('FirePHP_GROUP_END', 'GROUP_END');
define('FirePHP_GROUP_START', 'GROUP_START');
define('FirePHP_TABLE', 'TABLE');
define('FirePHP_TRACE', 'TRACE');
?>