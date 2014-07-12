<?php defined('SYSPATH') or die('No direct script access.');
 
class Model_DeliveryMethod extends ORM {
	const ID_OTHER = 7;
	
	public $_table_name = 'delivery_method';

	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"description" => array("type" => "string"),
	);
	
	public static function getOptions() {
		$session = Session::instance();
		$options = $session->get('SESSION_DELIVERY_METHOD_OPTIONS', NULL);
		
		if ($options == NULL) {
			$deliveryMethods = ORM::factory('deliveryMethod')->find_all();
			
			$options = $deliveryMethods->as_array('id', 'description');
			
			$session->set('SESSION_DELIVERY_METHOD_OPTIONS', $options);
		}
		
		return $options;
	}
}