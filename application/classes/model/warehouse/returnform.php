<?php
class Model_Warehouse_ReturnForm {
	public $action;
	public $order_product_id;
	
	public $orderProduct;
	public $warehouseReturn;
	public $returnHistory;
	public $errors;
	
	public function __construct($order_product_id) {
		$this->order_product_id = $order_product_id;
	}
	
	public function populate($post) {
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		
		$this->warehouseReturn = new Model_WarehouseReturn();
		$this->warehouseReturn->values($post);
	}

	public function process() {
		if (isset($this->action) && $this->action == 'save') {
			$this->save();
		}
		
		$this->init();
	}
	
	private function save() {
		$db = Database::instance();
		$db->begin();
		
		$this->errors = array();
	
		try {
			$this->warehouseReturn->return_date = date('Y-m-d');
			
			// Save to DB
			$this->warehouseReturn->save();
			
			// Update return_qty in order_product
			$orderProduct = ORM::factory('orderProduct')
							->with('order')
							->where('orderproduct.id', '=', $this->order_product_id)
							->find();
			$orderProduct->warehouse_return_qty += $this->warehouseReturn->qty;
			$orderProduct->save();
			
			// Clear form
			$this->warehouseReturn = new Model_WarehouseReturn();
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return;
		}
	
		$db->commit();
	}
	
	private function init() {
		$this->orderProduct = ORM::factory('orderProduct')
							->with('order')
							->where('orderproduct.id', '=', $this->order_product_id)
							->find();
		
		$this->returnHistory = ORM::factory('warehouseReturn')
							->where('order_product_id', '=', $this->order_product_id)
							->find_all();
	}
}