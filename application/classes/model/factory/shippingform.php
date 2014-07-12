<?php
class Model_Factory_ShippingForm {
	public $action;
	public $order_product_id;
	public $is_accept;
	public $container;
	public $factory_remark;
	
	public $orderProduct;
	public $containerHistory;
	
	public $errors;
	
	public function __construct($factory, $order_product_id = NULL) {
		$this->factory = $factory;
		$this->order_product_id = $order_product_id;
	}
	
	public function populate($post) {
		if ($this->order_product_id == NULL) {
			$this->order_product_id = isset($post['order_product_id']) ? $post['order_product_id'] : NULL;
		}
		
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->is_accept = isset($post['is_accept']) ? $post['is_accept'] : NULL;
		$this->factory_remark = isset($post['factory_remark']) ? $post['factory_remark'] : NULL;
		
		$this->container = new Model_Container();
		if (isset($post['container'])) {
			$this->container->values($post['container']);
		}
	}
	
	public function retrieveAction() {
		$this->initData();
		
		$container = $this->getDraftContainer($this->order_product_id);
		if ($container->loaded()) {
			$this->container = $container;
		}
	}

	public function processSaveAction() {
		if ($this->is_accept == 1) {
			if ($this->action == 'go_to_warehouse') {
				$result = $this->save();
			} else if ($this->action == 'draft') {
				$result = $this->createDraft();
			}
			$this->initData();
		} else {
			$result = $this->backToTranslator();
		}
		
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
		$this->containerHistory = ORM::factory('container')
									->where('order_product_id', '=', $this->order_product_id)
									->where('source', '=', Model_Container::SOURCE_FACTORY)
									->where('container.status', '>=' , Model_Container::STATUS_INIT)
									->find_all();
	}
	
	public function getAcceptVoidOptions() {
		if ($this->orderProduct->factory_delivery_qty == 0) {
			return array(1=>'接受item', 0=>'不接受item');
		} else {
			return array(1=>'接受item');
		}
	}
	
