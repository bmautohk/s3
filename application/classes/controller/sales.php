<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Sales extends Controller_CustomTemplate {
	
	public $template = 'template/main';
	
	public function action_index() {
		if ($this->hasPrivilege('sales_order_list')) {
			$this->action_order_list();
		} else if ($this->hasPrivilege('sales_order_return')) {
			$this->action_order_return();
		} else if ($this->hasPrivilege('sales_shipping_fee')) {
			$this->action_shipping_fee();
		} else if ($this->hasPrivilege('sales_customer_list')) {
			$this->action_customer_list();
		} else {
			$this->request->redirect('main/no_permission');
		}
	}

	public function action_order_list() {
		$this->checkPrivilege('sales_order_list');
		
		$form = new Model_Sales_OrderSearchForm();
		$form->defaultSearchAction();
		
		// Display
		$view = View::factory('sales/order_list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'order_list');
	}
	
	public function action_order_search() {
		$this->checkPrivilege('sales_order_list');
		
		$form = new Model_Sales_OrderSearchForm();
		$form->populate($_REQUEST);
		
		if ($form->action == 'export') {
			$form->exportAction();
			$this->auto_render = FALSE;
			
		} else if ($form->action == 'cancel') {
			$this->checkPrivilege('sales_order_list', Model_RoleMatrix::PERMISSION_WRITE);
			
			if (!$form->cancelAction()) {
				// Order not found
				$this->template->set('content', "Order not found");
				return;
			} else {
				$orderProduct = new Model_OrderProduct($form->order_product_id);
				$this->template->set('success', 'Product ['.$orderProduct->product_cd.'] of order ['.$orderProduct->order_id.'] is cancelled.');
			}
			
			// Display
			$view = View::factory('sales/order_list');
			$view->set('form', $form);
			$this->template->set('content', $view);
			$this->template->set('submenu', 'order_list');
		} else {
			// Search by criteria
			$form->searchAction();
			
			// Display
			$view = View::factory('sales/order_list');
			$view->set('form', $form);
			$this->template->set('content', $view);
			$this->template->set('submenu', 'order_list');
		}
	}
	
	public function action_order_add() {
		$this->checkPrivilege('sales_order_list', Model_RoleMatrix::PERMISSION_WRITE);
		
		$form = new Model_Sales_OrderForm();
		$form->initAdd();
		
		// Display
		$view = View::factory('sales/order_maint');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'order_add');
	}
	
	public function action_order_edit() {
		$this->checkPrivilege('sales_order_list', Model_RoleMatrix::PERMISSION_WRITE);
		
		$id = $this->request->param('id');
		$form = new Model_Sales_OrderForm();
		if (!$form->retrieve($id)) {
			// Order not found
			$this->template->set('content', "Order not found");
			return;
		}
		
		// Display
		$view = View::factory('sales/order_maint');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'order_add');
	}
	
	public function action_order_save() {
		$this->checkPrivilege('sales_order_list', Model_RoleMatrix::PERMISSION_WRITE);
		
		$form = new Model_Sales_OrderForm();
		$form->populate($_POST);
		
		$view = View::factory('sales/order_maint');

		if ($form->action == Model_Sales_OrderForm::ACTION_SAVE || $form->action == Model_Sales_OrderForm::ACTION_SAVE_ONLY) {
			$isPrintQuotation = $form->action == Model_Sales_OrderForm::ACTION_SAVE ? true : false;
			if ($form->processSaveAction($isPrintQuotation)) {
				$this->template->set('success', __('save.success'));
				
				if (sizeOf($form->warnings) > 0) {
					$view->set('warnings', $form->warnings);
				}
			} else {
				$this->template->set('errors', $form->errors);
			}
		} else if ($form->action == Model_Sales_OrderForm::ACTION_GO_TO_KAITOSTAFF) {
			if ($form->processGoToKaitoStaff()) {
				$this->template->set('success', __('transfer.kaitostaff'));
			} else {
				$this->template->set('errors', $form->errors);
			}
		} else if ($form->action == Model_Sales_OrderForm::ACTION_CALCULATE_PROFIT) {
			if (!$form->processProfitCalculation()) {
				$this->template->set('errors', $form->errors);
			}
		} else {
			$form->processOrderTypeChange();
		}

		// Display
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'order_add');
	}
	
	public function action_quotation_print() {
		$this->checkPrivilege('sales_order_list');
	
		$id = $this->request->param('id');
		$form = new Model_Sales_QuotationPDFForm($id);
	
		if ($form->processPrintAction()) {
			// Uncomment for testing
			/* $view = View::factory('sales/quotation_pdf');
			$view->set('form', $form);
			$this->response->body($view); */
				
			$this->auto_render = false; // Don't render template
		} else {
			$this->template->set('errors', $form->errors);
				
			$this->auto_render = false; // Don't render template
			echo 'Fail to quotation.';
		}
	}
	
	public function action_order_return() {
		$form = new Model_Sales_OrderReturnForm();
		$form->populate($_REQUEST);
	
		if (HTTP_Request::POST == $this->request->method()) {
			$this->checkPrivilege('sales_order_return', Model_RoleMatrix::PERMISSION_WRITE);
			if ($form->processSaveAction()) {
				$this->template->set('success', __('save.success'));
			} else {
				$this->template->set('errors', $form->errors);
			}
				
			// Display
			$view = View::factory('sales/order_return_add');
			$view->set('form', $form);
			$this->template->set('content', $view);
			$this->template->set('submenu', 'order_return');
		} else {
			if ($form->action == 'add') {
				$this->checkPrivilege('sales_order_return', Model_RoleMatrix::PERMISSION_WRITE);
				
				if ($form->processAddAction()) {
					// Display
					$view = View::factory('sales/order_return_add');
					$view->set('form', $form);
					$this->template->set('content', $view);
					$this->template->set('submenu', 'order_return');
					return;
				} else {
					$this->template->set('errors', $form->errors);
				}
			} else {
				$this->checkPrivilege('sales_order_return');
				$form->processSearchAction();
			}
				
			// Display
			$view = View::factory('sales/order_return_list');
			$view->set('form', $form);
			$this->template->set('content', $view);
			$this->template->set('submenu', 'order_return');
		}
	}
	
