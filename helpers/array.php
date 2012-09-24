<?php
class HelperArray{
	/**
	 * Remove keys from the given array passed by reference
	 * @param array $array the source array where to remove the keys
	 * @param array $keys the keys to remove from the base array 
	 */
	public static function unsetFields(&$array, $keys){
		foreach($items as $key){
			unset($array[$key]);
		}
	}
	
	/**
	 * 
	 * Find if one of the specified item of the destination array is into the source array, 
	 * if the destination array is empty or has one empty string elelemt will return the 
	 * $ifDestIsEmptyReturn value
	 *  
	 * @param array $arraySrc the array to look into for
	 * @param array $arrayDst the array which has the values to match int the $arraySrc
	 * @param any $ifDestIsEmptyReturn (optional default `true`)
	 */
	public static function oneOfIsIn(array $arraySrc,array $arrayDst, $ifDestIsEmptyReturn = true){
		if(count($arrayDst)==0 || (count($arrayDst)==1 && $arrayDst[0]=='') ) return $ifDestIsEmptyReturn;
		for($i = 0; $i<count($arraySrc );$i++){
			
			if($arraySrc[$i]!=''){
				if(array_search($arraySrc[$i],$arrayDst,true)!==false){
	
					return true;
				}
			}
			
		}
	
		return false;
	}

	/**
	 * will convert the given XML text to an array in the XML structure.
	 * 
	 * @param string $contents the XML text
	 * @return array The parsed XML in an array form. Use print_r() to see the resulting array structure.
	 * 
	 * @example $array =  xml2array(file_get_contents('feed.xml'));
	 */
	public static function xml2array($contents) {
		require_once(CORE_ROOT . '/classes/Xml2array.php');
		$x = new Xml2array();
		$x->fromString($contents);
		return $x->parse();
	}
		
}