	private function createDraft() {
		$db = Database::instance();
		$db->begin();
		
		try {
			// Validation
			$post = Validation::factory($_POST['container'])
					->rule('delivery_date', 'not_empty');
			
			if (!$post->check()) {
				$this->errors = $post->errors('factory/shippingform');
				return false;
			}
			
			$container = $this->getDraftContainer($this->order_product_id);
			
			$user = Auth::instance()->get_user();
			if (!$container->loaded()) {
				// New draft
				$container = new Model_Container();
				$container->order_product_id = $this->order_product_id;
				$container->source = Model_Container::SOURCE_FACTORY;
				$container->status = Model_Container::STATUS_DRAFT;
				$container->create_date = DB::expr('current_timestamp');
				$container->created_by = $user->username;
			}
			
			$container->values($_POST['container']);
			$container->orig_delivery_qty = $this->container->delivery_qty;
			if ($container->container_input_date == '') {
				$container->container_input_date = NULL;
			}
			$container->last_updated_by = $user->username;
			
			$container->save();
			
			// Update order's propose delivery date
			$orderProduct = new Model_OrderProduct($this->order_product_id);
			$orderProduct->propose_delivery_date = $container->delivery_date;
			$orderProduct->save();
			
		} catch (ORM_Validation_Exception $e) {
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
	
	private function save() {
		$db = Database::instance();
		$db->begin();
		
		try {
			// Validation
			$post = Validation::factory($_POST['container'])
					->rule('delivery_qty', 'not_empty')
					->rule('delivery_qty', 'digit')
					->rule('delivery_qty', 'CustomValidation::positive')
					
					->rule('delivery_date', 'not_empty')
					
					->rule('container_input_date', 'not_empty')
					
					->rule('container_no', 'not_empty');
				
			if (!$post->check()) {
				$this->errors = $post->errors('factory/shippingform');
				return false;
			}
			
			$orderProduct = new Model_OrderProduct($this->order_product_id);
			if ($this->container->delivery_qty > $orderProduct->factory_entry_qty) {
				$this->errors[] = '交貨數量  > 已進倉數量';
				return false;
			}
			
			// Get order type
			$order_type_id = $orderProduct->order->order_type_id;
				
			$container = $this->getDraftContainer($this->order_product_id);
				
			$user = Auth::instance()->get_user();
			if (!$container->loaded()) {
				// New container
				$container = new Model_Container();
				$container->order_product_id = $this->order_product_id;
				$container->source = Model_Container::SOURCE_FACTORY;
				$container->create_date = DB::expr('current_timestamp');
				$container->created_by = $user->username;
			}
			
			$container->values($_POST['container']);
			$container->orig_delivery_qty = $container->delivery_qty;
			$container->last_updated_by = $user->username;
			
			if ($order_type_id != Model_OrderType::ID_KAITO) {
				$container->status = Model_Container::STATUS_INIT;
			} else {
				// Order Type = Kaito
				// No need to generate delivery note
				$container->status = Model_Container::STATUS_COMPLETE;
				
				if ($orderProduct->factory_delivery_qty + $this->container->delivery_qty >= $orderProduct->factory_qty) {
					// All QTY are transfered to warehouse
					$orderProduct->factory_status = Model_OrderProduct::STATUS_COMPLETE;
				}
			}
			
			// Update order product's shipped qty
			$orderProduct->factory_delivery_qty = $orderProduct->factory_delivery_qty + $this->container->delivery_qty;
			$orderProduct->factory_entry_qty = 	$orderProduct->factory_entry_qty - $this->container->delivery_qty;
			$orderProduct->propose_delivery_date = NULL;
			
			if ($orderProduct->factory_status < Model_OrderProduct::STATUS_WAREHOUSE) {
				$orderProduct->factory_status = Model_OrderProduct::STATUS_WAREHOUSE;
			}
			
			// Update DB
			$container->save();
			$orderProduct->save();
			
			// Update order's status
			$orderProduct->order->updateStatus();
			
			// Update container summary
			$this->createSummary();
			
			// Clear form
			$this->container = new Model_Container();
		} catch (ORM_Validation_Exception $e) {
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
	
	private function backToTranslator() {
		$container = $this->getDraftContainer($this->order_product_id);
		if ($container->loaded()) {
			// Delete draft container
			$container->delete();
			
			// Clear propose delivery date in order
			$orderProduct = new Model_OrderProduct($this->order_product_id);
			$orderProduct->propose_delivery_date = NULL;
			$orderProduct->save();
		}
		
		$orderProduct = new Model_OrderProduct($this->order_product_id);
		$orderProduct->factory_remark = $this->factory_remark;
		$orderProduct->factory_status = Model_OrderProduct::STATUS_TRANSLATOR;
		$orderProduct->is_reject = Model_OrderProduct::IS_REJECT_YES;
		
		$orderProduct->save();
		
		return true;
	}
	
	private function createSummary() {
		$summary = ORM::factory('containerSummary')
					->where('order_product_id', '=', $this->order_product_id)
					->find();
		if (!$summary->loaded()) {
			$summary = new Model_ContainerSummary();
			$summary->order_product_id = $this->order_product_id;
		}
			
		$summary->container_no_list = '';
		$summary->delivery_date_list = '';
		$summary->container_input_date_list = '';
		$summary->delivery_qty_list = '';
			
		// Update container_no list
		$containers = DB::select('container_no')
						->from('container')
						->where('order_product_id', '=', $this->order_product_id)
						->where('source', '=', Model_Container::SOURCE_FACTORY)
						//->order_by('container_no')
						->order_by('container.id')
						->execute();
		
		foreach ($containers as $container) {
			$summary->container_no_list .= '['.$container['container_no'].'],';
		}
			
		// Update delivery_date list
		$containers = DB::select('delivery_date')
						->from('container')
						->where('order_product_id', '=', $this->order_product_id)
						->where('source', '=', Model_Container::SOURCE_FACTORY)
						//->order_by('delivery_date')
						->order_by('container.id')
						->execute();
			
		foreach ($containers as $container) {
			$summary->delivery_date_list .= '['.$container['delivery_date'].'],';
		}
			
		// Update container_input_date list
		$containers = DB::select('container_input_date')
						->from('container')
						->where('order_product_id', '=', $this->order_product_id)
						->where('source', '=', Model_Container::SOURCE_FACTORY)
						//->order_by('container_input_date')
						->order_by('container.id')
						->execute();
			
		foreach ($containers as $container) {
			$summary->container_input_date_list .= '['.$container['container_input_date'].'],';
		}
		
		// Update delivery QTY list
		$containers = DB::select('delivery_qty')
						->from('container')
						->where('order_product_id', '=', $this->order_product_id)
						->where('source', '=', Model_Container::SOURCE_FACTORY)
						//->order_by('container_input_date')
						->order_by('container.id')
						->execute();
			
		foreach ($containers as $container) {
			$summary->delivery_qty_list .= '['.$container['delivery_qty'].'],';
		}
			
		$summary->container_no_list = substr($summary->container_no_list, 0, strlen($summary->container_no_list) - 1);
		$summary->delivery_date_list = substr($summary->delivery_date_list, 0, strlen($summary->delivery_date_list) - 1);
		$summary->container_input_date_list = substr($summary->container_input_date_list, 0, strlen($summary->container_input_date_list) - 1);
		$summary->delivery_qty_list = substr($summary->delivery_qty_list, 0, strlen($summary->delivery_qty_list) - 1);
		$summary->save();
	}
	
	private function getDraftContainer($orderProductId) {
		return ORM::factory('container')
				->where('order_product_id', '=', $orderProductId)
				->where('status', '=', Model_Container::STATUS_DRAFT)
				->find();
	}
}