<?php
require_once APPPATH.'classes/vendor/tcpdf/tcpdf.php';
require_once APPPATH.'classes/vendor/PHPExcel.php';

class Model_Accountant_InvoicePDFForm {
	public $invoice_id;
	public $invoice;
	public $customer;
	public $deliveryNote;
	public $products;
	//public $bank;
	
	public $errors;
	
	public function __construct($invoice_id) {
		$this->invoice_id = $invoice_id;
	}
	
	public function processPrintAction() {
		if (!$this->printDeliveryNote('<br />')) {
			return false;
		}
		
		// Generate pdf
		$this->genreatePDF2();
		
		return true;
	}
	
	public function processPrintExcelAction() {
		if (!$this->printDeliveryNote(chr(13))) {
			return false;
		}
		
		// Generate pdf
		$this->genreateExcel();
		
		return true;
	}
	
	private function printDeliveryNote($CARRIAGE_RETURN) {
		$this->invoice = ORM::factory('invoice')
						->where('id', '=', $this->invoice_id)
						->find();
	
		// Get customer
		$this->customer = $this->invoice->customer;

		// Process product item
		$invoiceDetails = ORM::factory('invoiceDetail')
						->where('invoice_id', '=', $this->invoice_id)
						->where('source', '<>', Model_InvoiceDetail::SOURCE_TAX) // Exclude tax item
						->order_by('id')
						->find_all();
	
		$this->products = array();
		foreach ($invoiceDetails as $invoiceDetail) {
			$deliveryNote = ORM::factory('deliveryNote')
							->join('delivery_note_detail')->on('delivery_note_detail.delivery_note_id', '=', 'deliverynote.id')
							->where('delivery_note_detail.id', '=', $invoiceDetail->delivery_note_detail_id)
							->find();
			
			$product = new Model_Accountant_InvoiceProduct();
			$product->delivery_note_no = $deliveryNote->delivery_note_no;
			$product->delivery_note_create_date = $deliveryNote->create_date;
			
			if ($invoiceDetail->source == Model_InvoiceDetail::SOURCE_CONTAINER) {
				$container = ORM::factory('container')
							->join('delivery_note_detail')->on('container.id', '=', 'delivery_note_detail.container_id')
							->where('delivery_note_detail.id', '=', $invoiceDetail->delivery_note_detail_id)
							->select('delivery_note_detail.remark')
							->find();
				
				if ($container->source != Model_Container::SOURCE_GIFT) {
					$orderProduct = ORM::factory('orderProduct')
									->with('productMaster')
									//->join('product_master', 'LEFT')->on('product_master.no_jp', '=', 'orderproduct.product_cd')
									->where('orderproduct.id', '=', $container->order_product_id)
									//->select('product_desc')
									->find();
					
					$product->description = $orderProduct->product_cd.($orderProduct->is_tax == Model_OrderProduct::TAX_NOT_INCLUDE ? '＊' : '').$CARRIAGE_RETURN.$orderProduct->productMaster->product_desc;
					$product->qty = $invoiceDetail->qty;
					$product->market_price_rmb = $invoiceDetail->market_price_rmb;
					$product->market_price = $invoiceDetail->market_price;
					$product->total = $invoiceDetail->total;
					$product->total_rmb = $invoiceDetail->market_price_rmb * $invoiceDetail->qty;
					
					$product->remark = 'Order No. '.$orderProduct->order_id;
					if ($container->container_no != '') {
						$product->remark .= $CARRIAGE_RETURN.'櫃號: '.$container->container_no;
					}
					$product->remark .= $CARRIAGE_RETURN.$container->remark;
				}/*  else {
					// Gift
					$gift = new Model_Gift($container->gift_id);
					
					$product->description = $gift->product_cd.'<br>'.$gift->product_desc;
					$product->qty = $invoiceDetail->qty;
					$product->market_price_rmb = $invoiceDetail->market_price_rmb;
					$product->market_price = $invoiceDetail->market_price;
					$product->total = $invoiceDetail->total;
					$product->total_rmb = $invoiceDetail->market_price_rmb * $invoiceDetail->qty;
					
					if ($container->container_no != '') {
						$product->remark .= '<br />櫃號: '.$container->container_no;
					}
				} */

			} else if ($invoiceDetail->source == Model_InvoiceDetail::SOURCE_DELIVERY_FEE) {
				$product->description = $invoiceDetail->description;
				$product->qty = '';
				$product->market_price = '';
				$product->market_price_rmb = '';
				$product->total = $invoiceDetail->market_price;
				//$product->total_rmb = GlobalFunction::roundUpTo($invoiceDetail->total * 1.0 / $this->invoice->rmb_to_jpy_rate, 2);
				$product->total_rmb = $invoiceDetail->market_price_rmb;
			} else if ($invoiceDetail->source == Model_InvoiceDetail::SOURCE_ORDER_RETURN) {
				// Order return
				$deliveryNoteDetail = new Model_DeliveryNoteDetail($invoiceDetail->delivery_note_detail_id);
				
				$productMaster = ORM::factory('pmProductMaster')
								->where('no_jp', '=', $invoiceDetail->product_cd)
								->find();

				if ($productMaster->loaded()) {
					$product->description = $deliveryNoteDetail->product_cd.$CARRIAGE_RETURN.$productMaster->product_desc;
				} else {
					$product->description = $deliveryNoteDetail->product_cd;
				}
				
				$product->qty = $deliveryNoteDetail->qty;
				$product->qty = $invoiceDetail->qty;
				$product->market_price = $invoiceDetail->market_price;
				$product->market_price_rmb = $invoiceDetail->market_price_rmb;
				$product->total = $invoiceDetail->total;
				$product->total_rmb = GlobalFunction::roundUpTo($invoiceDetail->total * 1.0 / $this->invoice->rmb_to_jpy_rate, 2);
				$product->remark = $deliveryNoteDetail->remark;
			} else {
				$product->description = $invoiceDetail->description;
				$product->qty = '';
				$product->market_price = '';
				$product->market_price_rmb = '';
				$product->total = $invoiceDetail->market_price;
				$product->remark = '';
				
				if ($invoiceDetail->market_price_rmb != 0) {
					$product->total_rmb = $invoiceDetail->market_price_rmb;
				} else {
					$product->total_rmb = GlobalFunction::roundUpTo($invoiceDetail->market_price * 1.0 / $this->invoice->rmb_to_jpy_rate, 2);
				}
			}
			
			$this->products[] = $product;
		}

		// Update last print date
		$this->errors = array();
	
		$db = Database::instance();
		$db->begin();
	
		try {
			$this->invoice->last_print_date = DB::expr('current_timestamp');
			$this->invoice->save();
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
	
		$db->commit();

		return true;
	}
	
	/* private function printDeliveryNote() {
		$this->invoice = ORM::factory('invoice')
								->where('id', '=', $this->invoice_id)
								->find();
	
		// Get customer
		$this->customer = $this->invoice->customer;
		
		// Get bank
		//$this->bank = ORM::factory('bankAccount')->where('id', '=', $this->invoice->bank_id)->find();
		
		// Process product item
		$invoiceDetails = ORM::factory('invoiceDetail')
					->join('delivery_note_detail')->on('delivery_note_detail.id', '=', 'invoicedetail.delivery_note_detail_id')
					->join('delivery_note')->on('delivery_note.id', '=', 'delivery_note_detail.delivery_note_id')
					->join('product_master', 'LEFT')->on('product_master.no_jp', '=', 'invoicedetail.product_cd')
					->join('container')->on('container.id', '=', 'delivery_note_detail.container_id')
					->join('order_product')->on('order_product.id', '=', 'container.order_product_id')
					->where('invoicedetail.invoice_id', '=', $this->invoice_id)
					->select('product_desc')
					->select(array('delivery_note.create_date', 'delivery_note_create_date'))
					->select('delivery_note.delivery_note_no')
					->select('order_product.order_id')
					->select('container.container_no')
					->find_all();
		
		$this->products = array();
		foreach ($invoiceDetails as $invoiceDetail) {
			$product = new Model_Accountant_InvoiceProduct();
			$product->description = $invoiceDetail->product_cd.'<br>'.$invoiceDetail->product_desc;
			$product->qty = $invoiceDetail->qty;
			$product->market_price_rmb = $invoiceDetail->market_price_rmb;
			$product->market_price = $invoiceDetail->market_price;
			$product->delivery_note_create_date = $invoiceDetail->delivery_note_create_date;
			$product->delivery_note_no = $invoiceDetail->delivery_note_no;
			$product->total = $invoiceDetail->total;
			$product->total_rmb = $invoiceDetail->market_price_rmb * $invoiceDetail->qty;
			
			$product->remark = 'Order No. '.$invoiceDetail->order_id;
			if ($invoiceDetail->container_no != '') {
				$product->remark .= '<br />櫃號: '.$invoiceDetail->container_no;
			}
			
			$this->products[] = $product;
		}
		
		// Gift
		$invoiceDetails = ORM::factory('invoiceDetail')
						->join('delivery_note_detail')->on('delivery_note_detail.id', '=', 'invoicedetail.delivery_note_detail_id')
						->join('delivery_note')->on('delivery_note.id', '=', 'delivery_note_detail.delivery_note_id')
						->join('container')->on('container.id', '=', 'delivery_note_detail.container_id')
						->join('gift')->on('gift.id', '=', 'container.gift_id')
						->where('invoicedetail.invoice_id', '=', $this->invoice_id)
						->select('gift.product_desc')
						->select(array('delivery_note.create_date', 'delivery_note_create_date'))
						->select('delivery_note.delivery_note_no')
						->select('container.container_no')
						->find_all();
		foreach ($invoiceDetails as $invoiceDetail) {
			$product = new Model_Accountant_InvoiceProduct();
			$product->description = $invoiceDetail->product_cd.'<br>'.$invoiceDetail->product_desc;
			$product->qty = $invoiceDetail->qty;
			$product->market_price_rmb = $invoiceDetail->market_price_rmb;
			$product->market_price = $invoiceDetail->market_price;
			$product->delivery_note_create_date = $invoiceDetail->delivery_note_create_date;
			$product->delivery_note_no = $invoiceDetail->delivery_note_no;
			$product->total = $invoiceDetail->total;
			$product->total_rmb = $invoiceDetail->market_price_rmb * $invoiceDetail->qty;
			
			if ($invoiceDetail->container_no != '') {
				$product->remark .= '<br />櫃號: '.$invoiceDetail->container_no;
			}
				
			$this->products[] = $product;
		}
		
		// Process extra item
		$invoiceExtraDetails = ORM::factory('invoiceExtraDetail')
						->join('delivery_note_extra_detail')->on('delivery_note_extra_detail.id', '=', 'invoiceextradetail.delivery_note_extra_detail_id')
						->join('delivery_note')->on('delivery_note.id', '=', 'delivery_note_extra_detail.delivery_note_id')
						->where('invoiceextradetail.invoice_id', '=', $this->invoice_id)
						->select(array('delivery_note.create_date', 'delivery_note_create_date'))
						->select('delivery_note.delivery_note_no')
						->find_all();
		
		foreach ($invoiceExtraDetails as $invoiceExtraDetail) {
			$product = new Model_Accountant_InvoiceProduct();
			$product->description = $invoiceExtraDetail->description;
			$product->qty = '';
			$product->market_price = '';
			$product->market_price_rmb = '';
			$product->delivery_note_create_date = $invoiceExtraDetail->delivery_note_create_date;
			$product->delivery_note_no = $invoiceExtraDetail->delivery_note_no;
			
			if ($invoiceExtraDetail->currency == Model_Rate::RATE_TO_JPY) {
				// JPY
				$product->total = $invoiceExtraDetail->total;
				$product->total_rmb = GlobalFunction::roundUpTo($invoiceExtraDetail->total * 1.0 / $this->invoice->rmb_to_jpy_rate, 2);
			} else {
				// RMB
				$product->total = $invoiceExtraDetail->total * $this->invoice->rmb_to_jpy_rate;
				$product->total_rmb = $invoiceExtraDetail->total;
			}
			
			$this->products[] = $product;
		}
		
		// Update last print date
		$this->errors = array();
		
		$db = Database::instance();
		$db->begin();
		
		try {
			$this->invoice->last_print_date = DB::expr('current_timestamp');
			$this->invoice->save();
		} catch (Exception $e) {
			$db->rollback();
			$this->errors[] = $e->getMessage();
			return false;
		}
		
		$db->commit();
		
		// Generate pdf
		$this->genreatePDF();
		
		return true;
	} */
	
	/* private function genreatePDF() {
		// create new PDF document
		$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		
		// set document information
		$pdf->SetCreator('S3');
		$pdf->SetAuthor('S3');
		$pdf->SetTitle('Invoice');
		$pdf->SetSubject('Invoice');
		
		// set default header data
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		
		// set font
		$pdf->SetFont('cid0jp', '', 10);
		
		$pdf->AddPage();
		
		$html = '<h1>請求書</h1>';
		$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
		
		// Address
		$html = '<span style="font-weight:bold;font-size:18pt">'.$this->customer->name.' 御中</span><br>'.$this->customer->address1.'<br>'.$this->customer->address2.'<br>'.$this->customer->address3;
		//$pdf->MultiCell(100, 50, $html, 1, 'L', false, 0, '', '', true, 0, true);
		$pdf->writeHTMLCell(100, 40, '', '', $html, 1, 0, 0, true, '', true);
		
		// Date
		$pdf->SetAbsX($pdf->GetAbsX() + 100);
		$html = '請求日'.date('Y/m/d');
		$pdf->writeHTMLCell(40, 10, '', '', $html, 1, 0, 0, true, '', true);
		
		// Invoice no.
		$pdf->SetAbsX($pdf->GetAbsX() + 2);
		$html = 'NO '.$this->invoice->invoice_no;
		$pdf->writeHTMLCell(38, 10, '', '', $html, 1, 1, 0, true, '', true);
		
		// Office address
		$officeAddress = new Model_OfficeAddress($this->invoice->office_address_id);
		if ($officeAddress->loaded()) {
			$pdf->SetAbsX(210);
			$pdf->SetAbsY(30);
			$pdf->writeHTMLCell(80, 20, '', '', $officeAddress->name.'<br>'.$officeAddress->address.'<br>'.$officeAddress->tel, 1, 1, 0, true, '', true);
		}
		
		// Due date
		$pdf->SetAbsX(10);
		$pdf->SetAbsY(60);
		$due_date = strtotime($this->invoice->due_date);
		$year = date('Y', $due_date);
		$month = date('m', $due_date);
		$day = date('d', $due_date);
		$html = $year.'/'.$month.'/'.$day.'までにお振込み願います';
		//$html = 'お支找い期日<br />'.date('Y/m/d', strtotime($this->invoice->due_date));
		$pdf->writeHTMLCell(40, 10, '', '', $html, 1, 0, 0, true, '', true);
		
		// Bank Name
		$pdf->SetAbsX($pdf->GetAbsX() + 10);
		//$html = $this->bank->bank_name.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
		//		.$this->bank->branch.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
		//		.$this->bank->get_txn_type_description().'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
		//		.$this->bank->account_no.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
		//		.$this->bank->owner;
		$html = str_replace(' ', '&nbsp;', $this->customer->bank_account);
		$pdf->writeHTMLCell(230, 10, '', '', $html, 1, 0, 0, true, '', true);
		
		$view = View::factory('accountant/invoice_pdf');
		$view->set('form', $this);
		$pdf->SetY(80);
		$pdf->writeHTMLCell(0, 0, '', '', $view, 0, 1, 0, true, '', true);
		
		$invoiceNoteCreateDate = date('Y-m-d', strtotime($this->invoice->create_date));
		$pdf->Output($invoiceNoteCreateDate.'_'.$this->invoice->id.'.pdf', 'D');
	} */
	
	private function genreatePDF2() {
		// create new PDF document
		$pdf = new Model_Accountant_InvoicePDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->customer = $this->customer;
		$pdf->invoice = $this->invoice;
	
		// set document information
		$pdf->SetCreator('S3');
		$pdf->SetAuthor('S3');
		$pdf->SetTitle('Invoice');
		$pdf->SetSubject('Invoice');
	
		// set default header data
		$pdf->setPrintHeader(true);
		$pdf->setPrintFooter(true);
		
		// set header and footer fonts
		$pdf->setHeaderFont(Array('cid0jp', '', PDF_FONT_SIZE_MAIN));
	
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		
		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, 80, PDF_MARGIN_RIGHT);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	
		// set font
		$pdf->SetFont('cid0jp', '', 10);
	
		$pdf->AddPage();

		$view = View::factory('accountant/invoice_pdf');
		$view->set('form', $this);
		$pdf->SetY(80);
		$pdf->writeHTMLCell(0, 0, '', '', $view, 0, 1, 0, true, '', true);
	
		$invoiceNoteCreateDate = date('Y-m-d', strtotime($this->invoice->create_date));
		$pdf->Output($invoiceNoteCreateDate.'_'.$this->invoice->id.'.pdf', 'D');
	}
	
