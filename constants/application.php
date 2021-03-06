<?php

HelperConstant::define('APPLICATION_TIMEZONE', 'Europe/Rome',false,false,true);
/**
 * Durata massima in giorni della cache di elaborazione
 * @var Integer
 */
HelperConstant::define('CACHE_MAX_DURATION_DAYS', 0);
/**
 * nome della pagina predefinita alla quale si viene rediretti dopo il login
 * @var String
 */
HelperConstant::define('DEFAULT_PAGE',			'index.php');
/**
 * Nome di base della vista in caso non  venga specificata.
 * Questa informazione è usata ai fini della gestione della cache.
 * @var String
 */
HelperConstant::define('DEFAULT_VIEW_NAME',		'unnamed');
/**
 * URL completo dal quale è raggiungibile l'applicazione
 * @var string
 */
HelperConstant::define('APPLICATION_URL',		'http://localhost/alpha/');

HelperConstant::define('APPLICATION_AUTHENTICATION_MODULE_LDAP', 'LDAP',false, false, true);
HelperConstant::define('APPLICATION_AUTHENTICATION_MODULE_MYSQL', 'Mysql',false, false, true);
HelperConstant::define('APPLICATION_AUTHENTICATION_MODULE_XS', 'XS',false, false, true);

HelperConstant::define('APPLICATION_CONNECTOR_MODULE_MYSQL', 'Mysql',false, false, true);
HelperConstant::define('APPLICATION_CONNECTOR_MODULE_ORACLE', 'Oci8',false, false, true);

HelperConstant::define('APPLICATION_LOGGER_MODULE_MYSQL', 	'Mysql',false, false, true);
HelperConstant::define('APPLICATION_LOGGER_MODULE_OCI', 	'Oci8',false, false, true);
HelperConstant::define('APPLICATION_LOGGER_MODULE_SILENT', 'Silent',false, false, true);

HelperConstant::define('APPLICATION_AUTHENTICATION_MODULE', 	'APPLICATION_AUTHENTICATION_MODULE_LDAP',false, true);
HelperConstant::define('APPLICATION_CONNECTOR_MODULE', 		'APPLICATION_CONNECTOR_MODULE_ORACLE',false, true);
HelperConstant::define('APPLICATION_LOGGER_MODULE', 			'APPLICATION_LOGGER_MODULE_SILENT',false, true);
?>