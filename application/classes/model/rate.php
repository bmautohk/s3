<?php defined('SYSPATH') or die('No direct script access.');

class Model_Rate extends ORM {
	public $_table_name = 'rate';
	
	const RATE_FROM_RMB = 'RMB';
	const RATE_TO_JPY = 'JPY';
	const RATE_TO_USD = 'USD';
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"rate_from" => array("type" => "string"),
			"rate_to" => array("type" => "string"),
			"date_from" => array("type" => "date"),
			"date_to" => array("type" => "date"),
			"rate" => array("type" => "double"),
	);
	
	public function rules() {
		return array(
			'rate_from' => array(
						array('not_empty')
					),
			'rate_to' => array(
						array('not_empty')
					),
			'date_from' => array(
						array('not_empty')
					),
			'date_to' => array(
						array('not_empty'),
						array('Model_Rate::checkDate', array(':validation', 'date_from', 'date_to')),
						array('Model_Rate::checkDateOverlap', array(':validation', 'rate_from', 'rate_to', 'date_from', 'date_to'))
					),
			'rate' => array(
						array('not_empty'),
						array('numeric')
					),
		);
	}
	
	public static function checkDate($array, $from, $to) {
		return $array[$from] <= $array[$to];
	}
	
	public static function checkDateOverlap($array, $rate_from, $rate_to, $date_from, $date_to) {
		$count = ORM::factory('rate')
					->where('rate_from', '=', $array[$rate_from])
					->where('rate_to', '=', $array[$rate_to])
					->where('date_from', '<=', $array[$date_to])
					->where('date_to', '>=', $array[$date_from])
					->count_all();
		
		return $count == 0;
	}
	
	public static function getCurrentRate($rate_from, $rate_to) {
		// Get RMB <-> JPY rate
		$today = date('Y-m-d');
		$rate = ORM::factory('rate')
					->where('rate_from', '=', $rate_from)
					->where('rate_to', '=', $rate_to)
					->where('date_from', '<=', $today)
					->where('date_to', '>=', $today)
					->find();
		
		return $rate->loaded() ? $rate : NULL;
	}

}