<?php
class Model_Accountant_InvoiceSettlementForm {
	public $action;
	public $search_customer_id;
	public $customer_id;
	
	public $invoices;
	public $invoicesWithRemainingAmt;
	public $invoiceSettle;
	public $invoiceSettleHistory;
	
	public function populate($post) {
		$this->action = isset($post['action']) ? $post['action'] : NULL;
		$this->search_customer_id = isset($post['search_customer_id']) ? $post['search_customer_id'] : NULL;
		$this->customer_id = isset($post['customer_id']) ? $post['customer_id'] : NULL;
		
		$this->invoiceSettle = new Model_InvoiceSettle();
		$this->invoiceSettle->values($post);
	}

	public function processSearchAction() {
		$this->customer_id = $this->search_customer_id;
		$this->search();
	}
	
	public function processSaveAction() {
		$this->search_customer_id = $this->customer_id;
		$result = $this->save();
		$this->search();
		
		return $result;
	}
	
	public function processShowRemainingAction() {
		$sub = DB::select('customer_id', array('max("id")', 'invoice_id'))
				->from('invoice')
				->group_by('customer_id');
		
		$this->invoicesWithRemainingAmt = Model::factory('invoice')
											->with('customer')
											->join('bank_account', 'left')->on('bank_account.id', '=', 'bank_id')
											->join(array($sub, 'tmp'), 'INNER')->on('tmp.invoice_id', '=', 'invoice.id')
											//->where('invoice.total_amt', '>', DB::expr('invoice.settle_amt'))
											->select('bank_name')
											->order_by('cust_code')
											->find_all();
	}
	
	private function search() {
		$this->invoices = Model::factory('invoice')
						->with('customer')
						->join('bank_account', 'left')->on('bank_account.id', '=', 'bank_id')
						->where('customer.id', '=', $this->customer_id)
						->select('bank_name')
						->order_by('invoice.id')
						->find_all();
		
		if (empty($this->invoices)) {
			$this->invoices = NULL;
			return;
		}
		
		$this->invoiceSettleHistory = Model::factory('invoiceSettle')
									->with('invoice')
									->join('bank_account')->on('bank_account.id', '=', 'invoicesettle.bank_id')
									->where('invoice.customer_id', '=', $this->customer_id)
									->order_by('id')
									->select('bank_name')
									->select('branch')
									->select('owner')
									->find_all();
	}
	
