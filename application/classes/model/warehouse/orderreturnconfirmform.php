<?php
class Model_Warehouse_OrderReturnConfirmForm extends Model_PageForm {
	public $action;
	public $customer_id;
	public $return_date_from;
	public $return_date_to;
	
	public $order_return_id;
	
	public $orderReturns;
	public $errors;
	
	public $page_url = 'warehouse/order_return_confirm';
	
	public function populate($post) {
		parent::populate($post);
		
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->customer_id = isset($post['customer_id']) ? trim($post['customer_id']) : NULL;
		$this->return_date_from = isset($post['return_date_from']) ? trim($post['return_date_from']) : NULL;
		$this->return_date_to = isset($post['return_date_to']) ? trim($post['return_date_to']) : NULL;
		
		$this->order_return_id = isset($post['order_return_id']) ? trim($post['order_return_id']) : NULL;
	}
	
	public function processSearchAction() {
		$this->orderReturns = $this->search();
	}
	
	public function processConfirmAction() {
		$result = $this->confirm();
		$this->processSearchAction();
		return $result;
	}
	
	public function processCancelAction() {
		$result = $this->cancel();
		$this->processSearchAction();
		return $result;
	}
	
	private function confirm() {
		$this->errors = array();
		
		$db = Database::instance();
		$db->begin();
		
		try {
			$orderReturn = new Model_OrderReturn($this->order_return_id);
			
			if (!$orderReturn->loaded()) {
				$this->errors[] = 'Record not found';
				return false;
			} else if ($orderReturn->status > Model_OrderReturn::STATUS_INIT) {
				$this->errors[] = 'The return has already been confirmed.';
				return false;
			}
			
			// Update status
			$orderReturn->status = Model_OrderReturn::STATUS_READY_FOR_DELIVERY_NOTE;
			$orderReturn->save();
			
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		
		return true;
	}
	
	private function cancel() {
		$this->errors = array();
	
		$db = Database::instance();
		$db->begin();
	
		try {
			$orderReturn = new Model_OrderReturn($this->order_return_id);
				
			if (!$orderReturn->loaded()) {
				$this->errors[] = 'Record not found';
				return false;
			} else if ($orderReturn->status > Model_OrderReturn::STATUS_INIT) {
				$this->errors[] = 'The return has already been confirmed.';
				return false;
			}
				
			// Update status
			$orderReturn->status = Model_OrderReturn::STATUS_CANCEL;
			$orderReturn->save();
				
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
					->select('cust_code')
					->order_by('create_date', 'desc')
					->limit($limit)
					->offset($offset)
					->find_all();
	}
	
	public function getCriteria() {
		$orm = Model::factory('orderReturn')
				->with('customer');
				
		if (!empty($this->customer_id)) {
			$orm->where('orderreturn.customer_id', '=', $this->customer_id);
		}
		
		if (!empty($this->return_date_from)) {
			$orm->where('orderreturn.create_date', '>=', $this->return_date_from);
		}
			
		if (!empty($this->return_date_to)) {
			$toDate = date('Y-m-d', strtotime($this->return_date_to.' + 1 days'));
			$orm->where('orderreturn.create_date', '<', $toDate);
		}
	
		return $orm;
	}
	
	public function getQueryString() {
		$query_string = '';
		
		if (!empty($this->customer_id)) {
			$query_string .= '&customer_id='.$this->customer_id;
		}
		
		if (!empty($this->return_date_from)) {
			$query_string .= '&return_date_from='.$this->return_date_from;
		}
			
		if (!empty($this->settle_date_to)) {
			$query_string .= '&return_date_to='.$this->return_date_to;
		}
		
		return $query_string;
	}
}