<?php
class Model_Accountant_OrderReturnInvoiceForm extends Model_PageForm {
	public $action;
	public $bill_date_from;
	public $bill_date_to;
	public $customer_id;
	public $isFirstInvoice;
	
	public $invoices;
	public $errors;
	
	public function populate($post) {
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->bill_date_from = isset($post['bill_date_from']) ? $post['bill_date_from'] : NULL;
		$this->bill_date_to = isset($post['bill_date_to']) ? $post['bill_date_to'] : NULL;
		$this->due_date = isset($post['due_date']) ? $post['due_date'] : NULL;
		$this->customer_id = isset($post['customer_id']) ? $post['customer_id'] : NULL;
	}
	
	public function processSearchAction() {
		if ($this->customer_id == NULL) {
			$customer = ORM::factory('customer')->order_by('cust_code')->find();
			$this->customer_id = $customer->id;
		}
	
		$this->isFirstInvoice = true;
		$bill_date_from = $this->getInvoiceBillDateFrom($this->customer_id);
		if ($bill_date_from != NULL) {
			$this->isFirstInvoice = false;
			$this->bill_date_from = $bill_date_from;
		}
	
		$this->invoices = $this->search();
	}
	
	public function processScanAction() {
		$result = $this->createInvoice();
		$this->processSearchAction();
	
		return $result;
	}
	
