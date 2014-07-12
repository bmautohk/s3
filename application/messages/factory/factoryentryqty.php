<?php 
return array
(
	'factory_entry_qty' => array (
			'not_empty' => '進倉數量  must not be empty.',
			'digit' => '進倉數量  must be integer.',
			'CustomValidation::positive' => '進倉數量 must be lager than 0.',
	),
);
