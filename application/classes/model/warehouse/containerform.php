<?php
class Model_Warehouse_ContainerForm {	
	public $action;
	
	public $order_product_id;
	
	public $containers;
	
	// For container return
	public $container_id;
	public $inputContainerReturn;
	public $container; // Display container information
	public $containerReturnHistories; // Display container return history
	
	public function populate($post) {
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->container_id = isset($post['container_id']) ? $post['container_id'] : NULL;
		
		if (isset($post['container_return'])) {
			$this->inputContainerReturn = new Model_ContainerReturn();
			$this->inputContainerReturn->values($post['container_return']);
		}
	}
	
	public function retrieve($order_product_id) {
		$this->order_product_id = $order_product_id;
		$this->search();
	}
	
	public function addDeliveryNoteAction($container_id) {
		$this->container_id = $container_id;
		$result = $this->add_delivery_note();
		
		// Refresh container list
		$container = new Model_Container($this->container_id);
		$this->order_product_id = $container->order_product_id;
		$this->search();
		
		return $result;
	}
	
	public function returnToFactoryAction($container_id) {
		$this->container_id = $container_id;
		$result = $this->returnToFactory();
	
		// Refresh container list
		$container = new Model_Container($this->container_id);
		$this->order_product_id = $container->order_product_id;
		$this->search();
	
		return $result;
	}
	
	public function initContainerReturnAction($container_id) {
		$this->container_id = $container_id;
		$this->container_return_init();
		
		$this->inputContainerReturn = new Model_ContainerReturn();
	}
	
	public function addContainerReturnAction() {
		$result = $this->add_container_return();
		if ($result) {
			$this->inputContainerReturn = new Model_ContainerReturn();
		}
		
		$this->container_return_init();
		
		return $result;
	}
	
	private function search() {
		$this->containers =	ORM::factory('container')
							->join('order_product')->on('container.order_product_id', '=', 'order_product.id')
							->join('order')->on('order.id', '=', 'order_product.order_id')
							->join('customer')->on('customer.id', '=', 'order.customer_id')
							->join('temp_product_master', 'LEFT')->on('temp_product_master.order_product_id', '=', 'order_product.id')
							//->join('product_master', 'LEFT')->on('product_master.no_jp', '=', 'order_product.product_cd')
							->where('container.source', '=', Model_Container::SOURCE_FACTORY)
							->where('container.status', '>=' , Model_Container::STATUS_INIT)
							->where('container.order_product_id', '=', $this->order_product_id)
							->select('temp_product_master.product_desc')
							->select('customer.cust_code')
							->select('order_product.order_id')
							->select('order_product.product_cd')
							->select('factory_qty')
							->select('jp_qty')
							->select('order.picture1')
							->select('order.picture2')
							->select('order.picture3')
							->order_by('order_id', 'desc')
							->order_by('order_product.product_cd')
							->find_all();
	}

