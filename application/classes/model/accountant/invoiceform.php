<?php
class Model_Accountant_InvoiceForm extends Model_PageForm {
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
		$customer = new Model_Customer($this->customer_id);
		if ($customer->parent_customer_id != NULL) {
			$parentCustomer = new Model_Customer($customer->parent_customer_id);
			$this->errors[] = 'The customer is belonging to customer['.$parentCustomer->cust_code.']. Please select customer['.$parentCustomer->cust_code.'] to generate 請求書.';
			$isValid = false;
		}
		
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
			$customerIds = array();
			$customerIds[] = $customer->id;
			
			// Find children customer
			$childCustomers = ORM::factory('customer')
								->where('parent_customer_id', '=', $customer->id)
								->find_all();
			foreach ($childCustomers as $childCustomer) {
				$customerIds[] = $childCustomer->id;
			}
			
			// Find delivery note detail by customer
			// Find delivery note
			// Product detail
			$deliveryNotes = ORM::factory('deliveryNote')
									->where('deliverynote.customer_id', 'in', $customerIds)
									->where('deliverynote.invoice_id', '=', NULL)
									->where('deliverynote.create_date', '<', date('Y-m-d', strtotime($this->bill_date_to . "+1 days")))
									->find_all();
			
			// Product detail
			/* $deliveryNoteDetails = ORM::factory('deliveryNoteDetail')
							->with('deliveryNote')
							->with('container')
							->join('order_product')->on('order_product.id', '=', 'container.order_product_id')
							->where('deliveryNote.customer_id', '=', $this->customer_id)
							//->where('deliveryNote.create_date', '>=', $this->bill_date_from)
							->where('deliveryNote.create_date', '<', date('Y-m-d', strtotime($this->bill_date_to . "+1 days")))
							->where('container.status', '=', Model_Container::STATUS_DELIVERY_NOTE_GENREATED)
							->select('deliveryNote.tax_rate')
							->find_all();
			
			// Extra detail
			$deliveryNoteExtraDetails = ORM::factory('deliveryNoteExtraDetail')
										->with('deliveryNote')
										->where('deliveryNote.customer_id', '=', $this->customer_id)
										//->where('deliveryNote.create_date', '>=', $this->bill_date_from)
										->where('deliveryNote.create_date', '<', date('Y-m-d', strtotime($this->bill_date_to . "+1 days")))
										->find_all(); 
			
			if (sizeOf($deliveryNoteDetails) == 0 && sizeOf($deliveryNoteExtraDetails) == 0) {
				$this->errors[] = 'No item is available for generating 請求書';
				return false;
			}*/
			
			if (sizeOf($deliveryNotes) == 0) {
				$this->errors[] = 'No item is available for generating 請求書';
				return false;
			}
			
			if ($customer->office_address_id == 0) {
				$this->errors[] = 'Please set 公司地址 in [Sales > 客戶列表]';
				return false;
			}
			
