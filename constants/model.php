<?php
/**
 * @abstract all model constants are moved to the class file Model.php 
 * @Since 2011-05-04 
 */

global $formatArray;
$formatArray = array(
	'FROM_EURO_INT_VAL' => 	'(\d*)(\d{2})$',
	'TO_EURO_DEC_VAL'	=>	'\1,\2',
	'FROM_SQL_DATE'		=>	'(\d+)\-(\d+)\-(\d+)',
	'TO_DMY_DATE'		=>	'$3/$2/$1'
);
?>