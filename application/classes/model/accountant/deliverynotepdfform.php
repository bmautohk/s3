<?php
require_once APPPATH.'classes/vendor/tcpdf/tcpdf.php';

class Model_Accountant_DeliveryNotePDFForm {
	public $delivery_note_id;
	public $customer;
	public $deliveryNote;
	public $products;
	public $total;
	public $totalTax;
	public $prevOrderId;
	
	// JPY
	public $totalDetailAmtJPY;
	public $totalTaxAmtJPY;
	public $totalAmtJPY;
	
	public $errors;
	
	public function __construct($delivery_note_id) {
		$this->delivery_note_id = $delivery_note_id;
	}
	
	public function processPrintAction() {
		return $this->printDeliveryNote();
	}
	
	private function printDeliveryNote() {
		$this->deliveryNote = ORM::factory('deliveryNote')
								->where('id', '=', $this->delivery_note_id)
								->find();
	
		$this->customer = $this->deliveryNote->customer;

		/* $orderDeliveryNoteDetails = ORM::factory('deliveryNoteDetail')
						->with('container')
						->join('order_product')->on('order_product.id', '=', 'container.order_product_id')
						->join('product_master', 'LEFT')->on('product_master.no_jp', '=', 'deliverynotedetail.product_cd')
						->select('product_desc')
						->select('order_product.order_id')
						->select('container.container_no')
						->select('container.source')
						->where('deliverynotedetail.delivery_note_id', '=', $this->delivery_note_id)
						->where('container.source', 'in', array(Model_Container::SOURCE_FACTORY, Model_Container::SOURCE_JP))
						->order_by('order_product.order_id')
						->find_all();
		
		$giftDeliveryNoteDetails = ORM::factory('deliveryNoteDetail')
							->with('container')
							->join('gift')->on('gift.id', '=', 'container.gift_id')
							->select('product_desc')
							->select('container.container_no')
							->select('container.source')
							->where('deliverynotedetail.delivery_note_id', '=', $this->delivery_note_id)
							->where('container.source', '=', Model_Container::SOURCE_GIFT)
							->order_by('gift.id')
							->find_all(); */
		
		$deliveryNoteDetails = ORM::factory('deliveryNoteDetail')
									->where('delivery_note_id', '=', $this->delivery_note_id)
									->order_by('id')
									->find_all();
		
		$this->total = 0;
		$this->totalTax = 0;
		$this->totalTaxJPY = 0;
		$this->totalDetailAmtJPY = 0;
		$this->products = array();
		
		$this->processDeliveryNote($deliveryNoteDetails);
		/* $this->processDeliveryNote($orderDeliveryNoteDetails);
		$this->processDeliveryNote($giftDeliveryNoteDetails); */
		
		// Extra detail
		/* $deliveryNoteExtraDetails = ORM::factory('deliveryNoteExtraDetail')
								->where('deliverynoteextradetail.delivery_note_id', '=', $this->delivery_note_id)
								->find_all();
		
		$this->processDeliveryNoteExtra($deliveryNoteExtraDetails); */
		
		$this->totalAmtJPY = $this->totalDetailAmtJPY + $this->totalTaxJPY;
		
		// Update last print date
		$this->errors = array();
		
		$db = Database::instance();
		$db->begin();
		
		try {
			$this->deliveryNote->last_print_date = DB::expr('current_timestamp');
			$this->deliveryNote->save();
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		
		// Generate pdf
		$this->genreatePDF();
		
		return true;
		
	}
	
	private function processDeliveryNote($deliveryNoteDetails) {
		$this->prevOrderId = 0;
	
		foreach ($deliveryNoteDetails as $deliveryNoteDetail) {
			$product = new Model_Accountant_DeliveryNoteProduct();
			
			if ($deliveryNoteDetail->source == Model_DeliveryNoteDetail::SOURCE_CONTAINER) {
				$this->processFromContainer($deliveryNoteDetail);
			} else if ($deliveryNoteDetail->source == Model_DeliveryNoteDetail::SOURCE_DELIVERY_FEE) {
				$this->processFromDeliveryFee($deliveryNoteDetail);
			} else if ($deliveryNoteDetail->source == Model_DeliveryNoteDetail::SOURCE_ORDER_RETURN) {
				$this->processFromOrderReturn($deliveryNoteDetail);
			}	
		}
	}
	
	private function processFromContainer($deliveryNoteDetail) {
		$product = new Model_Accountant_DeliveryNoteProduct();
		
		$container = new Model_Container($deliveryNoteDetail->container_id);
		
		if ($container->source != Model_Container::SOURCE_GIFT) {
			$orderProduct = ORM::factory('orderProduct')
							->join('temp_product_master', 'LEFT')->on('temp_product_master.order_product_id', '=', 'orderproduct.id')
							//->join('product_master', 'LEFT')->on('product_master.no_jp', '=', 'orderproduct.product_cd')
							->where('orderproduct.id', '=', $container->order_product_id)
							->select('product_desc')
							->find();
			
			$product->description = $orderProduct->product_cd.($orderProduct->is_tax == Model_OrderProduct::TAX_NOT_INCLUDE ? '＊' : '').'<br>'.$orderProduct->product_desc;
			$product->qty = $deliveryNoteDetail->qty;
			$product->market_price = $deliveryNoteDetail->market_price;
			$product->market_price_jpy = GlobalFunction::convertRMB2JPY($deliveryNoteDetail->market_price, $this->deliveryNote->rmb_to_jpy_rate);
			
			if ($deliveryNoteDetail->is_tax == Model_OrderProduct::TAX_INCLUDE) {
				$this->totalTaxJPY += GlobalFunction::roundJPY($product->market_price_jpy * $deliveryNoteDetail->qty * $this->deliveryNote->tax_rate);
			}
			
			// Remark
			$product->remark = 'Order No. '.$orderProduct->order_id;
			if ($container->container_no != '') {
				$product->remark .= '<br />櫃號: '.$container->container_no;
			}
			
			if ($orderProduct->order_id != $this->prevOrderId) {
				$this->prevOrderId = $orderProduct->order_id;
		
				$order = new Model_Order($orderProduct->order_id);
				
				if ($order->s1_client_name != '') {
					$product->remark .= '<br />受取人: '.$order->s1_client_name;
				}
		
				$address = '';
				if ($order->delivery_address1 != '') {
					$address .= '<br />'.$order->delivery_address1;
				}
				if ($order->delivery_address2 != '') {
					$address .= '<br />'.$order->delivery_address2;
				}
				if ($order->delivery_address3 != '') {
					$address .= '<br />'.$order->delivery_address3;
				}
		
				if ($address != '') {
					$product->remark .= '<br />送貨地址:'.$address;
				}
			}
			
			if ($deliveryNoteDetail->remark != '') {
				$product->remark .= '<br />'.$deliveryNoteDetail->remark;
			}
		} /* else {
			// Gift
			$gift = new Model_Gift($container->gift_id);
			
			$product->description = $gift->product_cd.'<br>'.$gift->product_desc;
			$product->qty = $deliveryNoteDetail->qty;
			$product->market_price = $deliveryNoteDetail->market_price;
			$product->market_price_jpy = $deliveryNoteDetail->market_price * $this->deliveryNote->rmb_to_jpy_rate;
		
			if ($container->container_no != '') {
				$product->remark .= '<br />櫃號: '.$container->container_no;
			}
		} */
		
		$product->total = $deliveryNoteDetail->total;
		$product->total_jpy = $product->market_price_jpy * $deliveryNoteDetail->qty;
		
		$this->products[] = $product;
		
		$this->totalDetailAmtJPY += $product->total_jpy;
	}
	
	private function processFromDeliveryFee($deliveryNoteDetail) {
		$product = new Model_Accountant_DeliveryNoteProduct();
		$product->description = $deliveryNoteDetail->description;
		$product->qty = '';
		$product->market_price = '';
		$product->market_price_jpy = '';
		//$product->remark = $deliveryNoteDetail->remark;
		
		if ($deliveryNoteDetail->currency == Model_DeliveryNoteDetail::CURRENCY_JPY) {
			// JPY
			$product->total = GlobalFunction::convertJPY2RMB($deliveryNoteDetail->total, $this->deliveryNote->rmb_to_jpy_rate); 
			$product->total_jpy = $deliveryNoteDetail->total;
		} else {
			// RMB
			$product->total = $deliveryNoteDetail->total;
			$product->total_jpy = GlobalFunction::convertRMB2JPY($deliveryNoteDetail->total, $this->deliveryNote->rmb_to_jpy_rate);
		}
		
		$this->products[] = $product;
		
		$this->totalDetailAmtJPY += $product->total_jpy;
	}
	
	private function processFromOrderReturn($deliveryNoteDetail) {
		/* $productMaster = ORM::factory('productMaster')
						->where('no_jp', '=', $deliveryNoteDetail->product_cd)
						->find(); */
		$productMaster = ORM::factory('tempProductMaster')
						->join('order_product')->on('order_product.id', '=', 'tempproductmaster.order_product_id')
						->join('container')->on('container.order_product_id', '=', 'order_product.id')
						->where('container.id', '=', $deliveryNoteDetail->container_id)
						->find();
		
		$product = new Model_Accountant_DeliveryNoteProduct();
		
		if ($productMaster->loaded()) {
			$product->description = $deliveryNoteDetail->product_cd.'<br>'.$productMaster->product_desc;
		} else {
			$product->description = $deliveryNoteDetail->product_cd;
		}
		
		$product->qty = $deliveryNoteDetail->qty;
		$product->remark = $deliveryNoteDetail->remark;
		
		if ($deliveryNoteDetail->currency == Model_DeliveryNoteDetail::CURRENCY_JPY) {
			// JPY
			$product->market_price = GlobalFunction::convertJPY2RMB($deliveryNoteDetail->market_price, $this->deliveryNote->rmb_to_jpy_rate);
			$product->market_price_jpy = $deliveryNoteDetail->market_price;
			$product->total = $product->market_price * $product->qty;
			$product->total_jpy = $deliveryNoteDetail->total;
		} else {
			// RMB
			$product->market_price = $deliveryNoteDetail->market_price;
			$product->market_price_jpy = GlobalFunction::convertRMB2JPY($deliveryNoteDetail->market_price, $this->deliveryNote->rmb_to_jpy_rate);
			$product->total = $deliveryNoteDetail->total;
			$product->total_jpy = $product->market_price_jpy * $product->qty;
		}
	
		$this->products[] = $product;
	
		$this->totalDetailAmtJPY += $product->total_jpy;
	}
	
	/* private function processDeliveryNote($deliveryNoteDetails) {
		$prevOrderId = 0;
		
		foreach ($deliveryNoteDetails as $deliveryNoteDetail) {
			$product = new Model_Accountant_DeliveryNoteProduct();
			
			if ($deliveryNoteDetail->source != Model_Container::SOURCE_GIFT) {
				$product->description = $deliveryNoteDetail->product_cd.'<br>'.$deliveryNoteDetail->product_desc;
				$product->qty = $deliveryNoteDetail->qty;
				$product->market_price = $deliveryNoteDetail->market_price;
				$product->market_price_jpy = $deliveryNoteDetail->market_price * $this->deliveryNote->rmb_to_jpy_rate;
		
				// Remark
				$product->remark = 'Order No. '.$deliveryNoteDetail->order_id;
				if ($deliveryNoteDetail->container_no != '') {
					$product->remark .= '<br />櫃號: '.$deliveryNoteDetail->container_no;
				}
		
				if ($deliveryNoteDetail->order_id != $prevOrderId) {
					$prevOrderId = $deliveryNoteDetail->order_id;
						
					$order = new Model_Order($deliveryNoteDetail->order_id);
						
					$address = '';
					if ($order->delivery_address1 != '') {
						$address .= '<br />'.$order->delivery_address1;
					}
					if ($order->delivery_address2 != '') {
						$address .= '<br />'.$order->delivery_address2;
					}
					if ($order->delivery_address3 != '') {
						$address .= '<br />'.$order->delivery_address3;
					}
						
					if ($address != '') {
						$product->remark .= '<br />送貨地址:'.$address;
					}
				}
			} else {
				// Gift
				$product->description = $deliveryNoteDetail->product_cd.'<br>'.$deliveryNoteDetail->product_desc;
				$product->qty = $deliveryNoteDetail->qty;
				$product->market_price = $deliveryNoteDetail->market_price;
				$product->market_price_jpy = $deliveryNoteDetail->market_price * $this->deliveryNote->rmb_to_jpy_rate;
				
				if ($deliveryNoteDetail->container_no != '') {
					$product->remark .= '<br />櫃號: '.$deliveryNoteDetail->container_no;
				}
			}
				
			$product->total = $deliveryNoteDetail->total;
			$product->total_jpy = $deliveryNoteDetail->total * $this->deliveryNote->rmb_to_jpy_rate;
				
			$this->products[] = $product;
				
			$this->totalDetailAmtJPY += $product->total_jpy;
		}
	} */
	
	/* private function processDeliveryNoteExtra($deliveryNoteExtraDetails) {
		foreach ($deliveryNoteExtraDetails as $deliveryNoteExtraDetail) {
			$product = new Model_Accountant_DeliveryNoteProduct();
			$product->description = $deliveryNoteExtraDetail->description;
			$product->qty = '';
			$product->market_price = '';
			$product->market_price_jpy = '';
			$product->remark = $deliveryNoteExtraDetail->remark;
				
			if ($deliveryNoteExtraDetail->currency == Model_DeliveryNoteExtraDetail::CURRENCY_JPY) {
				// JPY
				$product->total = GlobalFunction::roundUpTo($deliveryNoteExtraDetail->total * 1.0 / $this->deliveryNote->rmb_to_jpy_rate, 2);
				$product->total_jpy = $deliveryNoteExtraDetail->total;
			} else {
				// RMB
				$product->total = $deliveryNoteExtraDetail->total;
				$product->total_jpy = $deliveryNoteExtraDetail->total * $this->deliveryNote->rmb_to_jpy_rate;
			}
				
			$this->products[] = $product;
				
			$this->totalDetailAmtJPY += $product->total_jpy;
		}
	} */
	
	// Obsoleted
	private function genreatePDF_obsoleted() {
		// create new PDF document
		$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		
		// set document information
		$pdf->SetCreator('S3');
		$pdf->SetAuthor('S3');
		$pdf->SetTitle('Delivery Note');
		$pdf->SetSubject('Delivery Note');
		
		// set default header data
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		
		// set header and footer fonts
		//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		
		// set margins
		/* $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		 $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER); */
		
		// set font
		//$pdf->SetFont('helvetica', '', 10);
		//$pdf->SetFont('msungstdlight', '', 10);
		$pdf->SetFont('cid0jp', '', 10);
		
		$pdf->AddPage();
		
		
		$html = '<h1>納品書</h1>';
		$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
		
		/** Address */
		$html =  '<span style="font-weight:bold;font-size:18pt">'.$this->customer->name.' 御中</span><br>'.$this->customer->address1.'<br>'.$this->customer->address2.'<br>'.$this->customer->address3;
		
		//$pdf->MultiCell(100, 50, $html, 1, 'L', false, 0, '', '', true, 0, true);
		$pdf->writeHTMLCell(100, 50, '', '', $html, 1, 0, 0, true, '', true);
		
		/** Date */
		$pdf->SetAbsX($pdf->GetAbsX() + 100);
		$html = '売上日'.date('Y/m/d');
		$pdf->writeHTMLCell(40, 10, '', '', $html, 1, 0, 0, true, '', true);
		
		/** Delivery note no. */
		$pdf->SetAbsX($pdf->GetAbsX() + 2);
		$html = 'NO '.$this->deliveryNote->delivery_note_no;
		$pdf->writeHTMLCell(38, 10, '', '', $html, 1, 1, 0, true, '', true);
		
		/** Office address */
		$officeAddress = new Model_OfficeAddress($this->deliveryNote->office_address_id);
		$pdf->SetAbsX(210);
		$pdf->SetAbsY(30);
		$pdf->writeHTMLCell(80, 30, '', '', $officeAddress->name.'<br>'.$officeAddress->address, 1, 1, 0, true, '', true);
		
		$view = View::factory('accountant/delivery_note_pdf');
		$view->set('form', $this);
		$pdf->SetY(80);
		$pdf->writeHTMLCell(0, 0, '', '', $view, 0, 1, 0, true, '', true);
		
		$deliveryNoteCreateDate = date('Y-m-d', strtotime($this->deliveryNote->create_date));
		$pdf->Output($deliveryNoteCreateDate.'_'.$this->deliveryNote->id.'.pdf', 'D');
	}
	
	private function genreatePDF() {
		// create new PDF document
		$pdf = new Model_Accountant_DeliveryNotePDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->customer = $this->customer;
		$pdf->deliveryNote = $this->deliveryNote;
		
		// set document information
		$pdf->SetCreator('S3');
		$pdf->SetAuthor('S3');
		$pdf->SetTitle('Delivery Note');
		$pdf->SetSubject('Delivery Note');
	
		// set default header data
		$pdf->setPrintHeader(true);
		$pdf->setPrintFooter(true);
	
		// set header and footer fonts
		$pdf->setHeaderFont(Array('cid0jp', '', PDF_FONT_SIZE_MAIN));
		//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	
		// set margins
		/* $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		 $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER); */
		$pdf->SetMargins(PDF_MARGIN_LEFT, 80, PDF_MARGIN_RIGHT);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		
		// set font
		//$pdf->SetFont('helvetica', '', 10);
		//$pdf->SetFont('msungstdlight', '', 10);
		$pdf->SetFont('cid0jp', '', 10);
	
		$pdf->AddPage();
		
		$view = View::factory('accountant/delivery_note_pdf');
		$view->set('form', $this);
		$pdf->SetY(80);
		$pdf->writeHTMLCell(0, 0, '', '', $view, 0, 1, 0, true, '', true);
	
		$deliveryNoteCreateDate = date('Y-m-d', strtotime($this->deliveryNote->create_date));
		$pdf->Output($deliveryNoteCreateDate.'_'.$this->deliveryNote->id.'.pdf', 'D');
		// Uncomment when testing
		/* echo $view;
		echo View::factory('profiler/stats'); */
	}
}