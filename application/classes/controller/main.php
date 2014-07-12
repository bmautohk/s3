<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Main extends Controller_CustomTemplate {
	
	public $template = 'template/main';
	
	public function action_index() {
		if (!isset($this->user)) {
			$this->request->redirect('main/no_permission');
		}
		
		// Display
		$view = View::factory('index');
		$this->template->set('content', $view);
		$this->template->set('submenu', '');
	}
	
	public function action_no_permission() {
		$view = View::factory('no_permission');
		$this->template->set('content', $view);
	}
	
	public function action_record_not_found() {
		$view = View::factory('record_not_found');
		$this->template->set('content', $view);
	}
	
	public function action_setting() {
		$form = new Model_Main_SettingForm();
		
		if (HTTP_Request::POST == $this->request->method()) {
			$form->populate($_POST);
			if ($form->changePasswordAction()) {
				$this->template->set('success', 'Password is changed successfully.');
			} else {
				$this->template->set('errors', $form->errors);
			}
		}
		
		$view = View::factory('main/setting');
		$this->template->set('content', $view);
		$this->template->set('form', $form);
	}
}
