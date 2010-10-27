<?php


_define('MODEL_KEYWORD_PHP_BLOCK_START',	'php');
_define('MODEL_KEYWORD_PHP_BLOCK_END',		'phpend');

/**
 * Parola chiave per l'apertura di un blocco da ciclare.
 * <code>
 * {foreach:item}
 * 		...
 * {loop}
 * </code>
 * @var String
 */
_define('MODEL_KEYWORD_LOOP_START', 	'foreach');
/**
 * Parola chiave per la chiusura di un blocco da ciclare.
 * <code>
 * {foreach:item}
 * 		...
 * {loop}
 * </code>
 * @var String
 */
_define('MODEL_KEYWORD_LOOP_END', 	'loop');
/**
 * Parola chiave che precede una variabile sul template
 * @var String
 */
_define('MODEL_KEYWORD_VAR',			'var');
/**
 * Parola chiave per impostare da modello il valore di una variabile
 * @var String
 */
_define('MODEL_KEYWORD_SETVAR',			'setvar');
/**
 * Parloa chiave che identifica l'inizio di un blocco condizionale
 * <code>
 * {if: <i>inline php block code</i>}
 * {endif}
 * @var String
 * @see MODEL_KEYWORD_IF_END
 */
_define('MODEL_KEYWORD_IF_START',	'if');

_define('MODEL_KEYWORD_IFVAR_START','ifv');

/**
 * Parloa chiave che identifica il termine di un blocco condizionale
 * <code>
 * {if: <inline php block code>}
 * {endif}
 * @var String
 * @see MODEL_KEYWORD_IF_START
 */
_define('MODEL_KEYWORD_IF_END',		'endif');
/**
 * Parloa chiave che identifica un blocco di codice PHP (esecuzione inline).
 * @var String
 */
_define('MODEL_KEYWORD_PHP',			'php');
/**
 * Parola chiave che identifica una funzione custom
 * @var String
 * @see FUNCTIONSROOT
 */
_define('MODEL_KEYWORD_FUNCTION',	'fn');
/**
 * Parola chiave che identifica l'inclusione di un file.
 * L'output generato dal file incluso verrà elaborato e
 * sostituirà l'include.
 * La differenza tra l'uso di include e include-static è
 * nel fatto che include richiede la presenza del file sul server
 * corrente, perchè lo script viene eseguito nel contesto applicativo
 * e quindi è in grado di modificare tutte le proprietà in uso.
 * Diversamente static viene eseguito in una sandbox quindi non ha 
 * possibilità di alterare le variabili di processo attuale. 
 * @var String
 */
_define('MODEL_KEYWORD_INCLUDE',		'include');
/**
 * Parola chiave che identifica l'inclusione di un file statico.
 * Tipicamente è un file html che può risiedere sullo stesso 
 * server applicativo o su un server remoto
 * @var String
 */
_define('MODEL_KEYWORD_INCLUDE_STATIC',	'include-static');

_define('MODEL_KEYWORD_REDIRECT',		'redirect');


global $formatArray;
$formatArray = array(
	'FROM_EURO_INT_VAL' => 	'(\d*)(\d{2})$',
	'TO_EURO_DEC_VAL'	=>	'\1,\2',
	'FROM_SQL_DATE'		=>	'(\d+)\-(\d+)\-(\d+)',
	'TO_DMY_DATE'		=>	'$3/$2/$1'
);


?>