			// Get total invoice amount
			$totalTax = 0;
			$invoiceDetails = array();
			$invoiceExtraDetails = array();
			$relatedOrderIdsAndInvoiceAmt = array();
			foreach ($deliveryNotes as $deliveryNote) {
				// Order's Product
				$deliveryNoteDetails = ORM::factory('deliveryNoteDetail')
										->where('deliverynotedetail.delivery_note_id', '=', $deliveryNote->id)
										->find_all();
				
				foreach ($deliveryNoteDetails as $deliveryNoteDetail) {
					$invoiceDetail = new Model_InvoiceDetail();
					$invoiceDetail->delivery_note_detail_id = $deliveryNoteDetail->id;
					$invoiceDetail->product_cd = $deliveryNoteDetail->product_cd;
					$invoiceDetail->description = $deliveryNoteDetail->description;
					$invoiceDetail->qty = $deliveryNoteDetail->qty;
					
					if ($deliveryNoteDetail->currency == Model_DeliveryNoteDetail::CURRENCY_JPY) {
						// JPY
						$invoiceDetail->market_price_rmb = GlobalFunction::convertJPY2RMB($deliveryNoteDetail->market_price, $rmbJPYRate->rate);
						$invoiceDetail->market_price = $deliveryNoteDetail->market_price;
					} if ($deliveryNoteDetail->currency == Model_DeliveryNoteDetail::CURRENCY_RMB) {
						// RMB
						$invoiceDetail->market_price_rmb = $deliveryNoteDetail->market_price;
						$invoiceDetail->market_price = GlobalFunction::convertRMB2JPY($deliveryNoteDetail->market_price, $rmbJPYRate->rate);
					}
					
					$invoiceDetail->total = $invoiceDetail->market_price * $invoiceDetail->qty;
					
					$invoiceDetail->is_tax = $deliveryNoteDetail->is_tax;
					$invoiceDetail->source = $deliveryNoteDetail->source;
					
					if ($invoiceDetail->is_tax == Model_OrderProduct::TAX_INCLUDE) {
						$totalTax += GlobalFunction::roundJPY($invoiceDetail->total * $deliveryNote->tax_rate);
					}

					$invoiceDetails[] = $invoiceDetail;

					// Change container's status to "INVOICE_GENERATED"
					if ($deliveryNoteDetail->container_id != 0) {
						$container = new Model_Container($deliveryNoteDetail->container_id);
						$container->status = Model_Container::STATUS_INVOICE_GENERATED;
						$container->update();
						
						// Update order product's QTY
						if ($container->order_product_id != NULL) {
							$orderProduct = new Model_OrderProduct($container->order_product_id);
							if ($container->source == Model_Container::SOURCE_FACTORY) {
								// From factory
								$orderProduct->factory_invoice_qty += $deliveryNoteDetail->qty;
									
								if ($orderProduct->factory_invoice_qty == $orderProduct->factory_qty) {
									$orderProduct->factory_status = Model_OrderProduct::STATUS_INVOICE_GENERATED;
								}
									
								$orderProduct->save();
							} else {
								// From JP
								$orderProduct->jp_invoice_qty += $deliveryNoteDetail->qty;
									
								if ($orderProduct->jp_invoice_qty == $orderProduct->jp_qty) {
									$orderProduct->jp_status = Model_OrderProduct::STATUS_INVOICE_GENERATED;
								}
									
								$orderProduct->save();
							}
							
							// Get ORDER_ID for calculating deposit
							if (!array_key_exists($orderProduct->order_id, $relatedOrderIdsAndInvoiceAmt)) {
								$relatedOrderIdsAndInvoiceAmt[$orderProduct->order_id] = 0;
							}
							
							$relatedOrderIdsAndInvoiceAmt[$orderProduct->order_id] += $invoiceDetail->market_price_rmb * $invoiceDetail->qty * (1 + $deliveryNote->tax_rate);
						}
					} else if ($deliveryNoteDetail->source == Model_DeliveryNoteDetail::SOURCE_DELIVERY_FEE) {
						// Get ORDER_ID for calculating deposit
						$orderProduct = new Model_OrderProduct($deliveryNoteDetail->reference_id);
						
						if (!array_key_exists($orderProduct->order_id, $relatedOrderIdsAndInvoiceAmt)) {
							$relatedOrderIdsAndInvoiceAmt[$orderProduct->order_id] = 0;
						}
						
						$relatedOrderIdsAndInvoiceAmt[$orderProduct->order_id] += $invoiceDetail->market_price_rmb;
					}
				}
				
				// Format tax value
				//$totalTax = ceil($totalTax);
				
				/* Process extra detail */
				/* $deliveryNoteExtraDetails = ORM::factory('deliveryNoteExtraDetail')
											->where('deliverynoteextradetail.delivery_note_id', '=', $deliveryNote->id)
											->find_all();
				
				foreach ($deliveryNoteExtraDetails as $deliveryNoteExtraDetail) {
					$invoiceExtraDetail = new Model_InvoiceExtraDetail();
					$invoiceExtraDetail->delivery_note_extra_detail_id = $deliveryNoteExtraDetail->id;
					$invoiceExtraDetail->description = $deliveryNoteExtraDetail->description;
					$invoiceExtraDetail->currency = $deliveryNoteExtraDetail->currency;
					//$invoiceExtraDetail->total = ceil($deliveryNoteExtraDetail->total * $deliveryNoteExtraDetail->rmb_to_jpy_rate);
					if ($deliveryNoteExtraDetail->currency == Model_DeliveryNoteExtraDetail::CURRENCY_JPY) {
						// JPY
						$invoiceExtraDetail->total = $deliveryNoteExtraDetail->total;
					} else {
						// RMB
						$invoiceExtraDetail->total = $deliveryNoteExtraDetail->total * $rmbJPYRate->rate;
					}
					
					$currentMonthAmount += $invoiceExtraDetail->total;
						
					$invoiceExtraDetails[] = $invoiceExtraDetail;
				} */
			}
			
			/**
			 * Add tax item
			 */
			$invoiceDetail = new Model_InvoiceDetail();
			$invoiceDetail->delivery_note_detail_id = 0;
			$invoiceDetail->product_cd = '';
			$invoiceDetail->description = '';
			$invoiceDetail->qty = 0;
			$invoiceDetail->market_price_rmb = 0;
			$invoiceDetail->market_price = 0;
			$invoiceDetail->total = $totalTax;
			$invoiceDetail->source = Model_InvoiceDetail::SOURCE_TAX;
			$invoiceDetails[] = $invoiceDetail;
			
			/**
			 * Deposit
			 */
			$depositInvoiceDeteails = $this->calculateDepositItems($relatedOrderIdsAndInvoiceAmt, $rmbJPYRate->rate);
			$invoiceDetails = array_merge($invoiceDetails, $depositInvoiceDeteails);
			
			// Calculate total invoice amount
			$currentMonthAmount = 0;
			foreach ($invoiceDetails as $invoiceDetail) {
				if ($invoiceDetail->source == Model_InvoiceDetail::SOURCE_TAX) {
					// SKip TAX item
					continue;
				}
				
				$currentMonthAmount += $invoiceDetail->total;
			}
			
