<?php
class Model_Warehouse_BorrowForm {
	public $action;
	public $order_product_id;
	
	public $orderProduct;
	public $warehouseBorrow;
	public $borrowHistory;
	public $errors;
	
	public function __construct($order_product_id) {
		$this->order_product_id = $order_product_id;
	}
	
	public function populate($post) {
		$this->action = isset($post['action']) ? $post['action'] : NULL;

		$this->warehouseBorrow = new Model_WarehouseBorrow();
		$this->warehouseBorrow->values($post);
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
			$this->warehouseBorrow->borrow_date = date('Y-m-d');
			$this->warehouseBorrow->created_by = Auth::instance()->get_user()->username;
			$this->warehouseBorrow->create_date = DB::expr('current_timestamp');
			
			// Validation
			// Check whether active container exists (not submit to delivery note yet)
			$container = ORM::factory('container')
						->where('order_product_id', '=', $this->order_product_id)
						->where('status', '=', Model_Container::STATUS_INIT)
						->order_by('create_date')
						->find();
			
			if (!$container->loaded()) {
				$this->errors[] = 'No item is ready for submitting to 入金管理';
				return false;
			}
			
			if ($container->delivery_qty + $this->warehouseBorrow->qty > $container->orig_delivery_qty) {
				$this->errors[] = '實際交貨數量['.$container->delivery_qty.'] + 借出數量['.$this->warehouseBorrow->qty.'] > 工場交貨數量['.$container->orig_delivery_qty.']';
				return false;
			}
			
			$this->warehouseBorrow->container_id = $container->id;
			
			// Update container's delivery QTY
			$container->delivery_qty += $this->warehouseBorrow->qty;
			
			// Update borrow_qty in order_product
			$orderProduct = ORM::factory('orderProduct')
							->with('order')
							->where('orderproduct.id', '=', $this->order_product_id)
							->find();
			$orderProduct->warehouse_borrow_qty += $this->warehouseBorrow->qty;
			
			// Save to DB
			$this->warehouseBorrow->save();
			$container->save();
			$orderProduct->save();
			
			// Clear form
			$this->warehouseBorrow = new Model_WarehouseBorrow();
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
		
		$this->borrowHistory = ORM::factory('warehouseBorrow')
							->where('order_product_id', '=', $this->order_product_id)
							->find_all();
	}
}