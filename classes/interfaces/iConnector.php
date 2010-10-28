<?php
interface iConnector{
	public function isConnected();
	public function connect($host, $db, $user, $password);
	public function query($sql, $empty = false);
	public function moveNext();
	public function getId();
	public function getCount(); 
	public function release($resource = null);
	public function getFirstRecord($sql);
	public function getLastError();
	public function allResults();
	
	public function describeTable($tableName);
	public function processDataValue($value);
}
?>