<?php 
return array
(
	'amount' => array (
			'not_empty' => '輸入經費  must not be empty.',
			'digit' => '合計請求金額 must be integer.',
			'CustomValidation::positive' => '輸入經費 must be lager than 0.',
	),
	'fee' => array (
			'not_empty' => '送金手數費  must not be empty.',
			'numeric' => '送金手數費 must be numeric.',
			'CustomValidation::positive' => '送金手數費 must be lager than 0.',
	),
	'input_date' => array (
			'not_empty' => '入金日期  must not be empty.',
	),
);
