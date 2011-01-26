<?php
/**
 * @package ALPHA
 * @version 1.0
 * @author Diego La Monica
 * @desc Si preoccupa di istanziare le classi necessarie al funzionamento del framework.
 */

class ClassFactory {
	public static $classes = array();
	/**
	 * Include e richiama la classe il cui nome è passato come parametro se non è stata ancora allocata.

	 * @param string $className la classe da allocare
	 * @param boolean $recreate se la classe non esiste in base a questo valore verrà creata o meno una nuova istanza
	 * @param string $name alias da attribuire alla classe. Se non è specificato l'alias della classe corrisponderà esattamente al nome della classe <b>ClassName</b>
	 * @return object
	 */
	static function get($className, $recreate = true, $name = ''){
		
		if($name == '') $name = $className;
		
		if($className!='Debug'){
			$dbg = ClassFactory::get('Debug');
			$dbg->setGroup('ClassFactory');
			$dbg->write('Getting class "' . $name .':' .$className . '"', DEBUG_REPORT_OTHER_DATA);
		}
		$result = null;
		if(array_key_exists($name,ClassFactory::$classes)) $result = ClassFactory::$classes[$name];
		if($result == null && $recreate){
			if($className!='Debug'){
				$dbg = ClassFactory::get('Debug');
				$dbg->write(($recreate?'Rec':'C') . 'reating Class "' . $className . '"', DEBUG_REPORT_OTHER_DATA, FirePHP_INFO);
			}	
			if($className!='Debug') $dbg->write('Class '  .$className . ' does not exists. Create and store as "' . $name . '"' , DEBUG_REPORT_CLASS_CONSTRUCTION);
			$result = ClassFactory::create($className, $name);
			$result = $result['instance'];
		}else{
			if($className!='Debug' && $result==null){
				$dbg = ClassFactory::get('Debug');
				$dbg->setGroup('ClassFactory');
				$dbg->write('Class "' . $className . '" not found', DEBUG_REPORT_OTHER_DATA);
				$dbg->setGroup('');
			}
			if($result!=null) $result = $result['instance'];
		}
		
		return $result;
	}
	
	static function set($alias, $object){
		if(array_key_exists($alias, ClassFactory::$classes)){
			ClassFactory::destroy($alias);
		}
		ClassFactory::$classes[$alias] = array('type'=>$alias, 'instance'=> $object );
		return true;
	}
	
	/**
	 * Crea un oggetto basato sull'istanza di <b>$className</b> memorizzandolo con l'alias <b>$name</b>
	 * 
	 * @param string $className nome della classe di cui creare un istanza
	 * @param string $name nome da attribuire all'istanza di classe per future refereziazioni
	 * @return object
	 */
	static function create($className, $name){
		
		require_once($className .'.php');
		$obj = new $className();
		ClassFactory::$classes[$name] = array('type' => $className , 'instance' =>& $obj);
		
		$result = ClassFactory::$classes[$name];
		return $result;
	}
	/**
	 * Distrugge una singola istanza o tutte le istanze identificate dal valore <b>$className</b>
	 * @param string $className	il nome della classe o l'alias da deallocare 
	 * @param boolean $byType se true, verranno deallocate tutti gli oggetti che sono istanza della classe <b>$className</b>
	 */
	static function destroy($className, $byType=false){
		if($className!='Debug'){
			$dbg = ClassFactory::get('Debug');
			$dbg->write('Destroying class "' . $className . '"', DEBUG_REPORT_OTHER_DATA);
		}
		foreach(ClassFactory::$classes as $name => $info ){
			if($className== $name && !$byType){
				unset(ClassFactory::$classes[$name]);
				break;
			}else if($byType && $info['type']==$className){
				unset(ClassFactory::$classes[$name]);
			}
		}
	}
	
	
	static function destroyAll(){
		foreach(ClassFactory::$classes as $name => $info ){
			unset(ClassFactory::$classes[$name]);
		}
	}
}
?>