/* ***************************************************************************************************************
 * 輸入經費
****************************************************************************************************************/
	public function action_shipping_fee() {
		$form = new Model_Sales_ShippingFeeForm();
		$form->populate($_REQUEST);
		
		if (HTTP_Request::POST == $this->request->method()) {
			$this->checkPrivilege('sales_shipping_fee', Model_RoleMatrix::PERMISSION_WRITE);
			if ($form->processSaveAction()) {
				$this->template->set('success', __('save.success'));
			} else {
				$this->template->set('errors', $form->errors);
			}
			
			// Display
			$view = View::factory('sales/shipping_fee_add');
			$view->set('form', $form);
			$this->template->set('content', $view);
			$this->template->set('submenu', 'shipping_fee');
		} else {
			if ($form->action == 'add') {
				$this->checkPrivilege('sales_shipping_fee', Model_RoleMatrix::PERMISSION_WRITE);
				if ($form->processAddAction()) {
					// Display
					$view = View::factory('sales/shipping_fee_add');
					$view->set('form', $form);
					$this->template->set('content', $view);
					$this->template->set('submenu', 'shipping_fee');
					return;
				} else {
					$this->template->set('errors', $form->errors);
				}
			} else {
				$this->checkPrivilege('sales_shipping_fee');
				$form->processSearchAction();
			}
			
			// Display
			$view = View::factory('sales/shipping_fee_list');
			$view->set('form', $form);
			$this->template->set('content', $view);
			$this->template->set('submenu', 'shipping_fee');
		}
	}
	
