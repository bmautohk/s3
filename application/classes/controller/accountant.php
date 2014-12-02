<?php
class Controller_Accountant extends Controller_CustomTemplate {

	public $template = 'template/main';
	
	public function action_index() {
		if ($this->hasPrivilege('accountant_delivery_note')) {
			$this->action_delivery_note();
		} else if ($this->hasPrivilege('accountant_invoice')) {
			$this->action_invoice();
		} else if ($this->hasPrivilege('accountant_deposit_settlement')) {
			$this->action_deposit_settlement();
		} else if ($this->hasPrivilege('accountant_shipping_fee_settlement')) {
			$this->action_shipping_fee_settlement();
		} else if ($this->hasPrivilege('accountant_invoice_settlement')) {
			$this->action_invoice_settlement();
		} else {
			$this->request->redirect('main/no_permission');
		}
	}
	
	public function action_picking_list() {
		$this->checkPrivilege('accountant_picking_list');
		
		// Display
		$view = View::factory('index');
		$this->template->set('content', $view);
		
		/* $form = new Model_Accountant_PickingListForm();
		$form->populate($_POST);
		
		$form->process();
		
		// Display
		$view = View::factory('accountant/picking_list');
		$view->set('form', $form);
		$this->template->set('content', $view); */
	}
	
	public function action_delivery_note() {
		$form = new Model_Accountant_DeliveryNoteForm();
		$form->populate($_REQUEST);

		if ($form->action == 'scan') {
			$this->checkPrivilege('accountant_delivery_note', Model_RoleMatrix::PERMISSION_WRITE);
			if ($form->processScanAction()) {
				$this->template->set('success', '納品書 is generated successfully.');
			} else {
				$this->template->set('errors', $form->errors);
			}
		} else if ($form->action == 'return') {
			// Return delivery note
			$this->checkPrivilege('accountant_delivery_note', Model_RoleMatrix::PERMISSION_WRITE);
			
			if ($form->processReturnAction()) {
				$this->template->set('success', '納品書 ['.$form->return_delivery_note_no.'] is returned successfully.');
			} else {
				$this->template->set('errors', $form->errors);
			}
		} else if ($form->action == 'back_to_prev_step') {
			// Back to warehouse / auditor
			$this->checkPrivilege('accountant_delivery_note', Model_RoleMatrix::PERMISSION_WRITE);
			
			if ($form->processBackToPrevStepAction()) {
				$this->template->set('success', 'The selected items are returned successfully.');
			} else {
				$this->template->set('errors', $form->errors);
			}
		} else {
			$this->checkPrivilege('accountant_delivery_note');
			$form->processSearchAction();
		}
		
		// Display
		$view = View::factory('accountant/delivery_note');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'delivery_note');
	}
	
	public function action_delivery_note_print() {
		$this->checkPrivilege('accountant_delivery_note');
		
		$id = $this->request->param('id');
		$form = new Model_Accountant_DeliveryNotePDFForm($id);
		
		if ($form->processPrintAction()) {
			// Uncomment for testing
			/* $view = View::factory('accountant/delivery_note_pdf');
			$view->set('form', $form);
			$this->response->body($view); */
			
			$this->auto_render = false; // Don't render template
		} else {
			$this->template->set('errors', $form->errors);
			
			$this->auto_render = false; // Don't render template
			echo 'Fail to generate delivery note.';
		}
	}
	
	public function action_invoice() {
		$form = new Model_Accountant_InvoiceForm();
		$form->populate($_POST);
	
		if ($form->action == 'scan') {
			$this->checkPrivilege('accountant_invoice', Model_RoleMatrix::PERMISSION_WRITE);
			if ($form->processScanAction()) {
				$this->template->set('success', __('save.success'));
			} else {
				$this->template->set('errors', $form->errors);
			}
		} else {
			$this->checkPrivilege('accountant_invoice');
			$form->processSearchAction();
		}
	
		// Display
		$view = View::factory('accountant/invoice');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'invoice');
	}
	
	public function action_invoice_print() {
		$this->checkPrivilege('accountant_invoice');
	
		$id = $this->request->param('id');
		$form = new Model_Accountant_InvoicePDFForm($id);
	
		if ($form->processPrintAction()) {
			// Uncomment for testing
			/* $view = View::factory('accountant/invoice_pdf');
			$view->set('form', $form);
			$this->response->body($view); */
			
			$this->auto_render = false; // Don't render template
		} else {
			$this->template->set('errors', $form->errors);
				
			$this->auto_render = false; // Don't render template
			echo 'Fail to generate delivery note.';
		}
	}
	
