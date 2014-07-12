<?php
class Model_Factory_EntryForm {
	public $order_product_id;
	public $factory_entry_qty;
	
	public $orderProduct;
	public $factoryEntryHistory;
	
	public $errors;
	
	public function __construct($factory, $order_product_id = NULL) {
		$this->factory = $factory;
		$this->order_product_id = $order_product_id;
	}
	
	public function populate($post) {
		if ($this->order_product_id == NULL) {
			$this->order_product_id = isset($post['order_product_id']) ? $post['order_product_id'] : NULL;
		}
	
		$this->factory_entry_qty = isset($post['factory_entry_qty']) ? $post['factory_entry_qty'] : NULL;
	}
	
	public function processSaveAction() {
		$result = $this->save();
		$this->initData();
	
		return $result;
	}
	
	public function initData() {
		// Find order product
		$this->orderProduct = ORM::factory('orderProduct')
								->with('order')
								->with('productMaster')
								->join('customer')->on('customer.id', '=', 'order.customer_id')
								->select('customer.cust_code')
								->where('factory_status', '>=', Model_OrderProduct::STATUS_FACTORY)
								->where('factory', '=', Model_OrderProduct::getFactoryCode($this->factory))
								->where('orderproduct.id', '=', $this->order_product_id)
								->find();
	
		// Find container history
		$this->factoryEntryHistory = ORM::factory('factoryEntryQty')
								->where('order_product_id', '=', $this->order_product_id)
								->order_by('create_date')
								->find_all();
	}
	
	private function save() {
		$db = Database::instance();
		$db->begin();
	
		try {
			// Add entry QTY history
			$history = new Model_FactoryEntryQty();
			$history->order_product_id = $this->order_product_id;
			$history->factory_entry_qty = $this->factory_entry_qty;
			$history->created_by = Auth::instance()->get_user()->username;
			
			// Valiation
			$history->check();
			
			$orderProduct = new Model_OrderProduct($this->order_product_id);
			if ($this->factory_entry_qty > $orderProduct->factory_qty - $orderProduct->factory_entry_qty - $orderProduct->factory_delivery_qty) {
				$this->errors[] = '進倉數量 > kaito staff 分貨qty - 已進倉數量 - 已出貨數量';
				return false;
			}
			
			$history->save();
				
			// Update order product's entry qty
			$orderProduct->factory_entry_qty = $orderProduct->factory_entry_qty + $this->factory_entry_qty;
			$orderProduct->save();
			
			// Clear form
			$this->factory_entry_qty = '';
		} catch (ORM_Validation_Exception $e) {
			$db->rollback();
			foreach ($e->errors('factory') as $error) {
				$this->errors[] = $error;
			}
			return false;
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}

		$db->commit();

		return true;
	}
}