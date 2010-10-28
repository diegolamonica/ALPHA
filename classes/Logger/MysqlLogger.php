<?php
/**
 * @name MySql Logger
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
		$result = $c->getFirstRecord('select * from `log` where id_utente=' . $idUser . ' order by id desc');

		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		return $result;
	}

	function getEntries($idUser = '', $source = '', $sourceKey = '', $field = '', $limit=''){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		$c = ClassFactory::get('connector');
		$q = 'select ';
		$q .='log.*, u.' . AUTHENTICATION_FIELD_USERNAME . ' ';
		$q .='from `log` left join ' . USER_INFO_DATABASE_TABLE .'  u on id_utente = u.' . AUTHENTICATION_FIELD_TOKEN . ' ';
		$q .='where (1=1)';
		if ($idUser!='') $q .=' and id_utente="' . $idUser . '"';
		if($source!='') $q.=' and tabella_rif="' . $source .'"';
		if($sourceKey!='') $q.=' and id_tabella_rif="' . $sourceKey .'"';
		if($field!='') $q.=' and campo="' . $field . '"';
		$q.=' order by log.id desc';
		if($limit!='') $q.=' limit ' . $limit;

		$c->query($q);
		 
		while( $rs = $c->moveNext() ) $result[] = $rs;
		if(!isset($result)) $result = null;
		$c->release();
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
		return $result;
	}

	function write($descrizione, $source = '', $sourceKey = '', $field = '', $from_value = '', $to_value = ''){
		$dbg = ClassFactory::get('Debug');
		$dbg->write('Entering ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_ENTER);
		$dbg->writeFunctionArguments(func_get_args());
		
		// Se � stata forzata la tabella di memorizzazione
		if($this->_source!=null && $source!='log'){
			$field = '('.$source.')' . $field;
			$source = $this->_source;
			if($this->_sourceKey!=null) $sourceKey = $this->_sourceKey;
			 
		}
		
		if($source!='log'){
			
			$b = ClassFactory::get('Binder',true,'ConnectorLog');
			$b->setSource('log');
			$b->setDefaultValue('data', date('Y-m-d H:i:s'));
			$b->setDefaultValue('descrizione', $descrizione); 
			$b->bind('id_utente', 'SESSION', SESSION_USER_KEY_VAR);
			$b->setDefaultValue('tabella_rif', $source);
			$b->setDefaultValue('id_tabella_rif', $sourceKey);
			$b->setDefaultValue('campo', $field);
			$b->setDefaultValue('da_valore', $from_value);
			$b->setDefaultValue('a_valore', $to_value);
			$b->unbind('id');
			$b->save(true);
		}
		$dbg->write('Exiting ' . __FUNCTION__, DEBUG_REPORT_FUNCTION_EXIT);
	}
	
	public function forceSource($source = null, $sourceKey = null){
		 $this->_source = $source;
		 $this->_sourceKey = $sourceKey;
	}
}
?>