	private function createInvoice() {
		$this->errors = array();
	
		$bill_date_from = $this->getInvoiceBillDateFrom($this->customer_id);
		if ($bill_date_from != NULL) {
			$this->bill_date_from = $bill_date_from;
		}
	
		// Validation
		$isValid = true;
		if ($this->bill_date_from == '' || $this->bill_date_to == '') {
			$this->errors[] = 'Date From and Date To must be filled in.';
			$isValid = false;
		} else if ($this->bill_date_to < $this->bill_date_from) {
			$this->errors[] = 'Date To must be larger than Date From';
			$isValid = false;
		}
	
		if ($this->due_date == '') {
			$this->errors[] = 'Due Date must be filled in.';
			$isValid = false;
		}
	
		if (!$isValid) {
			return false;
		}
	
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
	
		$db = Database::instance();
		$db->begin();
	
		try {
			// Find delivery note detail by customer
			// Find delivery note
			// Product detail
			$deliveryNoteDetails = ORM::factory('deliveryNoteDetail')
							->join('delivery_note')->on('delivery_note.id', '=', 'deliverynotedetail.delivery_note_id')
							->where('delivery_note.customer_id', '=', $this->customer_id)
							->where('deliverynotedetail.status', '=', Model_DeliveryNoteDetail::STATUS_INIT)
							->where('deliverynotedetail.source', '=', Model_DeliveryNoteDetail::SOURCE_ORDER_RETURN)
							->where('delivery_note.create_date', '<', date('Y-m-d', strtotime($this->bill_date_to . "+1 days")))
							->find_all();
			
			if (sizeOf($deliveryNoteDetails) == 0) {
				$this->errors[] = 'No item is available for generating 請求書';
				return false;
			}
				
			$customer = new Model_Customer($this->customer_id);
			if ($customer->office_address_id == 0) {
				$this->errors[] = 'Please set 公司地址 in [Sales > 客戶列表]';
				return false;
			}
				
			// Get total invoice amount
			$currentMonthAmount = 0;
			$totalTax = 0;
			$invoiceDetails = array();
			foreach ($deliveryNoteDetails as $deliveryNoteDetail) {
				$invoiceDetail = new Model_OrderReturnInvoiceDetail();
				$invoiceDetail->delivery_note_detail_id = $deliveryNoteDetail->id;
				$invoiceDetail->description = $deliveryNoteDetail->description;
				$invoiceDetail->product_cd = $deliveryNoteDetail->product_cd;
				$invoiceDetail->qty = $deliveryNoteDetail->qty;
				
				if ($deliveryNoteDetail->currency == Model_DeliveryNoteDetail::CURRENCY_JPY) {
					$invoiceDetail->market_price_rmb = GlobalFunction::roundUpTo($deliveryNoteDetail->market_price / $rmbJPYRate->rate, 2);;
					$invoiceDetail->market_price = $deliveryNoteDetail->market_price;
				} if ($deliveryNoteDetail->currency == Model_DeliveryNoteDetail::CURRENCY_RMB) {
					$invoiceDetail->market_price_rmb = $deliveryNoteDetail->market_price;
					$invoiceDetail->market_price = $deliveryNoteDetail->market_price * $rmbJPYRate->rate;
				}
				
				$invoiceDetail->total = $invoiceDetail->market_price * $invoiceDetail->qty;
				$invoiceDetail->remark = $deliveryNoteDetail->remark;
					
				$currentMonthAmount += $invoiceDetail->total;
					
				$invoiceDetails[] = $invoiceDetail;
				
				// Update detail status
				$deliveryNoteDetail->status = Model_DeliveryNoteDetail::STATUS_INVOICE_GENERATED;
				$deliveryNoteDetail->save();
			}
				
			// Find last month invoice
			$lastInvoice = ORM::factory('orderReturnInvoice')
							->where('customer_id', '=', $this->customer_id)
							->order_by('bill_date_from', 'desc')
							->find();
				
			// Last month invoice' settled amount
			$lastMonthAmt = 0;
			$lastMonthSettleAmt = 0;
			if ($lastInvoice->loaded()) {
				$lastMonthAmt = $lastInvoice->total_amt;
				$lastMonthSettleAmt = $lastInvoice->settle_amt;
			}
				
			// Create invoice
			$invoice = new Model_OrderReturnInvoice();
			$invoice->customer_id = $this->customer_id;
			$invoice->bill_date_from = $this->bill_date_from;
			$invoice->bill_date_to = $this->bill_date_to;
			$invoice->due_date = $this->due_date;
			$invoice->last_month_amt = $lastMonthAmt;
			$invoice->last_month_settle = $lastMonthSettleAmt;
			$invoice->current_month_amt = $currentMonthAmount;
			$invoice->total_tax = $totalTax;
			$invoice->total_amt = $lastMonthAmt - $lastMonthSettleAmt + $currentMonthAmount + $totalTax;
			$invoice->rmb_to_jpy_rate = $rmbJPYRate->rate;
			$invoice->rmb_to_usd_rate = $rmbUSDRate->rate;
			$invoice->office_address_id = $customer->office_address_id;
			$invoice = $invoice->save();
	
			// Create invoice's items
			foreach ($invoiceDetails as $invoiceDetail) {
				$invoiceDetail->order_return_invoice_id = $invoice->id;
				$invoiceDetail->save();
			}
			
			// Clear form
			$this->bill_date_from = NULL;
			$this->bill_date_to = NULL;
			$this->due_date = NULL;
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
	
		$db->commit();
	
		return true;
	}
	
	private function getInvoiceBillDateFrom($customer_id) {
		$invoice = ORM::factory('orderReturnInvoice')
					->where('customer_id', '=', $customer_id)
					->order_by('bill_date_to', 'desc')
					->find();
	
		if ($invoice->loaded()) {
			return date('Y-m-d', strtotime($invoice->bill_date_to.' + 1 days'));
		} else {
			return NULL;
		}
	}
	
	// Overrided function
	public function getData($limit, $offset) {
		return $this->getCriteria()
					->select('customer.cust_code')
					->order_by('orderreturninvoice.id', 'desc')
					->limit($limit)
					->offset($offset)
					->find_all();
	}
	
	public function getCriteria() {
		$orm = ORM::factory('orderReturnInvoice')
				->join('customer')->on('customer.id', '=', 'orderreturninvoice.customer_id')
				->where('orderreturninvoice.customer_id', '=', $this->customer_id);
		return $orm;
	}
	
	public function getQueryString() {
		return '&customer_id='.$this->customer_id;
	}
}