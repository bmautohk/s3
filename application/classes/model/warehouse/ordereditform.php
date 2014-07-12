<?php
class Model_Warehouse_OrderEditForm {	
	public $order_id;
	public $delivery_method;
	public $errors;
	
	public function populate($post) {
		$this->order_id = isset($post['order_id']) ? $post['order_id'] : NULL;
		$this->delivery_method = isset($post['delivery_method']) ? $post['delivery_method'] : NULL;
	}
	
	public function retrieve($order_id) {
		$this->order_id = $order_id;
		$order = new Model_Order($this->order_id);
		$this->delivery_method = $order->delivery_method;
	}
	
	public function saveAction() {
		return $this->save();
	}
	
	private function save() {
		$this->errors = array();
		
		$db = Database::instance();
		$db->begin();
		
		try {
			$order = new Model_Order($this->order_id);
			$order->delivery_method = $this->delivery_method;
			$order->save();
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		
		return true;
	}
}
