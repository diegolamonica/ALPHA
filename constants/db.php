<?php
/**
 * Nome utente autorizzato ad accedere all'istanza del database
 * @var String
 */
HelperConstant::define('CONNECTOR_USERNAME',	'');
/**
 * Password (in chiaro) dell'utente specificato in {@link CONNECTOR_USERNAME}
 * @var String
 */
HelperConstant::define('CONNECTOR_PASSWORD',	'');
/**
 * Host sul quale risiede l'istanza del database
 * @var String
 */
HelperConstant::define('CONNECTOR_HOST',		'');
/**
 * Istanza del database al quale bisogna connettersi
 * @var unknown_type
 */
HelperConstant::define('CONNECTOR_INSTANCE',	'');

HelperConstant::define('SQL_CHARSET', 		'utf8');
HelperConstant::define('SQL_COLLATION', 		'utf8_general_ci');

HelperConstant::define('SQL_TABLE_PREFIX', 		'');
HelperConstant::define('SQL_TABLE_POSTFIX',		'');
HelperConstant::define('SQL_FIELD_PREFIX',		'');
HelperConstant::define('SQL_FIELD_POSTFIX',		'');
?>