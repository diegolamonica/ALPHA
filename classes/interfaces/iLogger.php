<?php

interface iLogger{
	public function getLastEntry($idUser);
	public function getEntries($idUser = '', $source = '', $sourceKey = '', $field = '', $limit='');
	public function write($descrizione, $source = '', $sourceKey = '', $field = '', $from_value = '', $to_value = '');
	public function forceSource($source = null, $sourceKey = null);
}
?>