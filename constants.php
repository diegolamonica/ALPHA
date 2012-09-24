<?php
/**
 * @name Costants
 * @author Diego La Monica <me@diegolamonica.info>
 * @desc Required application Costants
 * @package ALPHA
 */


/*
 * Accidental removal of the version number in the latest commit 306d9bfbb49d7cdf987657cc763027ddc1411969
 * https://github.com/diegolamonica/ALPHA/commit/306d9bfbb49d7cdf987657cc763027ddc1411969#diff-6
 */ 
#define('ALPHA_FRAMEWORK_VERSION','1.2.1');
#define('ALPHA_FRAMEWORK_VERSION','1.3.0');
#define('ALPHA_FRAMEWORK_VERSION','1.3.1');
define('ALPHA_FRAMEWORK_VERSION','1.3.2');

/*
 * Removed a php_notice
 */
if(!defined('OUTPUT_DEBUG_INFO') || defined('OUTPUT_DEBUG_INFO') && OUTPUT_DEBUG_INFO) require_once CORE_ROOT. 'includes/debugging.php';


/**
 * Changed $_GET['__url'] in REQUESTED_URL introduced in /index.php
 */
$tmpScriptPath = $_SERVER['DOCUMENT_ROOT']. REQUESTED_URL; //$_SERVER['SCRIPT_FILENAME'];
$tmpScriptPath = preg_replace('/\/[^\/]+$/i','/', $tmpScriptPath);

define('CURRENT_SCRIPT_PATH', $tmpScriptPath);

# Event handling constants
require_once CORE_ROOT .'constants/events.php';

# Applicaton Paths Constants
require_once CORE_ROOT .'constants/path.php';

# Applicaton Settings Constants
require_once CORE_ROOT .'constants/application.php';
# LDAP Settings Constants
require_once CORE_ROOT .'constants/ldap.php';

# Database settings constants
require_once CORE_ROOT .'constants/db.php';

# Authentication settings constants
require_once CORE_ROOT .'constants/authentication.php';

# Model constants - DO NOT CHANGHE!!! -
require_once CORE_ROOT .'constants/model.php';

# Debugg constants
require_once CORE_ROOT .'constants/debug.php';

# Pagination constants
require_once CORE_ROOT .'constants/pagination.php';

# Search constants
require_once CORE_ROOT .'constants/searcher.php';

# Error presentation constants
require_once CORE_ROOT .'constants/error.php';

# Error presentation constants
require_once CORE_ROOT .'constants/filemanager.php';

# FirePHP Debugging constants
require_once CORE_ROOT .'constants/firephp.php';


# Storage handling constants
require_once CORE_ROOT .'constants/storage.php';


?>
