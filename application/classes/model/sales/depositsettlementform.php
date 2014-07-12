<?php
class Model_Sales_DepositSettlementForm extends Model_PageForm {
	public $action;
	public $order_id;
	
	public $order;
	public $depositSettle;
	public $depositSettleHistory;
	public $totalInputDeposit;
	public $errors;
	
	public $page_url = 'sales/deposit_settlement';
	
	public function populate($post) {
		parent::populate($post);
		
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->order_id = isset($post['order_id']) ? trim($post['order_id']) : NULL;
		
		$this->depositSettle = new Model_DepositSettle();
		$this->depositSettle->values($post);
	}
	
	public function processSearchAction() {
		$this->depositSettleHistory = $this->search();
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
		$this->initAddPage();
	
		return $result;
	}
	
	private function initAddPage() {
		$this->order = Model::factory('order')
						->with('customer')
						->where('order.id', '=', $this->order_id)
						->find();
		
		if (!$this->order->loaded()) {
			$this->errors[] = 'Order No['.$this->order_id.'] does not exist.';
			return false;
		}
		
		$this->depositSettleHistory = Model::factory('depositSettle')
										->where('order_id', '=', $this->order_id)
										->find_all();
		
		$this->totalInputDeposit = 0;
		foreach ($this->depositSettleHistory as $history) {
			$this->totalInputDeposit += $history->settle_amt + $history->fee;
		}
		
		return true;
	}

	private function save() {
		$db = Database::instance();
		$db->begin();
		
		$this->errors = array();
		
		try {
			// Validation
			$this->depositSettle->check();
			
			$order = Model::factory('order')
					->with('customer')
					->where('order.id', '=', $this->order_id)
					->find();
			
			$queryResult = DB::select(array(DB::expr('sum(settle_amt + fee)'), 'total'))
								->from('deposit_settle')
								->where('order_id', '=', $this->order_id)
								->execute();
			
			if ($this->depositSettle->settle_amt + $this->depositSettle->fee + $queryResult[0]['total'] > $order->deposit_amt) {
				$this->errors[] = '總入金大於 Deposit 金額';
				return false;
			}
			
			// Save to DB
			$this->depositSettle->created_by = Auth::instance()->get_user()->username;
			$this->depositSettle->save();
			
			// Clear form
			$this->depositSettle = new Model_DepositSettle();
		} catch (ORM_Validation_Exception $e) {
			$db->rollback();
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
		$orm = Model::factory('depositSettle')
				->with('order')
				->join('customer')->on('customer.id', '=', 'order.customer_id');
	
		if ($this->order_id != '') {
			$orm->where('order_id', '=', $this->order_id);
		}
	
		return $orm;
	}
	
	public function getQueryString() {
		return '&order_id='.$this->order_id;
	}
}