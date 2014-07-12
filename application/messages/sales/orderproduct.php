<?php 
return array
(
	'product_cd' => array (
		'not_empty' => 'Partno  must not be empty.',
	),
	'qty' => array (
		'not_empty' => '数量  must not be empty.',
		'digit' => '数量 must be integer.',
	),
	'market_price' => array (
		'not_empty' => '売値  must not be empty.',
	),
	'delivery_fee' => array (
		'not_empty' => '國內送料  must not be empty.',
		'numeric' => '國內送料  must be numeric.', 
	),
);
