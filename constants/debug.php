<?php
/**
 * Costante che indica di non creare alcuna informazione di debug.
 * Tale informazione deve essere utilizzata <b>esclusivamente</b> in sistemi stabili di produzione.
 * @var Integer
 */
define('DEBUG_REPORT_NONE', 0);
/**
 * Costante che, se passata all'istanza della classe di debug, consente di tracciare quando vengono richiamati
 * i costruttori di classe. 
 * @var Integer
 */ 
define('DEBUG_REPORT_CLASS_CONSTRUCTION', 	1);
/**
 * Costante che, se passata all'istanza della classe di debug, consente di tracciare quando vengono richiamati
 * i distruttori di classe.
 * @var Integer
 */	
define('DEBUG_REPORT_CLASS_DESTRUCTION', 	2);

/**
 * Costante che, consente di monitorare la chiamata dei costruttori e dei distruttori di una classe.
 * è utile adoperare questa configurazione in ambienti di staging per verificare le performances della procedura
 * ormai ad uno stadio finale e controllare in che modo vengono allocate e deallocate le classi. 
 * @var Integer
 * @see DEBUG_REPORT_CLASS_CONSTRUCTION, DEBUG_REPORT_CLASS_DESTRUCTION
 */
define('DEBUG_REPORT_CLASS_INFO',			DEBUG_REPORT_CLASS_CONSTRUCTION+
											DEBUG_REPORT_CLASS_DESTRUCTION);	# of classes (performance monitoring)
													# useful in a staging system to check
													# the correct system resources.

/**
 * Costante cheidentifica traccia l'uscita da una funzione.
 * @var Integer
 */
define('DEBUG_REPORT_FUNCTION_EXIT', 		4);
/**
 * Costante che traccia l'ingresso in una funzione.
 * @var Integer
 */
define('DEBUG_REPORT_FUNCTION_ENTER', 		8);
/**
 * Costante che traccia tutti i parametri passati a una funzione
 * @var Integer
 */
define('DEBUG_REPORT_FUNCTION_PARAMETERS', 	16);
/**
 * Costante che traccia tutti i dettagli di una funzione: ingresso, parametri passati e uscita.
 * E' suggerito utilizzare questa impostazione per ambienti di sviluppo.
 * <b>Nota:</b> è sconsigliato utilizzare questo metodo in ambienti di produzione in quanto pregiudica 
 * le performance della procedura.   
 * @var Integer
 * @see DEBUG_REPORT_FUNCTION_ENTER, DEBUG_REPORT_FUNCTION_EXIT, DEBUG_REPORT_FUNCTION_PARAMETERS
 */
define('DEBUG_REPORT_CLASS_FUNCTION_INFO',	DEBUG_REPORT_FUNCTION_EXIT+			# To debug details about function 
											DEBUG_REPORT_FUNCTION_ENTER+		# Entering, Exiting and Parameters list
											DEBUG_REPORT_FUNCTION_PARAMETERS);	# In a developement envrironment should
																				# be useful.
/**
 * Costante che traccia tutte le informazioni aggiuntive identificate come ALTRO.
 * @var Integer
 */																				
define('DEBUG_REPORT_OTHER_DATA', 			32);								

/**
 * Costante che descrive un tracciamento totale delle operazioni svolte dal framework.
 * Un'impostazione del genere causa delle gravi perdite di performance del sistema, queindi è altamente sconsigliato.
 * Lo scopo è per un debug maniacale e bisogna disporre di un hardware adeguato a sostenere la grossa mole di chiamate
 * alle procedure di debug e della scrittura su File System di un file di log.  
 * @var Integer
 * @see DEBUG_REPORT_CLASS_INFO, DEBUG_REPORT_CLASS_FUNCTION_INFO, DEBUG_REPORT_OTHER_DATA
 */
define('DEBUG_REPORT_ALL', 	DEBUG_REPORT_CLASS_INFO+							# For maniacal purpose.
								DEBUG_REPORT_CLASS_FUNCTION_INFO+
								DEBUG_REPORT_OTHER_DATA);

/**
 * Costante che indica il livello di Debug applicato
 * @var Integer
 */
#define('DEBUG_REPORT_LEVEL', DEBUG_REPORT_CLASS_FUNCTION_INFO+DEBUG_REPORT_CLASS_INFO);	
#_define('DEBUG_REPORT_LEVEL', DEBUG_REPORT_NONE,false,false,true);
#define('DEBUG_REPORT_LEVEL', DEBUG_REPORT_NONE,false,false,true);
#_define('DEBUG_REPORT_LEVEL', DEBUG_REPORT_CLASS_INFO+DEBUG_REPORT_FUNCTION_ENTER+DEBUG_REPORT_OTHER_DATA,false,false,true);
								
/**
 * Identifica il nome del file di debug generato su file system
 * @var String
 */
date_default_timezone_set(APPLICATION_TIMEZONE);
_define('DEBUG_FILE_NAME',	date('YmdHm') . '@' . $_SERVER['REMOTE_ADDR']. '.txt',false,false,true);
/**
 * Identifica il folder nel quale verranno messi i file di debug.
 * @var String
 */
_define('DEBUG_FILE_PATH',	CORE_ROOT . 'debugging/',false,false,true);

/**
 * Variabile per la notifica di errore presenti nella classe debugger
 */

_define('ERROR_FUNCTION_NOT_FOUND', 'Funzione non definita nel contesto');

?>