<?php
require_once APPPATH.'classes/vendor/tcpdf/tcpdf.php';

class Model_Accountant_DeliveryNotePDF extends TCPDF {

	public $customer;
	public $deliveryNote;

	// Page header
	public function Header() {
		/** Address */
		//$html = $this->customer->address1.'<br>'.$this->customer->address2.'<br>'.$this->customer->address3.'<br><span style="font-weight:bold;font-size:18pt">'.$this->customer->name.' 御中</span><br>';
		$html = '';
		if (!empty($this->customer->address1)) {
			$html .= $this->customer->address1;
		}
		if (!empty($this->customer->address2)) {
			$html = empty($html) ? $this->customer->address2 : $html.'<br>'.$this->customer->address2;
		}
		if (!empty($this->customer->address3)) {
			$html = empty($html) ? $this->customer->address3 : $html.'<br>'.$this->customer->address3;
		}

		if (!empty($html)) {
			$html .= '<br>';
		}
		$html .= '<span style="font-weight:bold;font-size:18pt">'.$this->customer->name.' 御中</span>';
		//$html =  '<span style="font-weight:bold;font-size:18pt">'.$this->customer->name.' 御中</span><br>'.$this->customer->address1.'<br>'.$this->customer->address2.'<br>'.$this->customer->address3;
		//$this->MultiCell(100, 50, $html, 1, 'L', false, 0, '', '', true, 0, true);
		$this->writeHTMLCell(100, 50, 25, 12, $html, 1, 0, 0, true, '', true);

		$html = '<h1>納品書</h1>';
		$this->writeHTMLCell(0, 0, 160, 12, $html, 0, 1, 0, true, '', true);

		/** Date */
		//$html = '売上日'.date('Y/m/d');
		$print_date = strtotime($this->deliveryNote->print_date);
		$year = date('Y', $print_date);
		$month = date('m', $print_date);
		$day = date('d', $print_date);
		$html = '売上日'.$year.'/'.$month.'/'.$day;
		$this->writeHTMLCell(40, 10, 210, '', $html, 1, 0, 0, true, '', true);

		/** Delivery note no. */
		$this->SetAbsX($this->GetAbsX() + 2);
		$html = 'NO '.$this->deliveryNote->delivery_note_no;
		$this->writeHTMLCell(38, 10, '', '', $html, 1, 1, 0, true, '', true);

		/** Office address */
		$officeAddress = new Model_OfficeAddress($this->deliveryNote->office_address_id);
		//$this->SetAbsX(210);
		//$this->SetAbsY(30);
		$this->writeHTMLCell(80, 15, 210, 30, $officeAddress->address.'<br>'.$officeAddress->name.'<br>'.$officeAddress->tel, 1, 1, 0, true, '', true);

		/* Rate */
		$html = 'レートの有効期間：当月末迄<br />1元 = '.$this->deliveryNote->rmb_to_jpy_rate.'円<br />1元 = '.$this->deliveryNote->rmb_to_usd_rate.'US$';
		//$this->writeHTMLCell(80, 15, 210, 50, $html, 1, 0, 0, true, '', true);
		$this->writeHTMLCell(80, 15, 210, 47, $html, 1, 0, 0, true, '', true);

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