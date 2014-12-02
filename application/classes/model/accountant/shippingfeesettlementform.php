<?php
class Model_Accountant_ShippingFeeSettlementForm extends Model_PageForm {
	const ACTION_CONFIRM_INIT = 'confirm_init';
	const ACTION_CANCEL_INIT = 'cancel_init';
	const ACTION_CONFIRM = 'confirm';
	const ACTION_CANCEL = 'cancel';
	
	public $action;
	public $delivery_note_id;
	public $customer_id;
	public $create_date_from;
	public $create_date_to;
	
	public $shippingFeeDeliveryNotes;
	public $errors;
	
	// Confirm / Cancel
	public $shippingDeliveryNote;
	public $remark;
	
	public $page_url = 'accountant/shipping_fee_settlement';
	
	public function populate($post) {
		parent::populate($post);
		
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->delivery_note_id = isset($post['delivery_note_id']) ? trim($post['delivery_note_id']) : NULL;
		$this->customer_id = isset($post['customer_id']) ? trim($post['customer_id']) : NULL;
		$this->create_date_from = isset($post['create_date_from']) ? trim($post['create_date_from']) : NULL;
		$this->create_date_to = isset($post['create_date_to']) ? trim($post['create_date_to']) : NULL;
		
		if ($this->action == self::ACTION_CONFIRM || $this->action == self::ACTION_CANCEL) {
			$this->remark = isset($post['remark']) ? trim($post['remark']) : NULL;
		}
	}
	
	public function processSearchAction() {
		$this->shippingFeeDeliveryNotes = $this->search();
	}
	
	public function processConfirmInit() {
		$this->shippingDeliveryNote = Model::factory('shippingFeeDeliveryNote')
										->with('customer')
										->where('shippingfeedeliverynote.id', '=', $this->delivery_note_id)
										->select('cust_code')
										->find();
	
		return $this->shippingDeliveryNote->loaded() ? true : false;
	}
	
	public function processConfirmAction() {
		$result = $this->confirm();
		$this->processSearchAction();
		return $result;
	}
	
	public function processCancelInit() {
		$this->shippingDeliveryNote = Model::factory('shippingFeeDeliveryNote')
										->with('customer')
										->where('shippingfeedeliverynote.id', '=', $this->delivery_note_id)
										->select('cust_code')
										->find();
	
		return $this->shippingDeliveryNote->loaded() ? true : false;
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
			$deliveryNote = new Model_ShippingFeeDeliveryNote($this->delivery_note_id);
				
			if (!$deliveryNote->loaded()) {
				$this->errors[] = 'Record not found';
				return false;
			} else if ($deliveryNote->is_settle != Model_ShippingFeeDeliveryNote::SETTLE_NO) {
				$this->errors[] = 'The invoice has already been settled.';
				return false;
			}
				
			// Updated order's confirmed amount
			$deliveryNote->is_settle = Model_ShippingFeeDeliveryNote::SETTLE_YES;
			$deliveryNote->remark = $this->remark;
			$deliveryNote->settle_date = DB::expr('current_timestamp');
			$deliveryNote->save();
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
			$deliveryNote = new Model_ShippingFeeDeliveryNote($this->delivery_note_id);
	
			if (!$deliveryNote->loaded()) {
				$this->errors[] = 'Record not found';
				return false;
			} else if ($deliveryNote->is_settle != Model_ShippingFeeDeliveryNote::SETTLE_NO) {
				$this->errors[] = 'The invoice has already been settled.';
				return false;
			}
	
			// Updated order's confirmed amount
			$deliveryNote->is_settle = Model_ShippingFeeDeliveryNote::SETTLE_VOID;
			$deliveryNote->remark = $this->remark;
			$deliveryNote->void_date = DB::expr('current_timestamp');
			$deliveryNote->save();
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
		$orm = Model::factory('shippingFeeDeliveryNote')
				->with('customer');

		if (!empty($this->customer_id)) {
			$orm->where('customer_id', '=', $this->customer_id);
		}
		
		if (!empty($this->create_date_from)) {
			$orm->where('shippingfeedeliverynote.create_date', '>=', $this->create_date_from);
		}
			
		if (!empty($this->create_date_to)) {
			$toDate = date('Y-m-d', strtotime($this->create_date_to.' + 1 days'));
			$orm->where('shippingfeedeliverynote.create_date', '<', $toDate);
		}
	
		return $orm;
	}
	
	public function getQueryString() {
		$query_string = '';

		if (!empty($this->customer_id)) {
			$query_string .= '&customer_id='.$this->customer_id;
		}
		
		if (!empty($this->create_date_from)) {
			$query_string .= '&create_date_from='.$this->create_date_from;
		}
			
		if (!empty($this->settle_date_to)) {
			$query_string .= '&settle_date_to='.$this->settle_date_to;
		}
		
		return $query_string;
	}
}