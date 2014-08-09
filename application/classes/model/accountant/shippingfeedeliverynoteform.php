<?php
class Model_Accountant_ShippingFeeDeliveryNoteForm extends Model_PageForm {
	
	public $action;
	public $customer_id;
	
	public $shippingFeeDeliveryNotes;
	
	public $pendingShippingFees;

	public $errors;
	
	public $page_url = 'accountant/shipping_fee_delivery_note';
	
	public function populate($post) {
		parent::populate($post);
		
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->customer_id = isset($post['customer_id']) ? $post['customer_id'] : NULL;
	}
	
	public function processSearchAction() {
		/* if ($this->customer_id == NULL) {
			$customer = ORM::factory('customer')->order_by('cust_code')->find();
			$this->customer_id = $customer->id;
			//$this->customer_id = 0;
		} */
		
		$this->shippingFeeDeliveryNotes = $this->search();
		
		$this->getPendingShippingFees();
	}
	
	public function processScanAction() {
		$result = false;
		
		// Generate delivery note of specfic customer
		try {
			$result = $this->create_delivery_note($this->customer_id);
		} catch (Exception $e) {
			if ($e->getMessage() == 'NO_ITEM') {
				$this->errors[] = 'No item is available for generating 請求書';
				
			} else {
				$this->errors[] = $e->getMessage();
			}
		}
		
		$this->processSearchAction();
		
		return $result;
	}
	
	private function create_delivery_note($customer_id) {
		$db = Database::instance();
		$db->begin();
		
		$this->errors = array();
		
		/**
		 * Create delivery note
		 * (Currency = RMB)
		 */
		// Find shipping fee by customer
		$shippingFees = ORM::factory('shippingFee')
					->where('customer_id', '=', $customer_id)
					->where('status', '=', Model_ShippingFee::STATUS_INIT)
					->find_all();

		if (sizeof($shippingFees) == 0) {
			throw new Exception('NO_ITEM');
			//return false;
		}
	
		try {
			// Find rate
			$rmbJPYRate = Model_Rate::getCurrentRate(Model_Rate::RATE_FROM_RMB, Model_Rate::RATE_TO_JPY);
			if ($rmbJPYRate == NULL) {
				$this->errors[] = 'Can\'t find rate RMB <-> JPY';
				return false;
			}
			
			$rmbUSDRate = Model_Rate::getCurrentRate(Model_Rate::RATE_FROM_RMB, Model_Rate::RATE_TO_USD);
			if ($rmbUSDRate == NULL) {
				$this->errors[] = 'Can\'t find rate RMB <-> USD';
				return false;
			}
			
			// Find tax rate
			$taxRateConfig = new Model_ProfitConfig15(Model_ProfitConfig15::CODE_TAX_RATE);
			$taxRate = $taxRateConfig-> value / 100.0;
			
			$customer = new Model_Customer($customer_id);
			if ($customer->office_address_id == 0) {
				$this->errors[] = 'Please set 公司地址 in [Sales > 客戶列表]';
				return false;
			}
			
			// Create delivery note
			$deliveryNote = new Model_ShippingFeeDeliveryNote();
			$deliveryNote->customer_id = $customer_id;
			$deliveryNote->rmb_to_jpy_rate = $rmbJPYRate->rate;
			$deliveryNote->rmb_to_usd_rate = $rmbUSDRate->rate;
			$deliveryNote->created_by = Auth::instance()->get_user()->username;
			$deliveryNote->last_print_date = NULL;
			$deliveryNote->save();
			
			// Create delivery note's items
			$total_amt = 0;
			foreach ($shippingFees as $shippingFee) {
				$total_amt += $shippingFee->amount;
				$shippingFee->status = Model_ShippingFee::STATUS_COMPLETE;
				$shippingFee->shipping_fee_delivery_note_id = $deliveryNote->id;
				$shippingFee->save();
			}
			
			/**
			 * Save to DB
			 */
			$deliveryNote->total_amt = $total_amt;
			$deliveryNote->save();
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		
		return true;
	}
	
	private function getPendingShippingFees() {
		// Find shipping fee by customer
		$orm = ORM::factory('shippingFee')
				->with('customer')
				->where('status', '=', Model_ShippingFee::STATUS_INIT)
				->select('customer.cust_code')
				->order_by('create_date', 'desc');
		
		if (!empty($this->customer_id)) {
			$orm->where('customer_id', '=', $this->customer_id);
		}
		
		$this->pendingShippingFees = $orm->find_all();
	}

// Overrided function
	public function getData($limit, $offset) {
		return $this->getCriteria()
					->select('customer.cust_code')
					->order_by('shippingfeedeliverynote.id', 'desc')
					->limit($limit)
					->offset($offset)
					->find_all();
	}
	
	public function getCriteria() {
		$orm = ORM::factory('shippingFeeDeliveryNote')
				->with('customer');
		
		if ($this->customer_id != 0) {
			$orm->where('shippingfeedeliverynote.customer_id', '=', $this->customer_id);
		}
		
		return $orm;
	}
	
	public function getQueryString() {
		return '&customer_id='.$this->customer_id;
	}
}