<?php
/**
 * @name OCI Logger
 * @version 1.0
 * @author Diego La Monica
 * @desc Classe che si preoccupa del logging delle operazioni. Questa classe nello specifico scrive i dati in una tabella log su mysql
 * @package ALPHA
 */
/**
 *
 */
require_once CORE_ROOT. 'classes/interfaces/iLogger.php';
class CustomLogger extends Debugger implements iLogger {
	
	private $_source = null;
	private $_sourceKey = null;

	function getLastEntry($idUser){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		$c = ClassFactory::get('connector');
		$result = $c->getFirstRecord("select * from LOG where ID_UTENTE='$idUser' order by ID desc");

		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		return $result;
	}

	function getEntries($idUser = '', $source = '', $sourceKey = '', $field = '', $limit=''){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		$c = ClassFactory::get('connector');
		$q = 'select ';
		$q .='L.*, TO_CHAR(L.DATA, \'DD/MM/YYYY HH:MI:SS\') AS DATA_FMT, U.' . AUTHENTICATION_FIELD_USERNAME . ' ';
		$q .='from LOG L,' . PEOPLE_DATABASE_TABLE .'  U ';
		$q .='where U.'.AUTHENTICATION_FIELD_TO_STORE .' = L.ID_UTENTE';
		if ($idUser!='') $q .=" and L.ID_UTENTE='$idUser'";
		if($source!='') $q.=" and L.TABELLA_RIF='$source'";
		if($sourceKey!='') $q.=" and L.ID_TABELLA_RIF='$sourceKey'";
		if($field!='') $q.=" and L.CAMPO='$field'";
		if($limit!='') $q.=' and '. $c->getLimitClause(-1, $limit);
		$q.=' order by L.ID desc';
		
		$c->query($q);
		 
		while( $rs = $c->moveNext() ) $result[] = $rs;
		if(!isset($result)) $result = null;
		$c->release();
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		return $result;
	}

	function write($descrizione, $source = '', $sourceKey = '', $field = '', $from_value = '', $to_value = ''){
		#flush();
		
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		// Se è stata forzata la tabella di memorizzazione
		if($this->_source!=null && $source!='log'){
			$field = '('.$source.')' . $field;
			$source = $this->_source;
			if($this->_sourceKey!=null) $sourceKey = $this->_sourceKey;
			 
		}
		if($source!='log'){
			
			$b = ClassFactory::get('Binder',true,'ConnectorLog');
			$b->setSource('log');
			$b->setDefaultValue('data', date('d/m/Y H:i:s'));
			$b->setDefaultValue('descrizione', $descrizione); 
			$b->bind('id_utente', 'SESSION', SESSION_USER_KEY_VAR);
			$b->setDefaultValue('tabella_rif', $source);
			$b->setDefaultValue('id_tabella_rif', $sourceKey);
			$b->setDefaultValue('campo', $field);
			#echo("$from_value - $to_value");
			$b->setDefaultValue('da_valore',   $from_value);
			# Modifica di Diego La Monica
			# del 2010-04-15
			# Motivo: Per poter inviare il simbolo dell'euro correttamente a Oracle devo 
			# convertire il simbolo euro (€) nel controvalore unicode (hex: 20AC) e utilizzare
			# la funzione oracle unistr per la conversione di codici unicode in utf8 quindi
			# poichè in questa fase mi trovo che ha già convertito in unicode, devo riportarlo
			# al valore originale, per un corretto confronto con l'informazione già presente
			# nel DB.
			$to_value = str_replace("'||unistr('\\20AC')||'", '€', $to_value);
			# Fine modifica
			$b->setDefaultValue('a_valore', str_replace("''","'",$to_value));
			$b->unbind('id');
			
			#global $printSQL;
			
			#$printSQL=true;
			$b->save(true);
			#$printSQL=false;
		}
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}
	
	public function forceSource($source = null, $sourceKey = null){
		 $this->_source = $source;
		 $this->_sourceKey = $sourceKey;
	}
}
?>