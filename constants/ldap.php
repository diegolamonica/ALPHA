<?php
/**
 * Host LDAP al quale si intende richiedere l'autenticazione
 * @var String
 */
HelperConstant::define('LDAP_HOST', 'ldaps://192.168.0.1');

/**
 * Porta di comunicazione accettata dal server LDAP
 * @var Integer
 */
HelperConstant::define('LDAP_PORT', NULL);

/**
 * Il dominio di connessione in forma canonica
 * @var unknown_type
 */
HelperConstant::define('LDAP_BASEDN', "dc=host, dc=domain, dc=tld");


/**
 * Issue #28: The LDAP class defines LOG Level to maximum for openLDAP
 * Sets the DEBUG_OPT_ERROR_LEVEL option value
 * @var Integer 
 */
HelperConstant::define('LDAPT_DEBUG_LEVEL', 0);

?>