	private function genreateExcel() {
		$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
		$cacheSettings = array('memoryCacheSize' => '8MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
		
		$objPHPExcel = new PHPExcel();
		
		$objPHPExcel->getProperties()->setCreator("S3")
					->setLastModifiedBy("S3")
					->setTitle("invoice");
		
		$sheet = $objPHPExcel->setActiveSheetIndex(0);
		
		// Border
		$styleArray = array(
				'borders' => array(
						'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN
						)
				),
		);
		
		$rowNo = 1;
		
		// Header
		$sheet->mergeCells('A1:F1');
		$sheet->setCellValueByColumnAndRow(0, $rowNo, '請求書');
		$sheet->getStyle('A1')->applyFromArray(
					array(
							'font' => array(
								'bold' => true
							),
							'alignment' => array(
								'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
							)
						)
				);
		
		// Address
		$rowNo = 2;
		if (!empty($this->customer->postal_code)) {
			$sheet->setCellValueByColumnAndRow(0, $rowNo++, $this->customer->postal_code);
		}
		if (!empty($this->customer->address1)) {
			$sheet->setCellValueByColumnAndRow(0, $rowNo++, $this->customer->address1);
		}
		if (!empty($this->customer->address2)) {
			$sheet->setCellValueByColumnAndRow(0, $rowNo++, $this->customer->address2);
		}
		if (!empty($this->customer->address3)) {
			$sheet->setCellValueByColumnAndRow(0, $rowNo++, $this->customer->address3);
		}
		
		$sheet->setCellValueByColumnAndRow(0, $rowNo++, $this->customer->name.' 御中');
		$sheet->setCellValueByColumnAndRow(0, $rowNo++, 'TEL '.$this->customer->tel);
		
		// Date
		$rowNo = 2;
		$sheet->setCellValueByColumnAndRow(4, $rowNo++, '請求日'.date('Y/m/d'));
		$sheet->setCellValueByColumnAndRow(4, $rowNo++, '請求期間'.date('m月d日', strtotime($this->invoice->bill_date_from)).' ~ '.date('m月d日', strtotime($this->invoice->bill_date_to)));
		
		// Invoice no.
		$rowNo = 2;
		$sheet->setCellValueByColumnAndRow(5, $rowNo++, 'NO '.$this->invoice->invoice_no);
		
		// Office address
		$rowNo = 4;
		$officeAddress = new Model_OfficeAddress($this->invoice->office_address_id);
		if ($officeAddress->loaded()) {
			$sheet->setCellValueByColumnAndRow(4, $rowNo++, $officeAddress->address);
			$sheet->setCellValueByColumnAndRow(4, $rowNo++, $officeAddress->name);
			$sheet->setCellValueByColumnAndRow(4, $rowNo++, $officeAddress->tel);
		}
		
		// Rate
		$rowNo = 8;
		$sheet->setCellValueByColumnAndRow(4, $rowNo++, 'レートの有効期間：当月末迄');
		$sheet->setCellValueByColumnAndRow(4, $rowNo++, '1元 = '.$this->invoice->rmb_to_jpy_rate.'円');
		$sheet->setCellValueByColumnAndRow(4, $rowNo++, '1元 = '.$this->invoice->rmb_to_usd_rate.'US$');
		
		// Due date
		$rowNo = 10;
		$due_date = strtotime($this->invoice->due_date);
		$year = date('Y', $due_date);
		$month = date('m', $due_date);
		$day = date('d', $due_date);
		$sheet->setCellValueByColumnAndRow(0, $rowNo, $year.'/'.$month.'/'.$day.'までにお振込み願います');
		
		// Bank Name
		$sheet->setCellValueByColumnAndRow(1, $rowNo, $this->customer->bank_account);
		
		// Invoice summary
		$rowNo = 12;
		
		$sheet->getStyle('A'.$rowNo.':F'.($rowNo + 1))->applyFromArray($styleArray);
		$sheet->getStyle('A'.$rowNo.':F'.$rowNo)->applyFromArray(
					array(
							'font' => array(
								'bold' => true
							)
					)
		);
		
		$sheet->setCellValueByColumnAndRow(0, $rowNo, '前回請求額');
		$sheet->setCellValueByColumnAndRow(1, $rowNo, '御入金額');
		$sheet->setCellValueByColumnAndRow(2, $rowNo, '繰越金額');
		$sheet->setCellValueByColumnAndRow(3, $rowNo, '今回御買上額');
		$sheet->setCellValueByColumnAndRow(4, $rowNo, '源泉徵收税額');
		$sheet->setCellValueByColumnAndRow(5, $rowNo, '今回請求金額');
		
		$rowNo++;
		$sheet->setCellValueByColumnAndRow(0, $rowNo, '￥'.GlobalFunction::displayJPYNumber($this->invoice->last_month_amt));
		$sheet->setCellValueByColumnAndRow(1, $rowNo, '￥'.GlobalFunction::displayJPYNumber($this->invoice->last_month_settle));
		$sheet->setCellValueByColumnAndRow(2, $rowNo, '￥'.GlobalFunction::displayJPYNumber($this->invoice->last_month_amt - $this->invoice->last_month_settle));
		$sheet->setCellValueByColumnAndRow(3, $rowNo, '￥'.GlobalFunction::displayJPYNumber($this->invoice->current_month_amt + $this->invoice->total_tax));
		$sheet->setCellValueByColumnAndRow(4, $rowNo, '');
		$sheet->setCellValueByColumnAndRow(5, $rowNo, '￥'.GlobalFunction::displayJPYNumber($this->invoice->total_amt));
		
		// Invoice detail
		$rowNo++;
		$rowNo++;
		
		$sheet->getStyle('A'.$rowNo.':F'.($rowNo + sizeOf($this->products)))->applyFromArray($styleArray);
		$sheet->getStyle('A'.$rowNo.':F'.$rowNo)->applyFromArray(
				array(
						'font' => array(
								'bold' => true
						)
				)
		);
		
		$sheet->setCellValueByColumnAndRow(0, $rowNo, '日付/伝票番号');
		$sheet->setCellValueByColumnAndRow(1, $rowNo, '品番/詳細');
		$sheet->setCellValueByColumnAndRow(2, $rowNo, '數量');
		$sheet->setCellValueByColumnAndRow(3, $rowNo, '単価  (RMB / ￥)');
		$sheet->setCellValueByColumnAndRow(4, $rowNo, '金額  (RMB / ￥)');
		$sheet->setCellValueByColumnAndRow(5, $rowNo, '備考');
		
		foreach ($this->products as $product) {
			$rowNo++;
			
			if ($product->delivery_note_no != NULL) {
				$sheet->setCellValueByColumnAndRow(0, $rowNo, date('Y/m/d', strtotime($product->delivery_note_create_date)).chr(13).$product->delivery_note_no);
			}
			
			$sheet->setCellValueByColumnAndRow(1, $rowNo, $product->description);
			$sheet->setCellValueByColumnAndRow(2, $rowNo, $product->qty);
			
			if ($product->market_price != '') {
				$sheet->setCellValueByColumnAndRow(3, $rowNo, GlobalFunction::displayNumber($product->market_price_rmb).'元 / '.GlobalFunction::displayJPYNumber($product->market_price).'円');
			}
			
			$sheet->setCellValueByColumnAndRow(4, $rowNo, GlobalFunction::displayNumber($product->total_rmb).'元 / '.GlobalFunction::displayJPYNumber($product->total).'円');
			$sheet->setCellValueByColumnAndRow(5, $rowNo, $product->remark);
		}
		
		header("Content-type:application/vnd.ms-excel");
		header('Content-Disposition: attachment;filename="invoice_'.$this->invoice_id.'.xls"');
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
}