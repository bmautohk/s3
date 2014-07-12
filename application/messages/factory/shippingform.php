<?php 
return array
(
		'delivery_qty' => array (
				'not_empty' => '交貨數量  must not be empty.',
				'digit' => '交貨數量  must be integer.',
				'CustomValidation::positive' => '交貨數量 must be lager than 0.',
		),
		'delivery_date' => array (
			'not_empty' => '予定交貨日期  must not be empty.',
		),
		'container_input_date' => array (
			'not_empty' => '入櫃日期  must not be empty.',
		),
		'container_no' => array (
				'not_empty' => '櫃號  must not be empty.',
		),
		
);
