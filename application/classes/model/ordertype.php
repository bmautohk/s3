<?php defined('SYSPATH') or die('No direct script access.');
 
class Model_OrderType extends ORM {
	public $_table_name = 'order_type';
	
	const ID_ORDER = 1; // 訂單
	const ID_CLAIM = 2; // claim
	const ID_SAMPLE = 3; // sample
	const ID_RETAIL = 4; // 零售
	const ID_MONOPOLY = 5; // 專賣品
	const ID_MONOPOLY_RETAIL = 6; // 専売零售
	const ID_KAITO = 7; // Kaito
	const ID_STOCK = 8; // 在庫処分
	const ID_TEMP = 9; // 一次性訂單
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"description" => array("type" => "string"),
	);
	
	public static function getOptions($hasAll = false) {
		$session = Session::instance();
		$options = $session->get('SESSION_ORDER_TYPE_OPTIONS', NULL);
		
		if ($options == NULL) {
			$orderTypes = ORM::factory('orderType')->find_all();
			
			$options = $orderTypes->as_array('id', 'description');
			
			$session->set('SESSION_ORDER_TYPE_OPTIONS', $options);
		}
		
		if ($hasAll) {
			$allOption = array(0=>'All');
			$options = array_merge($allOption, $options); 
		}
		
		return $options;
	}
}