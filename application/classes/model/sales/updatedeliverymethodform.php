<?php
class Model_Sales_UpdateDeliveryMethodForm {
	public $order_id;
	public $delivery_method_id;
	public $delivery_method;
	
	public $errors;
	
	public function populate($post) {
		$this->order_id = isset($post['order_id']) ? $post['order_id'] : NULL;
		$this->delivery_method_id = isset($post['delivery_method_id']) ? $post['delivery_method_id'] : NULL;
		$this->delivery_method = isset($post['delivery_method']) ? $post['delivery_method'] : NULL;
	}
	
	public function init() {
		$order = new Model_Order($this->order_id);
		$this->delivery_method_id = $order->delivery_method_id;
		$this->delivery_method = $order->delivery_method;
	}
	
	public function processSaveAction() {
		$order = new Model_Order($this->order_id);
		$order->delivery_method_id = $this->delivery_method_id;
		$order->delivery_method = $this->delivery_method;
		
		$db = Database::instance();
		$db->begin();
		
		$this->errors = array();
		try {
			$order->save();
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			echo Debug::vars($this->errors);
			return false;
		}
		
		$db->commit();
		
		return true;
	}
	
	public function getCurrentDeliveryMethod() {
		$deliveryMethod = new Model_DeliveryMethod($this->delivery_method_id);
		
		return Model_Order::getDisplayDeliveryMethod($deliveryMethod->description, $this->delivery_method);
	}
}

?>