// -------------------------------------------------------------------------
	public function action_order_return_invoice() {
		$form = new Model_Accountant_OrderReturnInvoiceForm();
		$form->populate($_REQUEST);
	
		if ($form->action == 'scan') {
			$this->checkPrivilege('accountant_order_return_invoice', Model_RoleMatrix::PERMISSION_WRITE);
			if ($form->processScanAction()) {
				$this->template->set('success', '負納品書 is generated successfully.');
			} else {
				$this->template->set('errors', $form->errors);
			}
		} else {
			$this->checkPrivilege('accountant_order_return_invoice');
			$form->processSearchAction();
		}
	
		// Display
		$view = View::factory('accountant/order_return_invoice');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'order_return_invoice');
	}
	
	public function action_order_return_invoice_print() {
		$this->checkPrivilege('accountant_order_return_invoice');
	
		$id = $this->request->param('id');
		$form = new Model_Accountant_OrderReturnInvoicePDFForm($id);
	
		if ($form->processPrintAction()) {
			// Uncomment for testing
			/* $view = View::factory('accountant/shipping_fee_delivery_note_pdf');
				$view->set('form', $form);
			$this->response->body($view); */
	
			$this->auto_render = false; // Don't render template
		} else {
			$this->template->set('errors', $form->errors);
	
			$this->auto_render = false; // Don't render template
			echo 'Fail to generate order return invoice.';
		}
	}
// -------------------------------------------------------------------------
	public function action_shipping_fee_delivery_note() {
		$form = new Model_Accountant_ShippingFeeDeliveryNoteForm();
		$form->populate($_REQUEST);
	
		if ($form->action == 'scan') {
			$this->checkPrivilege('accountant_shipping_fee_delivery_note', Model_RoleMatrix::PERMISSION_WRITE);
			if ($form->processScanAction()) {
				$this->template->set('success', '納品書 is generated successfully.');
			} else {
				$this->template->set('errors', $form->errors);
			}
		} else {
			$this->checkPrivilege('accountant_shipping_fee_delivery_note');
			$form->processSearchAction();
		}
	
		// Display
		$view = View::factory('accountant/shipping_fee_delivery_note');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'shipping_fee_delivery_note');
	}
	
	public function action_shipping_fee_delivery_note_print() {
		$this->checkPrivilege('accountant_shipping_fee_delivery_note');
	
		$id = $this->request->param('id');
		$form = new Model_Accountant_ShippingFeeDeliveryNotePDFForm($id);
	
		if ($form->processPrintAction()) {
			// Uncomment for testing
			/* $view = View::factory('accountant/shipping_fee_delivery_note_pdf');
			$view->set('form', $form);
			$this->response->body($view); */
				
			$this->auto_render = false; // Don't render template
		} else {
			$this->template->set('errors', $form->errors);
				
			$this->auto_render = false; // Don't render template
			echo 'Fail to generate delivery note.';
		}
	}
/* ***************************************************************************************************************
 * Deposit 確認
 ****************************************************************************************************************/
	
	public function action_deposit_settlement() {
		$form = new Model_Accountant_DepositSettlementForm();
		$form->populate($_REQUEST);
		
		if (HTTP_Request::POST == $this->request->method()) {
			$this->checkPrivilege('accountant_deposit_settlement', Model_RoleMatrix::PERMISSION_WRITE); // Check privilege
			
			if ($form->action == 'confirm') {
				if ($form->processConfirmAction()) {
					$successMsg = 'The deposit is confirmed.';
					if ($form->successMsg != NULL) {
						$successMsg .= '<br />'.$form->successMsg;
					}
					$this->template->set('success', $successMsg);
				} else {
					$this->template->set('errors', $form->errors);
				}
				
				$view = View::factory('accountant/deposit_settlement_list');
			} else if ($form->action == 'add') {
				if ($form->processAddAction()) {
					$this->template->set('success', 'The deposit is added and confirmed.');
				} else {
					$this->template->set('errors', $form->errors);
				}
				
				$view = View::factory('accountant/deposit_settlement_add');
			}
			
			// Display
			$view->set('form', $form);
			$this->template->set('content', $view);
			$this->template->set('submenu', 'deposit_settlement');
		} else {
			if ($form->action == 'confirm') {
				// Confirm deposit
				$this->checkPrivilege('accountant_deposit_settlement', Model_RoleMatrix::PERMISSION_WRITE); // Check privilege
				
				if ($form->processConfirmInit()) {
					// Display
					$view = View::factory('accountant/deposit_settlement_confirm');
					$view->set('form', $form);
					$this->template->set('content', $view);
					$this->template->set('submenu', 'deposit_settlement');
					return;
				} else {
					$this->template->set('errors', $form->errors);
				}
			} else if ($form->action == 'add') {
				// Add deposit
				$this->checkPrivilege('accountant_deposit_settlement', Model_RoleMatrix::PERMISSION_WRITE); // Check privilege
				
				if ($form->processAddInit()) {
					// Display
					$view = View::factory('accountant/deposit_settlement_add');
					$view->set('form', $form);
					$this->template->set('content', $view);
					$this->template->set('submenu', 'deposit_settlement');
					return;
				} else {
					$this->template->set('errors', $form->errors);
				}
			} else {
				$this->checkPrivilege('accountant_deposit_settlement'); // Check privilege
			}
			
			// Default action
			$form->processSearchAction();
			
			// Display
			$view = View::factory('accountant/deposit_settlement_list');
			$view->set('form', $form);
			$this->template->set('content', $view);
			$this->template->set('submenu', 'deposit_settlement');
		}
	}
	
	public function action_deposit_settlement_export() {
		$this->checkPrivilege('accountant_deposit_settlement');
	
		$form = new Model_Accountant_DepositSettlementForm();
		$form->populate($_REQUEST);
	
		$form->exportAction();
	
		$this->auto_render = FALSE;
	}
	
