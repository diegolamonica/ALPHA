<?php
/**
 * Nome della pagina di autenticazione
 * @var String
 */
HelperConstant::define('AUTHENTICATION_LOGIN_PAGE',					APPROOT . 'login.php');

/**
 * Nome della variabile di sessione che dovrà mantenere l'id dell'utente autenticato
 * @var String
 */
HelperConstant::define('SESSION_USER_KEY_VAR',						'authentication_user_id');
/**
 * Nome della variabile di sessione che dovrà conservare il token assegnato all'utente 
 * relativamente alla sessione corrente
 * @var String
 */
HelperConstant::define('SESSION_USER_TOKEN_VAR',					'authentication_user_token');
/**
 * Nome del campo della tabella dal quale verrà prelevato lo {@link SESSION_USER_KEY_VAR user_id} 
 * @var String
 */
HelperConstant::define('AUTHENTICATION_FIELD_TO_STORE',				'id');
/**
 * Nome del campo della tabella sul quale è scritto il nome utente 
 * @var String
 */
HelperConstant::define('AUTHENTICATION_FIELD_USERNAME', 			'username');
/**
 * Nome del campo della tabella sul quale è scritta la password dell'utente
 * @var String
 */
HelperConstant::define('AUTHENTICATION_FIELD_PASSWORD', 			'password');
/**
 * Nome del campo della tabella sul quale verrà scritto il token relativo alla sessione corrente.
 * Lo stesso token verrà mantenuto in {@link SESSION_USER_TOKEN_VAR} una variabile di sessione.
 * @var String
 */
HelperConstant::define('AUTHENTICATION_FIELD_TOKEN', 				'token');
/**
 * Nome della tabella che contiene le informazioni sugli utenti 
 * @var String
 */
HelperConstant::define('AUTHENTICATION_DATABASE_TABLE', 			'utenti');

/**
 * Costante che definisce il metodo di conservazione in chiaro della password utente sulla tabella. 
 * @var String
 */
HelperConstant::define('AUTHENTICATION_PASSWORD_ENCODING_PLAIN',	'plain');
/**
 * Costante che definisce il metodo di conservazione in formato MD5 della password utente sulla tabella. 
 * @var String
 */
HelperConstant::define('AUTHENTICATION_PASSWORD_ENCODING_MD5',		'md5');
/**
 * Costante che definisce il metodo di conservazione in formato SHA-1 della password utente sulla tabella. 
 * @var String
 */
HelperConstant::define('AUTHENTICATION_PASSWORD_ENCODING_SHA',		'sha');

/**
 * Costante che indica in che modo è memorizzata la password dell'utente sulla tabella.
 * Essa può assumere tre valori:
 * - {@link AUTHENTICATION_PASSWORD_ENCODING_PLAIN in chiaro}
 * - {@link AUTHENTICATION_PASSWORD_ENCODING_MD5 con algoritmo MD5}
 * - {@link AUTHENTICATION_PASSWORD_ENCODING_SHA1 con algoritmo SHA-1}
 * @var String
 */
HelperConstant::define('AUTHENTICATION_PASSWORD_ENCODING',		'AUTHENTICATION_PASSWORD_ENCODING_PLAIN', false, true);
/**
 * Costante di autenticazione che indica la corretta autenticazione dell'utente
 * @var integer
 */
HelperConstant::define('AUTHENTICATION_ERROR_OK',						0);
/**
 * Costante di autenticazione che indica un errore nel nome utente o nella password indicati in fase di login
 * @var integer
 */
HelperConstant::define('AUTHENTICATION_ERROR_WRONG_USER_OR_PASSWORD',	1);
/**
 * Costante di autenticazione che indica un errore nel token presente in sessione
 * @var integer
 */
HelperConstant::define('AUTHENTICATION_ERROR_INVALID_TOKEN',			2);
/**
 * Costante di autenticazione che indica un errore nell'id mantenuto in sessione
 * @var integer
 */
HelperConstant::define('AUTHENTICATION_ERROR_INVALID_KEY',				4);

HelperConstant::define('AUTHENTICATION_ERROR_LDAP_SERVER_UNAVAILABLE',	8);

HelperConstant::define('AUTHENTICATION_ERROR_LDAP_NO_SEARCH_RESULTS',	16);

HelperConstant::define('AUTHENTICATION_ERROR_WRONG_PASSWORD',			32);
HelperConstant::define('AUTHENTICATION_ERROR_WRONG_USER',				64);

HelperConstant::define('AUTHENTICATION_ERROR_XS_NO_RECORD_FOUND',		128);

?>