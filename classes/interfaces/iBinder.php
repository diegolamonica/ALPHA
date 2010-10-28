<?php
interface iBinder{
	public function setSource($sourceName);
	public function querySource($query);
	public function bind($field, $source, $key);
	public function setDefaultValue($field, $value);
	public function getBindedValue($field);
	public function unbind($field, $source = '', $key = '');
	public function find();
	public function save($force = true);
}
?>