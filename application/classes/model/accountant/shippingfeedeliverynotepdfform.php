<?php
require_once APPPATH.'classes/vendor/tcpdf/tcpdf.php';

class Model_Accountant_ShippingFeeDeliveryNotePDFForm {
	public $shipping_fee_delivery_note_id;
	public $customer;
	public $office_address;
	public $bank;
	public $shippingFees;
	
	public $total;
	
	public $errors;
	
	public function __construct($shipping_fee_delivery_note_id) {
		$this->shipping_fee_delivery_note_id = $shipping_fee_delivery_note_id;
	}
	
	public function processPrintAction() {
		return $this->printShippingFeeDeliveryNote();
	}
	
	private function printShippingFeeDeliveryNote() {
		$this->shippingFeeDeliveryNote = ORM::factory('shippingFeeDeliveryNote')
										->where('id', '=', $this->shipping_fee_delivery_note_id)
										->find();
	
		$this->customer = $this->shippingFeeDeliveryNote->customer;
		
		$this->office_address = new Model_OfficeAddress($this->customer->office_address_id);
		
		$this->bank = new Model_BankAccount($this->customer->bank_account_id);
		
		$this->shippingFees = ORM::factory('shippingFee')
						->where('shipping_fee_delivery_note_id', '=', $this->shipping_fee_delivery_note_id)
						->find_all();
		
		$this->total = 0;
		foreach ($this->shippingFees as $shippingFee) {
			$this->total += $shippingFee->amount;
		}
		
		// Update last print date
		$this->errors = array();
		
		$db = Database::instance();
		$db->begin();
		
		try {
			$this->shippingFeeDeliveryNote->last_print_date = DB::expr('current_timestamp');
			$this->shippingFeeDeliveryNote->save();
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
		
		$html = '<div style="width:100%;text-align:left;background-color:rgb(188, 208, 241);color:black;font-size:20pt">御請求書</div>';
		$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
		
		/** Date */
		$html = date('Y年m月d日', strtotime($this->shippingFeeDeliveryNote->create_date)).'発行';
		$pdf->writeHTMLCell(100, 10, 250, 15, $html, 0, 1, 0, true, '', true);
		
		/** Customer Name */
		//$html = '<div style="font-size:15px">'.$this->customer->name.' 様</div><br /><div style="font-size:8px">大変お世話になっております。</div>';
		$html = '<div style="font-size:15px">'.$this->customer->name.' 様</div>';
		$pdf->writeHTMLCell(180, 10, 10, 20, $html, 0, 1, 0, true, '', true);
		$html = '<div style="font-size:8px">大変お世話になっております。</div>';
		$pdf->writeHTMLCell(100, 10, 10, 27, $html, 0, 1, 0, true, '', true);
		
		/** Total */
		$html = '税込請求金額 '.GlobalFunction::displayJPYNumber($this->total).'円';
		$pdf->writeHTMLCell(100, 10, 10, 35, $html, 0, 1, 0, true, '', true);
		
		/** Office Address */
		$html = '<div style="font-size:8px">'.$this->office_address->name.'<br>'.$this->office_address->address.'</div>';
		$pdf->writeHTMLCell(100, 10, 200, 20, $html, 0, 1, 0, true, '', true);
		
		/** Bank */
		$html = '<div style="font-size:8px">振込銀行&nbsp;'.$this->bank->bank_name.'<br>支店名&nbsp;'.$this->bank->branch.'<br>振込先口座&nbsp;'.$this->bank->account_no.'<br>振込先名義&nbsp;'.$this->bank->owner.'</div>';
		$pdf->writeHTMLCell(100, 10, 200, 30, $html, 0, 1, 0, true, '', true);
		
		$view = View::factory('accountant/shipping_fee_delivery_note_pdf');
		$view->set('form', $this);
		$pdf->SetY(50);
		$pdf->writeHTMLCell(0, 0, '', '', $view, 0, 1, 0, true, '', true);
		
		$deliveryNoteCreateDate = date('Y-m-d', strtotime($this->shippingFeeDeliveryNote->create_date));
		$pdf->Output($deliveryNoteCreateDate.'_'.$this->shippingFeeDeliveryNote->id.'.pdf', 'D');
	}
}