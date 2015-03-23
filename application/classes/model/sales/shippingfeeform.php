<?php
class Model_Sales_ShippingFeeForm extends Model_PageForm {
	public $action;
	public $customer_id;
	
	public $shippingFee;
	public $shippingFees;
	public $totalShippingFee;
	
	public $errors = array();
	
	public $page_url = 'sales/shipping_fee';
	
	public function populate($post) {
		parent::populate($post);
		
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->customer_id = isset($post['customer_id']) ? $post['customer_id'] : NULL;
		
		$this->shippingFee = new Model_ShippingFee();
		$this->shippingFee->values($post);
	}
	
	public function processSearchAction() {
		$this->shippingFees = $this->search();
	}
	
	public function processViewAction($shipping_fee_id) {
		$this->shippingFee = new Model_ShippingFee($shipping_fee_id);
		
		if (!$this->shippingFee->loaded()) {
			$this->errors[] = 'Record not found';
		} else {
			return true;
		}
	}
	
	public function processAddAction() {
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
		if ($result) {
			$this->initAddPage();
		}
		
		return $result;
	}
	
	private function initAddPage() {
		$this->shippingFee = new Model_ShippingFee();

		return true;
	}
	
	private function save() {
		$db = Database::instance();
		$db->begin();
		
		// Check privilege
		/* $user = Auth::instance()->get_user();
		if ($user->isSales() && $this->order->created_by != $user->username) {
			throw new HTTP_Exception_401(__('error.no_authorization.modify'));
		} */

		try {
			// Validation
			$this->shippingFee->check();
			
			// 
			/* $query = DB::select(array(DB::expr('SUM(amount)'), 'total_amount'))
					->from(array('shippingFee', 'shippingfee'))
					>where('order_id', '=', $this->order_id);
			$result = $query->execute();
			$totalShippingFee = $result[0]['total_amount']; */
			
			// Save to DB
			$this->shippingFee->status = Model_ShippingFee::STATUS_INIT; 
			$this->shippingFee->created_by = Auth::instance()->get_user()->username;
			$this->shippingFee->create_date = DB::expr('current_timestamp');
			$this->shippingFee->save(NULL);
				
			// Clear form
			$this->shippingFee = new Model_ShippingFee();
		} catch (ORM_Validation_Exception $e) {
			foreach ($e->errors('sales') as $error) {
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
	
	private function getOrder() {
		$orm = Model::factory('order')
		->with('customer')
		->where('order.id', '=', $this->order_id);
	
		$user = Auth::instance()->get_user();
		if ($user->isSales()) {
			$orm->where('order.created_by', '=', $user->username);
		}
	
		return $orm->find();
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
		$orm = Model::factory('shippingFee')
				->with('customer');
	
		if ($this->customer_id != 0) {
			$orm->where('customer.id', '=', $this->customer_id);
		}
		
		$user = Auth::instance()->get_user();
		if ($user->isSales()) {
			$orm->where('shippingfee.created_by', '=', $user->username);
		}
		
		return $orm;
	}
	
	public function getQueryString() {
		return '&customer_id='.$this->customer_id;
	}
}