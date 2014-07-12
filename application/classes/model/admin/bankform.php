<?php
class Model_Admin_BankForm {
	const ACTION_ADD = 'add';
	const ACTION_EDIT = 'edit';
	
	public $bank_id;
	public $bank;
	
	public $banks;
	
	public $action = 'add';
	public $errors;
	
	public function populate($post) {
		$this->bank_id = isset($post['bank_id']) ? $post['bank_id'] : NULL;
		
		if (!empty($this->bank_id)) {
			// Existing bank
			$this->bank = new Model_BankAccount($this->bank_id);
			$this->bank->values($post);
		} else {
			// New bank
			$this->bank = new Model_BankAccount();
			$this->bank->values($post);
		}
	}
	
	public function searchAction() {
		// Retrieve all bank
		$this->banks = ORM::factory('bankAccount')
						->order_by('bank_name')
						->find_all();
	}
	
	public function retrieve($bank_id) {
		$this->bank = new Model_BankAccount($bank_id);
		if (!$this->bank->loaded()) {
			return false;
		} else {
			$this->bank_id = $bank_id;
			return true;
		}
	}

	public function saveAction() {
		$result = $this->saveBank();

		// Display bank list
		$this->searchAction();
	
		return $result;
	}
	
	private function saveBank() {
		$db = Database::instance();
		$db->begin();
		
		$this->errors = array();
		
		try {
			$this->bank->check();
			
			$this->bank->save();
		} catch (ORM_Validation_Exception $e) {
			foreach ($e->errors('admin') as $error) {
				$this->errors[] = $error;
			}
			return false;
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		
		// Clear form
		$this->bank_id = NULL;
		$this->bank = new Model_BankAccount();
		
		return true;
	}
}