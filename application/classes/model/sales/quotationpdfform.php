<?php
require_once APPPATH.'classes/vendor/tcpdf/tcpdf.php';

class Model_Sales_QuotationPDFForm {
	public $order_id;
	public $customerName;
	public $order;
	public $orderProducts;
	public $productTotal;
	public $tax_rate; 
	//public $bank;
	
	public $errors;
	
	public function __construct($order_id) {
		$this->order_id = $order_id;
	}
	
	public function processPrintAction() {
		return $this->printQuotation();
	}
	
	private function printQuotation() {
		$this->order = new Model_Order($this->order_id);
		
		$user = Auth::instance()->get_user();
		if ($user->isSales() && $this->order->created_by != $user->username) {
			return false;
		}
		
		$this->orderProducts = ORM::factory('orderProduct')
								->where('order_id', '=', $this->order_id)
								->where('jp_status', '>=', Model_OrderProduct::STATUS_SALES)
								->where('factory_status', '>=', Model_OrderProduct::STATUS_SALES)
								->order_by('id')
								->find_all();
		
		if ($this->order->s1_client_name != '') {
			$this->customerName = $this->order->s1_client_name;
		} else {
			$this->customerName = $this->order->customer->name;
		}
		
		// Find tax
		$this->tax_rate = Model_ProfitConfig15::getTaxRate();
		
		// Bank
		//$this->bank = new Model_BankAccount($this->order->customer->bank_account_id);
		
		// Calculate order total
		/*$this->productTotal = $this->order->deposit_amt;
		foreach ($this->orderProducts as $orderProduct) {
			$this->productTotal += $orderProduct->qty * $orderProduct->market_price + $orderProduct->delivery_fee * 1.0 / $this->order->rmb_to_jpy_rate;
		}*/
		
		$this->genreatePDF();
		
		return true;
	}
	
	private function genreatePDF() {
		// create new PDF document
		$pdf = new Model_Sales_QuotationPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		
		// set document information
		$pdf->SetCreator('S3');
		$pdf->SetAuthor('S3');
		$pdf->SetTitle('Quotation');
		$pdf->SetSubject('Quotation');
		
		// set default header data
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(true);
		
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		
		// set margins
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		
		// set font
		$pdf->SetFont('cid0jp', '', 10);
		
		$pdf->AddPage();
		//background-color:grey
		$html = '<div style="width:100%;text-align:center;background-color:grey">お見積書'.$this->order_id.'</div>';
		$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
		
		/** Customer Name */
		//$pdf->SetAbsX(10);
		//$pdf->SetAbsY(20);
		$html = $this->customerName.' 御中';
		$pdf->writeHTMLCell(100, 10, 10, 20, $html, 0, 1, 0, true, '', true);
		
		/** Customer Address */
		$customerAddress = '';
		if (!empty($this->order->customer->address1)) {
			$customerAddress .= $this->order->customer->address1.'<br />';
		}
		if (!empty($this->order->customer->address2)) {
			$customerAddress .= $this->order->customer->address2.'<br />';
		}
		if (!empty($this->order->customer->address3)) {
			$customerAddress .= $this->order->customer->address3.'<br />';
		}
		$pdf->writeHTMLCell(100, 30, 10, 25, $customerAddress, 0, 1, 0, true, '', true);
		
		/** Order No & Date */
		$pdf->SetAbsX(240);
		$pdf->SetAbsY(25);
		$html = 'No.';
		$pdf->writeHTMLCell(20, 5, 240, 25, $html, 0, 1, 0, true, '', true);
		
		$html = 'Date: ';
		$pdf->writeHTMLCell(20, 5, 240, 30, $html, 0, 1, 0, true, '', true);
		
		$html = $this->order_id;
		$pdf->writeHTMLCell(30, 5, 260, 25, $html, 0, 1, 0, true, '', true);
		
		$html = Date('Y-m-d');
		$pdf->writeHTMLCell(30, 5, 260, 30, $html, 0, 1, 0, true, '', true);
		
		/** Office Address */
		$officeAddress = new Model_OfficeAddress($this->order->customer->office_address_id);
		if ($officeAddress->loaded()) {
			$html = $officeAddress->name.'<br />'.$officeAddress->address.'<br />'.$officeAddress->tel;
			$pdf->writeHTMLCell(55, 10, 240, 35, $html, 0, 1, 0, true, '', true);
		}
		
		/** Products */
		$view = View::factory('sales/quotation_pdf');
		$view->set('form', $this);
		$pdf->SetY(55);
		$pdf->writeHTMLCell(0, 0, '', '', $view, 0, 1, 0, true, '', true);
		
		$orderCreateDate = date('Y-m-d', strtotime($this->order->create_date));
		//$pdf->Output($orderCreateDate.'_'.$this->order_id.'.pdf', 'D');
		$pdf->Output($this->order_id.'.pdf', 'D');
	}
}