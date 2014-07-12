<?php 
return array
(
	'rate_from' => array (
		'not_empty' => 'Rate From must not be empty.',
	),
	'rate_to' => array (
		'not_empty' => 'Rate To must not be empty.',
	),
	'date_from' => array (
		'not_empty' => 'Date From must not be empty.',
	),
	'date_to' => array (
		'not_empty' => 'Date To must not be empty.',
		'Model_Rate::checkDate' => 'Date From must less than or equal to Date To.',
		'Model_Rate::checkDateOverlap' => 'The rate of this period has already been set.',
	),
	'rate' => array (
		'not_empty' => 'Rate must not be empty.',
		'numeric' => 'Rate must be decimal.',
	),
);
