<?php
class Controller_CustomTemplate extends Kohana_Controller_Template {
	public $user;
	
	public function __construct(Request $request, Response $response) {
		parent::__construct($request, $response);
		$this->user = Auth::instance()->get_user();
	}
	
	public function before() {
		$controller = Request::initial()->controller();
		
		if (!$this->user) {
			$this->request->redirect('user/login');
		} else {
			parent::before();
		}
	}
	
	public function checkPrivilege($page, $permission=NULL) {
		if (!$this->user->hasPrivilege($page, $permission)) {
			// No privilege
			$this->request->redirect('main/no_permission');
		}
	}
	
	public function hasPrivilege($page) {
		return $this->user->hasPrivilege($page);
	}
}