	private function add_delivery_note() {
		$db = Database::instance();
		$db->begin();
		
		$this->errors = array();
		
		try {
			// Get container
			$container = ORM::factory('container')
						->where('id', '=', $this->container_id)
						->find();
			
			// Validation
			
			// Total QTY which warehouse has delivered to accountant
			$result = DB::select(array(DB::expr('SUM(delivery_qty)'), 'sum'))
											->from('container')
											->where('order_product_id', '=', $container->order_product_id)
											->where('status', '>=', Model_Container::STATUS_READY_FOR_DELIVERY_NOTE)
											->where('source', '=', Model_Container::SOURCE_FACTORY)
											->execute();
			$totalAccountantDeliveryQty = $result[0]['sum'];
			
			$orderProduct = new Model_OrderProduct($container->order_product_id);
			if ($container->delivery_qty + $totalAccountantDeliveryQty > $orderProduct->factory_qty) {
				$this->errors[] = '交貨數量['.$container->delivery_qty.'] + 已給入金管理數量['.$totalAccountantDeliveryQty.'] > 厰/ben 數量(大步分貨量)['.$orderProduct->factory_qty.']. 需要進行還貨.';
				return false;
			}
			
			$container->status = Model_Container::STATUS_READY_FOR_DELIVERY_NOTE;
			$container->save();
			
			// Refresh the flag "has_container_to_accountant"
			$orderProduct = new Model_OrderProduct($container->order_product_id);
			$orderProduct->refreshHasContainerToAccountant();
			$orderProduct->save();
			
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		
		return true;
	}
	
	private function returnToFactory() {
		$db = Database::instance();
		$db->begin();
	
		$this->errors = array();
	
		try {
			// Validation
			// 返品 exist -> Not allow to return back to factory
			$result = DB::select(array(DB::expr('COUNT(id)'), 'count'))
						->from('container_return')
						->where('container_id', '=', $this->container_id)
						->execute();
			$noOfReturn = $result[0]['count'];
			if ($noOfReturn > 0) {
				$this->errors[] = '返品 has already created. You are not allowed to return to 工場.';
				return false;
			}
			
			// Update container
			$container = new Model_Container($this->container_id);
			$container->status = Model_Container::STATUS_DRAFT;
			$container->last_updated_by = Auth::instance()->get_user()->username;
			$container->save();
			
			// Update order product
			$orderProduct = new Model_OrderProduct($container->order_product_id);
			$orderProduct->factory_delivery_qty = $orderProduct->factory_delivery_qty - $container->delivery_qty;
			$orderProduct->factory_entry_qty = 	$orderProduct->factory_entry_qty + $container->delivery_qty;
			$orderProduct->propose_delivery_date = $container->delivery_date;
			
			// Update order product's status
			$result = DB::select(array(DB::expr('COUNT(id)'), 'count'))
					->from('container')
					->where('order_product_id', '=', $container->order_product_id)
					->where('status', '>=', Model_Container::STATUS_INIT)
					->execute();
			$noOfContainer = $result[0]['count'];
			
			if ($noOfContainer == 0) {
				$orderProduct->factory_status = Model_OrderProduct::STATUS_FACTORY;
			}
				
			// Refresh the flag "has_container_to_accountant"
			$orderProduct->refreshHasContainerToAccountant();
			
			$orderProduct->save();
			
			// Update order's status
			$orderProduct->order->updateStatus();
			
			// Update container summary
			Model_ContainerSummary::createSummary($this->order_product_id);
			
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
	
		$db->commit();
	
		return true;
	}
	
	private function container_return_init() {
		$this->container = new Model_Container($this->container_id);
		
		// Search history records
		$this->containerReturnHistories = ORM::factory('containerReturn')
											->where('container_id', '=', $this->container_id)
											->order_by('create_date')
											->find_all();
	}

	private function add_container_return() {
		$this->errors = array();
		
		$db = Database::instance();
		$db->begin();
		
		try {
			$this->inputContainerReturn->container_id = $this->container_id;
			$this->inputContainerReturn->created_by = Auth::instance()->get_user()->username;
			$this->inputContainerReturn->create_date = DB::expr('current_timestamp');
			
			// Validation
			$this->inputContainerReturn->check();

			$container = new Model_Container($this->container_id);
			
			if ($this->inputContainerReturn->qty > $container->delivery_qty) {
				$this->errors[] = '返回數量 is larger than 實際交貨數量.';
				return false;
			}
			
			// Decrease QTY that warehouse needs to deliver to accountant
			$container->delivery_qty -= $this->inputContainerReturn->qty;
			
			if ($container->delivery_qty == 0) {
				$container->status = Model_Container::STATUS_COMPLETE;
			}
			
			// Save to DB
			$this->inputContainerReturn->save();
			$container->save();

			// Refresh the flag "has_container_to_accountant"
			$orderProduct = new Model_OrderProduct($container->order_product_id);
			$orderProduct->refreshHasContainerToAccountant();
			$orderProduct->save();
			
		} catch (ORM_Validation_Exception $e) {
			foreach ($e->errors('warehouse') as $error) {
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
