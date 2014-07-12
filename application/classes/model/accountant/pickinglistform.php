<?php
class Model_Accountant_PickingListForm {
	public $action;
	public $container_no;
	public $customer_id;
	
	public $containers;
	
	public function populate($post) {
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->container_no = isset($post['container_no']) ? $post['container_no'] : NULL;
		$this->customer_id = isset($post['customer_id']) ? $post['customer_id'] : NULL;
	}

	public function process() {
		if ($this->action == 'search') {
			$this->search();
		}
	}
	
	private function search() {
		$orm = ORM::factory('container')
				->with('orderProduct')
				->join('order')->on('order.id', '=', 'orderProduct.order_id')
				->join('customer')->on('customer.id', '=', 'order.customer_id')
				->select('cust_code');
		
		if ($this->container_no != NULL) {
			$orm = $orm->where('container_no', 'like', '%'.$this->container_no.'%');
		}
		
		if ($this->customer_id != NULL && $this->customer_id != 0) {
			$orm = $orm->where('customer_id', '=', $this->customer_id);
		}
		
		$this->containers = $orm->find_all();
	}
}