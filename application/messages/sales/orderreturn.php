<?php 
return array
(
		'product_cd' => array (
				'not_empty' => '商品番号  must not be empty.',
		),
		'return_qty' => array (
				'not_empty' => '返品数量 must not be empty.',
				'digit' => '返品数量  must not be integer.',
				'CustomValidation::positive' => '返品数量 must be larger than 0.',
		),
		'return_pay' => array (
				'not_empty' => '返品金額  must not be empty.',
				'numeric' => '返品金額  must not be numeric.',
				'CustomValidation::positive' => '返品金額 must be larger than 0.',
		),
);
