<?php
class Model_Admin_ProfitConfigForm {
	public $action;
	public $customer_id;
	
	public $success;
	public $errors;
	
	public function populate($post) {
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		
		$this->profitConfigs = array();
		if (isset($post['profitConfig'])) {
			foreach ($post['profitConfig'] as $idx=>$value) {
				$profitConfig = new Model_ProfitConfig();
				$profitConfig->code = $idx;
				$profitConfig->values($value);
				$this->profitConfigs[] = $profitConfig;
			}
		}
	}
	
	public function processSearchAction() {
		$this->profitConfigs = ORM::factory('profitConfig')
							->order_by('code')
							->find_all();
	}
	
	public function processSaveAction() {
		return $this->save();
	}

	private function save() {
		$db = Database::instance();
		$db->begin();
		
		$this->errors = array();
		
		try {
			$searchResult = ORM::factory('profitConfig')
									->find_all();
			
			
			$orgProfitConfigs = array();
			foreach ($searchResult as $profitConfig) {
				$orgProfitConfigs[$profitConfig->code] = $profitConfig;
			}
			
			foreach ($this->profitConfigs as $newProfitConfig) {
				$orgProfitConfig = $orgProfitConfigs[$newProfitConfig->code];
				$orgProfitConfig->value = $newProfitConfig->value;
				$orgProfitConfig->save();
			}
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		return true;
	}
}