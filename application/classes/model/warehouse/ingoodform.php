<?php
class Model_Warehouse_IngoodForm extends Model_PageForm {	
	public $action;
	public $container_no;
	public $order_id;
	public $product_cd;
	public $customer_id;
	public $order_date_from;
	public $order_date_to;
	
	public $container_id;
	public $containers;
	
	public $page_url = 'warehouse/ingood';
	
	public function populate($post) {
		parent::populate($post);
		
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->container_no = isset($post['container_no']) ? $post['container_no'] : NULL;
		$this->order_id = isset($post['order_id']) ? $post['order_id'] : NULL;
		$this->product_cd = isset($post['product_cd']) ? $post['product_cd'] : NULL;
		$this->customer_id = isset($post['customer_id']) ? $post['customer_id'] : NULL;
		$this->order_date_from = isset($post['order_date_from']) ? $post['order_date_from'] : NULL;
		$this->order_date_to = isset($post['order_date_to']) ? $post['order_date_to'] : NULL;
	}
	
	public function searchAction() {
		$this->containers = $this->search();
	}
	
	public function addDeliveryNoteAction() {
		$result = $this->add_delivery_note();
		$this->searchAction();
		
		return $result;
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
			
			$container->status = Model_Container::STATUS_READY_FOR_DELIVERY_NOTE;
			$container->save();
			
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		
		return true;
	}

// Overrided function
	public function getData($limit, $offset) {
		return $this->getCriteria()
				->select('product_master.product_desc')
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
				->limit($limit)
				->offset($offset)
				->find_all();
	}
	
	public function getCriteria() {
		$orm = ORM::factory('container')
				->join('order_product')->on('container.order_product_id', '=', 'order_product.id')
				->join('order')->on('order.id', '=', 'order_product.order_id')
				->join('customer')->on('customer.id', '=', 'order.customer_id')
				->join('product_master', 'LEFT')->on('product_master.no_jp', '=', 'order_product.product_cd')
				->where('container.source', '=', Model_Container::SOURCE_FACTORY)
				->where('container.status', '>=' , Model_Container::STATUS_INIT);
	
		if (!empty($this->container_no)) {
			$orm->where('container_no', 'like', '%'.$this->container_no.'%');
		}
	
		if (!empty($this->order_id)) {
			$orm->where('order_id', '=', $this->order_id);
		}
	
		if (!empty($this->product_cd)) {
			$orm->where('product_cd', '=', $this->product_cd);
		}
	
		if (!empty($this->customer_id)) {
			$orm->where('customer_id', '=', $this->customer_id);
		}
		
		if (!empty($this->order_date_from)) {
			$orm->where('order.order_date', '>=', $this->order_date_from);
		}
			
		if (!empty($this->order_date_to)) {
			$toDate = date('Y-m-d', strtotime($this->order_date_to.' + 1 days'));
			$orm->where('order.order_date', '<', $toDate);
		}
	
		return $orm;
	}
	
	public function getQueryString() {
		$query_string = '';
		
		if (!empty($this->container_no)) {
			$query_string .= '&container_no='.$this->container_no;
		}
		
		if (!empty($this->order_id)) {
			$query_string .= '&order_id='.$this->order_id;
		}
		
		if (!empty($this->product_cd)) {
			$query_string .= '&product_cd='.$this->product_cd;
		}
		
		if (!empty($this->customer_id)) {
			$query_string .= '&customer_id='.$this->customer_id;
		}
		
		if (!empty($this->order_date_from)) {
			$query_string .= '&order_date_from='.$this->order_date_from;
		}
			
		if (!empty($this->order_date_to)) {
			$query_string .= '&order_date_to='.$this->order_date_to;
		}
		
		return $query_string;
	}
}