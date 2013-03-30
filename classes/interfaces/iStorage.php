<?php
interface iStorage{
	
	public function write($key, $value);
	
	/**
	 * Read the value of an item. If the item is non-existing the method will return an empty string value
	 * @param $key the key to obtain value 
	 */
	public function read($key);
	
	/**
	 * Removes the given key(s) from storage. 
	 * The function accepts either string, array or list of arguments.
	 * 
	 * @param string|Array|ParamArray $key the key to remove or the list of keys (as array) to remove.
	 *  
	 */
	public function destroy($key = '');
	public function debug();
	
}
?>