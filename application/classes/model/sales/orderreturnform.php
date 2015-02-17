<?php
class Model_Sales_OrderReturnForm extends Model_PageForm {
	public $action;
	
	public $rmb_to_jpy_rate;
	public $customer_id;
	public $orderReturn;
	public $orderReturns;
	
	public $errors = array();
	
	public $page_url = 'sales/order_return';
	
	public function populate($post) {
		parent::populate($post);
		
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->customer_id = isset($post['customer_id']) ? $post['customer_id'] : NULL;
		
		$this->orderReturn = new Model_OrderReturn();
		$this->orderReturn->values($post);
	}
	
	public function processSearchAction() {
		$this->orderReturns = $this->search();
	}
	
	public function processAddAction() {
		$this->orderReturn = new Model_OrderReturn();
		
		if ($this->initAddPage()) {
			return true;
		} else {
			$this->order_id = '';
			$this->processSearchAction();
			return false;
		}
	}
	
	public function processSaveAction() {
		$result = $this->save();
		
		$this->initAddPage();
		if ($result) {
			// Clear input
			$this->orderReturn = new Model_OrderReturn();
		}
	
		return $result;
	}
	
	private function initAddPage() {
		$rmbJPYRate = Model_Rate::getCurrentRate('RMB', 'JPY');
		if ($rmbJPYRate == NULL) {
			$this->errors[] = 'Can\'t find rate RMB <-> JPY';
			$this->rmb_to_jpy_rate = 0;
			return false;
		}
		$this->rmb_to_jpy_rate = $rmbJPYRate->rate;
		
		return true;
	}
	
	private function save() {
		$db = Database::instance();
		$db->begin();
		
		// Check privilege
		$user = Auth::instance()->get_user();
		/* if ($user->isSales() && $this->order->created_by != $user->username) {
			throw new HTTP_Exception_401(__('error.no_authorization.modify'));
		} */
	
		try {
			// Validation
			$this->orderReturn->check();
			
			$rmbJPYRate = Model_Rate::getCurrentRate('RMB', 'JPY');

			// Save to DB
			$this->orderReturn->rmb_to_jpy_rate = $rmbJPYRate->rate;
			$this->orderReturn->return_date = date('Y-m-d'); // Return Date = today
			$this->orderReturn->created_by = $user->username;
			$this->orderReturn->create_date = DB::expr('current_timestamp');
			$this->orderReturn->save(NULL);
	
			// Clear form
			$this->orderReturn = new Model_OrderReturn();
		} catch (ORM_Validation_Exception $e) {
			foreach ($e->errors('sales') as $error) {
				$this->errors[] = $error;
			}
			$db->rollback();
			return false;
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
				->select('customer.cust_code')
				->order_by('create_date', 'desc')
				->limit($limit)
				->offset($offset)
				->find_all();
	}
	
	public function getCriteria() {
		$orm = Model::factory('orderReturn')
				->with('customer');
	
		if (!empty($this->customer_id)) {
			$orm->where('customer_id', '=', $this->customer_id);
		}
		
		$user = Auth::instance()->get_user();
		if ($user->isSales()) {
			$orm->where('orderreturn.created_by', '=', $user->username);
		}
	
		return $orm;
	}
	
	public function getQueryString() {
		return '&customer_id='.$this->customer_id;
	}	
}