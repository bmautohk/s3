<?php defined('SYSPATH') or die('No direct script access.');

class Model_Order extends ORM {
	public $_table_name = 'order';
	
	const STATUS_ACTIVE = 'A';
	const STATUS_VOID = 'V';
	const STATUS_COMPLETE = 'C';
	
	const KAITO_YES = "Y";
	const KAITO_NO = "N";
	
	protected $_has_many = array('orderProducts' => array('model' => 'orderProduct', 'foreign_key' => 'order_id'));
	
	protected $_belongs_to = array('order_type' => array('model'=>'orderType', 'foreign_key'=>'order_type_id'),
									'customer' => array('model'=>'customer', 'foreign_key'=>'customer_id'));
	
	protected $_table_columns = array(
			"id" => array("type" => "int"),
			"customer_id" => array("type" => "int"),
			"order_type_id" => array("type" => "int"),
			"delivery_date" => array("type" => "date"),
			"s1_client_name" => array("type" => "string"),
			"tel" => array("type" => "string"),
			"postal_code" => array("type" => "string"),
			"remark" => array("type" => "string"),
			"delivery_address1" => array("type" => "string"),
			"delivery_address2" => array("type" => "string"),
			"delivery_address3" => array("type" => "string"),
			"delivery_method_id" => array("type" => "int"),
			"delivery_method" => array("type" => "string"),
			"deposit_amt" => array("type" => "double"),
			"rmb_to_jpy_rate" => array("type" => "double"),
			"rmb_to_usd_rate" => array("type" => "double"),
			"picture1" => array("type" => "string"),
			"picture2" => array("type" => "string"),
			"picture3" => array("type" => "string"),
			"status" => array("type" => "string"),
			"order_date" => array("type" => "timestamp"),
			"confirm_deposit_amt" => array("type" => "double"),
			"is_kaito" => array("type" => "string"),
			"created_by" => array("type" => "string"),
			"create_date" => array("type" => "timestamp"),
			"last_updated_by" => array("type" => "string"),
	);
	
	public function rules() {
		return array(
				'customer_id' => array(
						array('not_empty')
				),
				'order_type_id' => array(
						array('not_empty')
				),
				'delivery_date' => array(
						array('not_empty')
				),
				'deposit_amt' => array(
						array('numeric')
				),
		);
	}
	
	public function save(Validation $validation = NULL) {
		$this->last_updated_by = Auth::instance()->get_user()->username;
		return parent::save($validation);
	}
	
	public function updateStatus() {
		$isAllComplete = true;
		$orderProducts = $this->orderProducts->find_all();
		foreach ($orderProducts as $orderProduct) {
			if ($orderProduct->jp_status != Model_OrderProduct::STATUS_COMPLETE
					|| $orderProduct->factory_status != Model_OrderProduct::STATUS_COMPLETE) {
				$isAllComplete = false;
				break;
			}
		}
		
		if ($isAllComplete) {
			$this->status = self::STATUS_COMPLETE;
			$this->save();
		}
	}
	
	public static function getOrderStatusOptions() {
		return array('A'=>'未完成', 'V'=> 'Void', 'C'=>'完成');
	}
	
	public static function getDisplayDeliveryMethod($deliveryMethodDescription, $remark) {
		$description = $deliveryMethodDescription;
		if (trim($remark) != '') {
			$description .= ' - '.$remark;
		}
		return $description;
	}
}