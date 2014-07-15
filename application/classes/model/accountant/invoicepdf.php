<?php
require_once APPPATH.'classes/vendor/tcpdf/tcpdf.php';

class Model_Accountant_InvoicePDF extends TCPDF {
	
	public $customer;
	public $invoice;
	
	// Page header
	public function Header() {
		// Address
		$html = '';
		
		if (!empty($this->customer->postal_code)) {
			$html .= $this->customer->postal_code.'';
		}
		if (!empty($this->customer->address1)) {
			$html = empty($html) ? ' '.$this->customer->address1 : $html.'<br> '.$this->customer->address1;
		}
		if (!empty($this->customer->address2)) {
			$html = empty($html) ? ' '.$this->customer->address2 : $html.'<br> '.$this->customer->address2;
		}
		if (!empty($this->customer->address3)) {
			$html = empty($html) ? ' '.$this->customer->address3 : $html.'<br> '.$this->customer->address3;
		}
		
		if (!empty($html)) {
			$html = '<span style="font-size:12pt"> '.$html.'</span><br><br>';
		}
		
		$html .= '<span style="font-size:12pt"> '.$this->customer->name.' 御中</span><br><br>';
		
		$html .= '<span style="font-size:12pt"> TEL '.$this->customer->tel.' </span><br>';
		
		$this->writeHTMLCell(90, 40, 16, 12, $html, 1, 0, 0, true, '', true);
		
		$html = '<h1>請求書</h1>';
		$this->writeHTMLCell(0, 0, 140, 12, $html, 0, 1, 0, true, '', true);
		
		// Date
		//$html = '請求日'.date('Y/m/d');
		//$this->writeHTMLCell(40, 10, 210, 17, $html, 1, 0, 0, true, '', true);
		$html = '請求日'.date('Y/m/d').'<br />請求期間'.date('m月d日', strtotime($this->invoice->bill_date_from)).' ~ '.date('m月d日', strtotime($this->invoice->bill_date_to));
		$this->writeHTMLCell(50, 10, 200, 17, $html, 1, 0, 0, true, '', true);
		
		// Invoice no.
		$html = 'NO '.$this->invoice->invoice_no;
		$this->writeHTMLCell(38, 10, 252, 17, $html, 1, 1, 0, true, '', true);
		
		/** Office address */
		$officeAddress = new Model_OfficeAddress($this->invoice->office_address_id);
		if ($officeAddress->loaded()) {
			$this->writeHTMLCell(80, 15, 210, 30, $officeAddress->address.'<br>'.$officeAddress->name.'<br>'.$officeAddress->tel, 1, 1, 0, true, '', true);
		}
		
		/* Rate */
		$html = 'レートの有効期間：当月末迄<br />1元 = '.$this->invoice->rmb_to_jpy_rate.'円<br />1元 = '.$this->invoice->rmb_to_usd_rate.'US$';
		$this->writeHTMLCell(80, 15, 210, 47, $html, 1, 0, 0, true, '', true);
		
		// Due date
		$due_date = strtotime($this->invoice->due_date);
		$year = date('Y', $due_date);
		$month = date('m', $due_date);
		$day = date('d', $due_date);
		$html = $year.'/'.$month.'/'.$day.'までにお振込み願います';
		//$html = 'お支找い期日<br />'.date('Y/m/d', strtotime($this->invoice->due_date));
		$this->writeHTMLCell(40, 10, 16, 60, $html, 1, 0, 0, true, '', true);
		
		// Bank Name
		$html = str_replace(' ', '&nbsp;', $this->customer->bank_account);
		$this->writeHTMLCell(121, 10, 60, 60, $html, 1, 0, 0, true, '', true);
		
		/* 2 Blocks */
		$this->writeHTMLCell(10, 10, 270, 63, '', 1, 0, 0, true, '', true);
		$this->writeHTMLCell(10, 10, 280, 63, '', 1, 0, 0, true, '', true);
	}
	
	// Page footer
	public function Footer() {
		// Position at 15 mm from bottom
		$this->SetY(-15);
		// Set font
		$this->SetFont('helvetica', '', 8);
		// Page number
		//$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 10, 'P. '.$this->getAliasNumPage(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
	}
}