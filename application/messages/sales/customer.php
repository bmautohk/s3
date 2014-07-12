<?php 
return array
(
	'name' => array (
		'not_empty' => '公司名字  must not be empty.',
	),
	'cust_code' => array (
		'not_empty' => '代號cust code must not be empty.',
		'unique' => '代號cust code must be unique.',
	),
);
