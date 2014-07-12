<?php
require_once APPPATH.'classes/vendor/tcpdf/tcpdf.php';

class Model_Accountant_OrderReturnInvoicePDFForm {
	public $invoice_id;
	public $customer;
	public $deliveryNote;
	public $products;
	
	public $errors;
	
	public function __construct($invoice_id) {
		$this->invoice_id = $invoice_id;
	}
	
	public function processPrintAction() {
		return $this->printDeliveryNote();
	}
	
	private function printDeliveryNote() {
		$this->invoice = ORM::factory('orderReturnInvoice')
						->where('id', '=', $this->invoice_id)
						->find();
	
		// Get customer
		$this->customer = $this->invoice->customer;

		// Process product item
		$invoiceDetails = ORM::factory('orderReturnInvoiceDetail')
						->where('order_return_invoice_id', '=', $this->invoice_id)
						->order_by('id')
						->find_all();
	
		$this->products = array();
		foreach ($invoiceDetails as $invoiceDetail) {
			$deliveryNoteDetail = ORM::factory('deliveryNoteDetail')
								->join('delivery_note')->on('delivery_note.id', '=', 'deliverynotedetail.delivery_note_id')
								->where('deliverynotedetail.id', '=', $invoiceDetail->delivery_note_detail_id)
								->select('delivery_note.delivery_note_no')
								->select(array('delivery_note.create_date', 'delivery_note_create_date'))
								->find();
			
			$product = new Model_Accountant_InvoiceProduct();
			
			$productMaster = ORM::factory('productMaster')->where('no_jp', '=', $invoiceDetail->product_cd)->find();
			if ($productMaster->loaded()) {
				$product->description = $invoiceDetail->product_cd.'<br>'.$productMaster->product_desc;
			} else {
				$product->description = $invoiceDetail->product_cd;
			}
			
			$product->qty = $invoiceDetail->qty;
			$product->market_price_rmb = $invoiceDetail->market_price_rmb;
			$product->market_price = $invoiceDetail->market_price;
			$product->delivery_note_create_date = $deliveryNoteDetail->delivery_note_create_date;
			$product->delivery_note_no = $deliveryNoteDetail->delivery_note_no;
			$product->total = $invoiceDetail->total;
			$product->total_rmb = $invoiceDetail->market_price_rmb * $invoiceDetail->qty;
			$product->remark = $invoiceDetail->remark;
			
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
	}
	
	private function genreatePDF() {
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
		$html = $this->customer->name.'<br>'.$this->customer->address1.'<br>'.$this->customer->address2.'<br>'.$this->customer->address3;
		//$pdf->MultiCell(100, 50, $html, 1, 'L', false, 0, '', '', true, 0, true);
		$pdf->writeHTMLCell(100, 40, '', '', $html, 1, 0, 0, true, '', true);
		
		// Date
		$pdf->SetAbsX($pdf->GetAbsX() + 100);
		$html = '請求日'.date('Y/m/d');
		$pdf->writeHTMLCell(40, 10, '', '', $html, 1, 0, 0, true, '', true);
		
		// Invoice no.
		/* $pdf->SetAbsX($pdf->GetAbsX() + 2);
		$html = 'NO '.$this->invoice->invoice_no;
		$pdf->writeHTMLCell(38, 10, '', '', $html, 1, 1, 0, true, '', true); */
		
		/** Office address */
		$officeAddress = new Model_OfficeAddress($this->invoice->office_address_id);
		if ($officeAddress->loaded()) {
			$pdf->SetAbsX(210);
			$pdf->SetAbsY(30);
			$pdf->writeHTMLCell(80, 20, '', '', $officeAddress->name.'<br>'.$officeAddress->address, 1, 1, 0, true, '', true);
		}
		
		// Due date
		//$pdf->SetAbsX(10);
		//$pdf->SetAbsY(60);
		$due_date = strtotime($this->invoice->due_date);
		$year = date('Y', $due_date);
		$month = date('m', $due_date);
		$day = date('d', $due_date);
		$html = $year.'/'.$month.'/'.$day.'までにお振込み願います';
		//$html = 'お支找い期日<br />'.date('Y/m/d', strtotime($this->invoice->due_date));
		$pdf->writeHTMLCell(40, 10, 10, 60, $html, 1, 0, 0, true, '', true);
		
		// Bank Name
		$pdf->SetAbsX($pdf->GetAbsX() + 10);
		/* $html = $this->bank->bank_name.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
				.$this->bank->branch.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
				.$this->bank->get_txn_type_description().'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
				.$this->bank->account_no.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
				.$this->bank->owner; */
		$html = str_replace(' ', '&nbsp;', $this->customer->bank_account);
		$pdf->writeHTMLCell(230, 10, '', '', $html, 1, 0, 0, true, '', true);
		
		$view = View::factory('accountant/order_return_invoice_pdf');
		$view->set('form', $this);
		$pdf->SetY(80);
		$pdf->writeHTMLCell(0, 0, '', '', $view, 0, 1, 0, true, '', true);
		
		$invoiceNoteCreateDate = date('Y-m-d', strtotime($this->invoice->create_date));
		$pdf->Output($invoiceNoteCreateDate.'_'.$this->invoice->id.'.pdf', 'D');
	}
}