/* ***************************************************************************************************************
 * deposit 確認入金
****************************************************************************************************************/
	public function action_deposit_settlement() {
		$form = new Model_Sales_DepositSettlementForm();
		$form->populate($_REQUEST);
	
		if (HTTP_Request::POST == $this->request->method()) {
			$this->checkPrivilege('sales_deposit_settlement', Model_RoleMatrix::PERMISSION_WRITE); // Check privilege
				
			if ($form->processSaveAction()) {
				$this->template->set('success', __('save.success'));
			} else {
				$this->template->set('errors', $form->errors);
			}
				
			// Display
			$view = View::factory('sales/deposit_settlement_add');
			$view->set('form', $form);
			$this->template->set('content', $view);
			$this->template->set('submenu', 'deposit_settlement');
		} else {
			if ($form->action == 'add') {
				$this->checkPrivilege('sales_deposit_settlement', Model_RoleMatrix::PERMISSION_WRITE); // Check privilege
	
				if ($form->processAddAction()) {
					// Display
					$view = View::factory('sales/deposit_settlement_add');
					$view->set('form', $form);
					$this->template->set('content', $view);
					$this->template->set('submenu', 'deposit_settlement');
					return;
				} else {
					$this->template->set('errors', $form->errors);
				}
			} else {
				$this->checkPrivilege('sales_deposit_settlement'); // Check privilege
				$form->processSearchAction();
			}
				
			// Display
			$view = View::factory('sales/deposit_settlement_list');
			$view->set('form', $form);
			$this->template->set('content', $view);
			$this->template->set('submenu', 'deposit_settlement');
		}
	}
	
/* ***************************************************************************************************************
 * 客戶列表
****************************************************************************************************************/
	public function action_customer_list() {
		$this->checkPrivilege('sales_customer');
		
		$form = new Model_Sales_CustomerForm();
		$form->searchAction();
		
		$customers = ORM::factory('customer')->find_all();
		
		$view = View::factory('sales/customer_list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'customer_list');
	}
	
	public function action_customer_add() {
		$this->checkPrivilege('sales_customer', Model_RoleMatrix::PERMISSION_WRITE);
		
		$form = new Model_Sales_CustomerForm();
		$form->initAddAction();
		
		$view = View::factory('sales/customer_maint');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'customer_add');
	}
	
	public function action_customer_edit() {
		$this->checkPrivilege('sales_customer', Model_RoleMatrix::PERMISSION_WRITE);
		
		$id = $this->request->param('id');
		
		$form = new Model_Sales_CustomerForm();
		if (!$form->retrieve($id)) {
			$this->request->redirect('main/no_permission');
		}
		
		$view = View::factory('sales/customer_maint');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'customer_add');
	}
	
	public function action_customer_delete() {
		$this->checkPrivilege('sales_customer', Model_RoleMatrix::PERMISSION_WRITE);

		$form = new Model_Sales_CustomerForm();
		$form->populate($_POST);
		
		if ($form->deleteAction()) {
			$this->template->set('success', __('delete.success'));
		} else {
			$this->template->set('errors', $form->errors);
		}
		
		$view = View::factory('sales/customer_list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'customer_list');
	}
	
	public function action_customer_save() {
		$this->checkPrivilege('sales_customer', Model_RoleMatrix::PERMISSION_WRITE);
		
		$form = new Model_Sales_CustomerForm();
		$form->populate($_POST);
		if ($form->saveAction()) {
			$this->template->set('success', __('save.success'));
		} else {
			$this->template->set('errors', $form->errors);
		}
		
		$view = View::factory('sales/customer_maint');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'customer_add');
	}
	
	public function action_update_delivery_method_init() {
		$form = new Model_Sales_UpdateDeliveryMethodForm();
		$form->populate($_POST);
		
		$form->init();
		
		$this->auto_render = false;
		$view = View::factory('sales/update_delivery_method');
		$view->set('form', $form);
		
		echo $view;
	}
	
	public function action_update_delivery_method() {
		$form = new Model_Sales_UpdateDeliveryMethodForm();
		$form->populate($_POST);
		
		if ($form->processSaveAction()) {
			echo $form->getCurrentDeliveryMethod();
		}
		
		$this->auto_render = false;
	}

}