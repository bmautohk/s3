<?php
class Model_Admin_ProfitConfig15Form {
	public $code;
	public $config;
	public $configs;
	
	public $action;
	public $errors;
	
	public function populate($post) {
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->code = isset($post['code']) ? $post['code'] : NULL;
		
		if (isset($post['config'])) {
			$this->config = new Model_ProfitConfig15($this->code);
			$this->config->values($post['config']);
		}
	}
	
	public function searchAction() {
		// Retrieve all config
		$this->configs = ORM::factory('profitConfig15')
						->select('description')
						->find_all();
	}
	
	public function retrieve($code) {
		$this->config = new Model_ProfitConfig15($code);
		if (!$this->config->loaded()) {
			return false;
		} else {
			return true;
		}
	}
	
	public function editAction() {
		$result = $this->updateConfig();
		
		if ($result) {
			$this->searchAction();
		}
		
		return $result;
	}
	
	private function updateConfig() {
		$this->errors[] = array();
		
		if ($this->config->loaded()) {
			$db = Database::instance();
			$db->begin();
			try {
				$this->config->save();
			} catch (ORM_Validation_Exception $e) {
				$this->errors = $e->errors('admin');
				$db->rollback();
				return false;
			}
			$db->commit();
			
		} else {
			$this->errors[] = 'Configuration does not exist.';
			return false;
		}
		
		return true;
	}
}