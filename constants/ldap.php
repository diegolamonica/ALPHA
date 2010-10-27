<?php
/**
 * Host LDAP al quale si intende richiedere l'autenticazione
 * @var String
 */
_define('LDAP_HOST', 'ldaps://192.168.0.1');

/**
 * Porta di comunicazione accettata dal server LDAP
 * @var Integer
 */
_define('LDAP_PORT', NULL);

/**
 * Il dominio di connessione in forma canonica
 * @var unknown_type
 */
_define('LDAP_BASEDN', "dc=host, dc=domain, dc=tld");

?>