/* ***************************************************************************************************************
 * 經費入金確認
****************************************************************************************************************/
	public function action_shipping_fee_settlement() {
		$form = new Model_Accountant_ShippingFeeSettlementForm();
		$form->populate($_REQUEST);
	
		if (HTTP_Request::POST == $this->request->method()) {
			$this->checkPrivilege('accountant_shipping_fee_settlement', Model_RoleMatrix::PERMISSION_WRITE); // Check privilege
			
			if ($form->action == Model_Accountant_ShippingFeeSettlementForm::ACTION_CONFIRM_INIT) {
				/**
				 * Go to confirm page
				 */
				if ($form->processConfirmInit()) {
					// Display
					$view = View::factory('accountant/shipping_fee_settlement_confirm');
					$view->set('form', $form);
					$this->template->set('content', $view);
					$this->template->set('submenu', 'shipping_fee_settlement');
					return;
				} else {
					$this->template->set('errors', $form->errors);
				}
			
			} if ($form->action == Model_Accountant_ShippingFeeSettlementForm::ACTION_CANCEL_INIT) {
				/**
				 * Go to cancel page
				 */
				if ($form->processCancelInit()) {
					// Display
					$view = View::factory('accountant/shipping_fee_settlement_cancel');
					$view->set('form', $form);
					$this->template->set('content', $view);
					$this->template->set('submenu', 'shipping_fee_settlement');
					return;
				} else {
					$this->template->set('errors', $form->errors);
				}
			
			} if ($form->action == Model_Accountant_ShippingFeeSettlementForm::ACTION_CONFIRM) {
				if ($form->processConfirmAction()) {
					$this->template->set('success', '請求書 has been settled.');
				} else {
					$this->template->set('errors', $form->errors);
				}
				
				// Display
				$view = View::factory('accountant/shipping_fee_settlement_list');
				$view->set('form', $form);
				$this->template->set('content', $view);
				$this->template->set('submenu', 'shipping_fee_settlement');
				
			} if ($form->action == Model_Accountant_ShippingFeeSettlementForm::ACTION_CANCEL) {
				if ($form->processCancelAction()) {
					$this->template->set('success', '請求書 has been cancelled.');
				} else {
					$this->template->set('errors', $form->errors);
				}
				
				// Display
				$view = View::factory('accountant/shipping_fee_settlement_list');
				$view->set('form', $form);
				$this->template->set('content', $view);
				$this->template->set('submenu', 'shipping_fee_settlement');
			}
			
		} else {
			$this->checkPrivilege('accountant_shipping_fee_settlement'); // Check privilege
			
			// Search action
			$form->processSearchAction();
				
			// Display
			$view = View::factory('accountant/shipping_fee_settlement_list');
			$view->set('form', $form);
			$this->template->set('content', $view);
			$this->template->set('submenu', 'shipping_fee_settlement');
		}
	}
	
	public function action_invoice_settlement() {
		$form = new Model_Accountant_InvoiceSettlementForm();
		$form->populate($_POST);

		if ($form->action == 'search') {
			$this->checkPrivilege('accountant_invoice_settlement');
			$form->processSearchAction();
		} else if ($form->action == 'save') {
			$this->checkPrivilege('accountant_invoice_settlement', Model_RoleMatrix::PERMISSION_WRITE);
			
			if ($form->processSaveAction()) {
				$this->template->set('success', __('save.success'));
			} else {
				$this->template->set('errors', $form->errors);
			}
		} /*else {
			$this->checkPrivilege('accountant_invoice_settlement');
			$form->processShowRemainingAction();
		}*/

		// Display
		$view = View::factory('accountant/invoice_settlement');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'invoice_settlement');
	}
	
	/*
	 * Trigger by 'delivery_note.php".
	 * Get order information when selecting delivery address.
	 */
	public function action_order() {
		$id = $this->request->param('id');
		
		if ($id != '') {
			$order = ORM::factory('order')
				->where('id', '=', $id)
				->find();
			
			if ($order->loaded()) {
				echo json_encode(array('s1_client_name' => $order->s1_client_name,
										'tel' => $order->tel,
										'postal_code' => $order->postal_code));
			}
		}
		
		$this->auto_render = false; // Don't render template
	}
}