	private function save() {
		$db = Database::instance();
		$db->begin();
		
		$this->errors = array();
		
		try {
			// Get last invoice
			$lastInvoice = ORM::factory('invoice')
							->where('customer_id', '=', $this->customer_id)
							->order_by('id', 'desc')
							->find();
			
			/*if ($lastInvoice->settle_amt + $this->invoiceSettle->settle_amt > $lastInvoice->total_amt) {
				$this->errors[] = 'The settled amount is larger than invoice remaining amount.';
				return false;
			}*/
			$lastInvoice->settle_amt = $lastInvoice->settle_amt + $this->invoiceSettle->settle_amt + $this->invoiceSettle->fee;
			$lastInvoice->save();
			
			// Save to DB
			$this->invoiceSettle->invoice_id = $lastInvoice->id;
			$this->created_by = Auth::instance()->get_user()->username;
			$this->invoiceSettle->save();
			
			// Assign the payment to invoice
			// Get un-settled invoice
			$invoices = ORM::factory('invoice')
							->where('customer_id', '=', $this->customer_id)
							->where('invoice.is_settle', '=', Model_Invoice::SETTLED_NO)
							->order_by('id', 'asc')
							->find_all();
			
			$remainingAmount = $this->invoiceSettle->settle_amt + $this->invoiceSettle->fee;
			foreach ($invoices as $invoice) {
				if ($remainingAmount <= 0) {
					break;
				}
				
				// Assign payment to non product item first
				$invoiceDetails = ORM::factory('invoiceDetail')
								->where('invoice_id', '=', $invoice->id)
								->where('source', '<>', Model_InvoiceDetail::SOURCE_CONTAINER)
								->where('is_settle', '=', Model_InvoiceDetail::SETTLED_NO)
								->order_by('id')
								->find_all();
				
				if (sizeOf($invoiceDetails) > 0) {
					foreach ($invoiceDetails as $invoiceDetail) {
						$remainingAmount = $this->assignPayment($this->invoiceSettle->id, $invoiceDetail, $remainingAmount);
						
						if ($remainingAmount <= 0) {
							break;
						}
					}
				}
				
				// Assign payment to non-tax item
				if ($remainingAmount > 0) {
					$invoiceDetails = ORM::factory('invoiceDetail')
									->where('invoice_id', '=', $invoice->id)
									->where('source', '=', Model_InvoiceDetail::SOURCE_CONTAINER)
									->where('is_settle', '=', Model_InvoiceDetail::SETTLED_NO)
									->order_by('id')
									->find_all();
					
					if (sizeOf($invoiceDetails) > 0) {
						foreach ($invoiceDetails as $invoiceDetail) {
							$remainingAmount = $this->assignPayment($this->invoiceSettle->id, $invoiceDetail, $remainingAmount);
							
							if ($remainingAmount <= 0) {
								break;
							}
						}
					}
				}
				
				// Update invoice's status 
				$result = DB::select(array(DB::expr('COUNT(invoice_detail.id)'), 'count'))
						->from('invoice_detail')
						->where('invoice_id', '=', $invoice->id)
						->where('is_settle', '=', Model_InvoiceDetail::SETTLED_NO)
						->execute();
				
				if ($result[0]['count'] == 0) {
					// All invoice items are settled
					$invoice->is_settle = Model_Invoice::SETTLED_YES;
					$invoice->save();
				}
			}
			
			$this->invoiceSettle->remaining_amt = $remainingAmount;
			$this->invoiceSettle->save();
			
			// Clear form
			$this->invoiceSettle = new Model_InvoiceSettle();
		} catch (ORM_Validation_Exception $e) {
			foreach ($e->errors('accountant') as $error) {
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
	
	private function assignPayment($invoice_settle_id, $invoiceDetail, $paymentAmount) {
		$invoiceSettleDetail = new Model_InvoiceSettleDetail();
		$invoiceSettleDetail->invoice_settle_id = $invoice_settle_id;
		$invoiceSettleDetail->invoice_detail_id = $invoiceDetail->id;
		
		$remainingAmount = $paymentAmount;
		$invoiceDetailRemainingAmt = $invoiceDetail->total - $invoiceDetail->settle_amt;
		if ($invoiceDetailRemainingAmt > $remainingAmount) {
			// Payment not enough
			$invoiceSettleDetail->settle_amt = $remainingAmount;
			
			// Update invoice item
			$invoiceDetail->settle_amt += $remainingAmount;
			$invoiceDetail->save();
			
			$remainingAmount = 0;
		} else {
			// Payment enough
			$invoiceSettleDetail->settle_amt = $invoiceDetailRemainingAmt;
			
			// Update invoice item
			$invoiceDetail->settle_amt += $invoiceDetailRemainingAmt;
			$invoiceDetail->is_settle = Model_InvoiceDetail::SETTLED_YES;
			$invoiceDetail->save();
			
			$remainingAmount = $remainingAmount - $invoiceDetailRemainingAmt;
			
			// Update order item status
			if ($invoiceDetail->source == Model_InvoiceDetail::SOURCE_CONTAINER) {
				// Find corresponding order product
				$orderProduct = ORM::factory('orderProduct')
							->join('container')->on('container.order_product_id', '=', 'orderproduct.id')
							->join('delivery_note_detail')->on('container.id', '=', 'delivery_note_detail.container_id')
							->where('delivery_note_detail.id', '=', $invoiceDetail->delivery_note_detail_id)
							->select(array('container.source', 'container_source'))
							->find();
				
				if (($orderProduct->container_source == Model_Container::SOURCE_FACTORY && $orderProduct->factory_status == Model_OrderProduct::STATUS_INVOICE_GENERATED)
						|| ($orderProduct->container_source == Model_Container::SOURCE_JP && $orderProduct->jp_status == Model_OrderProduct::STATUS_INVOICE_GENERATED)) {
					// All QTY of order's product has already genereated to invoice
					$result = DB::select(array(DB::expr('COUNT(invoice_detail.id)'), 'count'))
								->from('container')
								->join('delivery_note_detail')->on('delivery_note_detail.container_id', '=', 'container.id')
								->join('invoice_detail')->on('invoice_detail.delivery_note_detail_id', '=', 'delivery_note_detail.id')
								->where('container.order_product_id', '=', $orderProduct->id)
								->where('container.source', '=', $orderProduct->container_source)
								->where('invoice_detail.is_settle', '=', Model_InvoiceDetail::SETTLED_NO)
								->execute();
					
					if ($result[0]['count'] == 0) {
						// All invoice items are settled
						if ($orderProduct->container_source == Model_Container::SOURCE_FACTORY) {
							$orderProduct->factory_status = Model_OrderProduct::STATUS_COMPLETE;
						} else if ($orderProduct->container_source == Model_Container::SOURCE_JP) {
							$orderProduct->jp_status = Model_OrderProduct::STATUS_COMPLETE;
						}
						$orderProduct->save();
					}
					
					// Update order's status
					if ($orderProduct->factory_status == Model_OrderProduct::STATUS_COMPLETE && $orderProduct->jp_status == Model_OrderProduct::STATUS_COMPLETE) {
						$result = DB::select(array(DB::expr('COUNT(order_product.id)'), 'count'))
								->from('order_product')
								->where('order_product.order_id', '=', $orderProduct->id)
								->and_where_open()
								->where('jp_status', '<>', Model_Order::STATUS_COMPLETE)
								->or_where('factory_status', '<>', Model_Order::STATUS_COMPLETE)
								->and_where_close()
								->execute();
						
						if ($result[0]['count'] == 0) {
							$order = new Model_Order($orderProduct->order_id);
							$order->status = Model_Order::STATUS_COMPLETE;
							$order->save();
						}
					}
				}
			}
		}
		
		$invoiceSettleDetail->save();
		
		return $remainingAmount;
	}
	
}
