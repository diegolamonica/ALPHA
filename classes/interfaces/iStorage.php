<?php
interface iStorage{
	
	public function write($key, $value);
	public function read($key);
	public function destroy($key = '');
	public function debug();
	
}
?>