<?php
class Model_Sales_CustomerForm {
	public $customer_id;
	
	public $customers;
	public $customer;
	
	public $errors;
	
	public function populate($post) {
		$this->customer_id = isset($post['customer_id']) ? $post['customer_id'] : NULL;
		
		$this->customer = new Model_Customer();
		$this->customer->values($post);
	}
	
	public function retrieve($customer_id) {
		$this->customer = $this->getCustomer($customer_id);
		if ($this->customer == NULL) {
			return false;
		}
		
		$this->customer_id = $this->customer->id;
		
		return true;
	}
	
	public function searchAction() {
		$orm = ORM::factory('customer')
				->join('office_address', 'LEFT')->on('office_address.id', '=', 'customer.office_address_id')
				->select(array('office_address.name', 'office_address_name'));
		
		$user = Auth::instance()->get_user();
		if ($user->isSales()) {
			$orm->where('sales_code', '=', $user->username);
		}
		
		$this->customers = $orm->order_by('cust_code')->find_all();
	}
	
	public function initAddAction() {
		$this->customer = new Model_Customer();
	}
	
	public function saveAction() {
		$result = $this->save();
		
		return $result;
	}
	
	public function deleteAction() {
		$result = $this->delete();
		
		$this->searchAction();
		
		return $result;
	}
	
	private function save() {
		$this->errors = array();
		
		$user = Auth::instance()->get_user();
		if (empty($this->customer_id)) {
			// New record
			$customer = new Model_Customer();
			$customer->sales_code = $user->username;
			$customer->created_by = $user->username;
			$customer->create_date = DB::expr('current_timestamp');
		} else {
			// Existing customer
			$customer = $this->getCustomer($this->customer_id);
			if ($customer == NULL) {
				$this->errors[] = 'Customer not found';
				return false;
			}
		}
		
		$customer->values($_POST);
		$customer->last_updated_by = $user->username;
		
		$db = Database::instance();
		$db->begin();
		
		try {
			$customer->save();
			$this->customer = $customer;
			$this->customer_id = $customer->id;
		} catch (ORM_Validation_Exception $e) {
			$db->rollback();
			$this->errors = $e->errors('sales');
			return false;
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		
		return true;
	}
	
	private function delete() {
		// Check whether the customer has any order
		$result = DB::select(array(DB::expr('COUNT(order.id)'), 'count'))
		->from('order')
		->where('customer_id', '=', $this->customer_id)
		->execute();
			
		if ($result[0]['count'] > 0) {
			$this->errors[] = 'Fail to delete customer. The customer has already had sales order.';
			return false;
		}
			
		$customer = $this->getCustomer($this->customer_id);
		if ($customer == NULL) {
			$this->errors[] = 'Customer not found';
			return false;
		}
		
		$db = Database::instance();
		$db->begin();
		
		try {
			// Delete customer
			$customer->delete();
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		
		return true;
	}
	
	private function getCustomer($customer_id) {
		$user = Auth::instance()->get_user();
		$customer = new Model_Customer($customer_id);
		
		// Check privilege
		if ($user->isSales() && $customer->sales_code != $user->username) {
			throw new HTTP_Exception_401(__('error.no_authorization.modify'));
		}
		
		return $customer->loaded() ? $customer : NULL;
	}
}