			// Find last month invoice
			$lastInvoice = ORM::factory('invoice')
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
			$invoice = new Model_Invoice();
			$invoice->invoice_no = $this->generateInvoiceNo($customer->cust_code);
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
				$invoiceDetail->invoice_id = $invoice->id;
				$invoiceDetail->save();
			}
			
			foreach ($invoiceExtraDetails as $invoiceExtraDetail) {
				$invoiceExtraDetail->invoice_id = $invoice->id;
				$invoiceExtraDetail->save();
			}
			
			$results = DB::select('delivery_note_id')
						->distinct(true)
						->from('invoice_detail')
						->join('delivery_note_detail')->on('delivery_note_detail.id', '=', 'invoice_detail.delivery_note_detail_id')
						->where('invoice_id', '=', $invoice->id)
						->order_by('delivery_note_id')
						->execute();
			
			if ($results->count() > 0) {
				foreach ($results as $result) {
					$invoice->delivery_note_id_list .= '['.$result['delivery_note_id'].'],';
				}
				
				$invoice->delivery_note_id_list = substr($invoice->delivery_note_id_list, 0, strlen($invoice->delivery_note_id_list) - 1);
			} else {
				$invoice->delivery_note_id_list = '';
			}
			
			$invoice->save();
			
			foreach ($deliveryNotes as $deliveryNote) {
				$deliveryNote->invoice_id = $invoice->id;
				$deliveryNote->save();
			}
			
			// Clear form
			$this->bill_date_from = NULL;
			$this->bill_date_to = NULL;
			$this->due_date = NULL;
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
			//throw $e;
		}
		
		$db->commit();
		
		return true;
	}
	
	private function calculateDepositItems($relatedOrderIdsAndInvoiceAmt, $rmbJPYRate) {
		$invoiceDetails = array();
		foreach ($relatedOrderIdsAndInvoiceAmt as $orderId=>$invoiceAmt) {
			$order = new Model_Order($orderId);
			
			// Currency = RMB
			$result = DB::select(array(DB::expr('ifnull(SUM(market_price_rmb), 0)'), 'deposit_total'))
						->from('invoice_detail')
						->where('source', '=', Model_InvoiceDetail::SOURCE_DEPOSIT)
						->where('reference_id', '=', $orderId)
						->execute();
			
			$invoicedDepositAmt = $result[0]['deposit_total'];
			
			//$remainingDeposit = $order->deposit_amt + $invoicedDepositAmt;
			$remainingDeposit = $order->confirm_deposit_amt + $invoicedDepositAmt; // Invoice Deposit Amount is negative value
			
			if ($remainingDeposit <= 0) {
				// SKip
				continue;
			}
			
			$invoiceDepositAmt = 0;
			if ($invoiceAmt >= $remainingDeposit) {
				$invoiceDepositAmt = $remainingDeposit  * -1;
			} else {
				$invoiceDepositAmt = $invoiceAmt * -1;
			}
			
			$invoiceDetail = new Model_InvoiceDetail();
			$invoiceDetail->delivery_note_detail_id = 0;
			$invoiceDetail->product_cd = '';
			$invoiceDetail->description = 'Deposit (Order No. '.$orderId.')';
			$invoiceDetail->qty = 1;
			$invoiceDetail->market_price_rmb = $invoiceDepositAmt;
			$invoiceDetail->market_price = GlobalFunction::convertRMB2JPY($invoiceDepositAmt, $rmbJPYRate);
			$invoiceDetail->total = $invoiceDetail->market_price;
			$invoiceDetail->source = Model_InvoiceDetail::SOURCE_DEPOSIT;
			$invoiceDetail->reference_id = $orderId;
			$invoiceDetails[] = $invoiceDetail;
		}
		
		return $invoiceDetails;
	}
	
	private function generateInvoiceNo($cust_code) {
		$runningSequence = new Model_CustomerRunningSequence($cust_code);
		if (!$runningSequence->loaded()) {
			// Not exist
			$runningSequence = new Model_CustomerRunningSequence();
			$runningSequence->cust_code = $cust_code;
			$runningSequence->delivery_note_seq = 0;
			$runningSequence->invoice_seq = 0;
		}
		
		$runningSequence->invoice_seq++;
		$runningSequence->save();
		
		return 'I-'.$cust_code.'-'.str_pad($runningSequence->invoice_seq, 8, '0', STR_PAD_LEFT);
	}
	
	private function getInvoiceBillDateFrom($customer_id) {
		$invoice = ORM::factory('invoice')
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
		->order_by('invoice.id', 'desc')
		->limit($limit)
		->offset($offset)
		->find_all();
	}
	
	public function getCriteria() {
		$orm = ORM::factory('invoice')
		->join('customer')->on('customer.id', '=', 'invoice.customer_id')
		->where('invoice.customer_id', '=', $this->customer_id);
		return $orm;
	}
	
	public function getQueryString() {
		return '&customer_id='.$this->customer_id;
	}
}