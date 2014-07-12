<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Translator extends Controller_CustomTemplate {
	
	public $template = 'template/main';
	
	public function action_index() {
		if ($this->hasPrivilege('translator_gz_list')) {
			$this->action_list(GlobalConstant::FORM_FACTORY_GZ);
		} else if ($this->hasPrivilege('translator_ben_list')) {
			$this->action_list(GlobalConstant::FORM_FACTORY_BEN);
		} else {
			$this->request->redirect('main/no_permission');
		}
	}
	
	public function action_list($inputFactory=NULL) {
		if ($inputFactory == NULL) {
			$factory = $this->request->param('factory');
		} else {
			$factory = $inputFactory;
		}
		
		$this->checkPrivilege('translator_'.$factory.'_list');
		
		$form = new Model_Translator_OrderProductForm($factory);
		$form->populate($_REQUEST);
		
		$form->searchAction();
		
		// Display
		$view = View::factory('translator/translator_product_list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list_'.$factory);
	}
	
	public function action_export() {
		$factory = $this->request->param('factory');
		
		$this->checkPrivilege('translator_'.$factory.'_list');
		
		$form = new Model_Translator_OrderProductForm($factory);
		$form->populate($_REQUEST);
	
		$form->exportAction();
	
		$this->auto_render = FALSE;
	}
	
	public function action_save() {
		$factory = $this->request->param('factory');
		
		$this->checkPrivilege('translator_'.$factory.'_list', Model_RoleMatrix::PERMISSION_WRITE);
		
		$form = new Model_Translator_OrderProductForm($factory);
		$form->populate($_POST);
		
		if ($form->action == Model_Translator_OrderProductForm::ACTION_GO_TO_FACTORY) {
			if ($form->processGoToFactoryAction()) {
				$this->template->set('success', __('transfer.factory'));
			} else {
				$this->template->set('errors', $form->errors);
			}
		} else if ($form->action == Model_Translator_OrderProductForm::ACTION_BACK_TO_AUDITOR) {
			if ($form->processBackToAuditorAction()) {
				$this->template->set('success', __('transfer.auditor'));
			} else {
				$this->template->set('errors', $form->errors);
			}
		}
		
		// Display
		$view = View::factory('translator/translator_product_list');
		$view->set('form', $form);
		$this->template->set('content', $view);
		$this->template->set('submenu', 'list_'.$factory);
	}
}
