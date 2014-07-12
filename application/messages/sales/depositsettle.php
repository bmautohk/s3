<?php 
return array
(
		'settle_amt' => array (
				'not_empty' => '入金  must not be empty.',
				'numeric' => '入金 must not be integer.',
				'CustomValidation::positive' => '入金 must be larger than 0.',
		),
		'fee' => array (
				'not_empty' => '送金手數費 must not be empty.',
				'numeric' => '送金手數費 must not be integer.',
				'CustomValidation::minValue' => '送金手數費 must be equal to or larger than 0.',
		),
		'settle_date' => array (
				'not_empty' => '入金日期 must not be empty.',
		),
		'bank_name' => array (
				'not_empty' => '銀行名字 must not be empty.',
		),
);
