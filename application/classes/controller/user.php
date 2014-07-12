<?php
class Controller_User extends Controller {
	
	public function action_index() {
		$user = Auth::instance()->get_user();
		if (!$user) {
			$this->request->redirect('user/login');
		} else {
			$this->request->redirect('main');
		}
	}
	
	public function action_login() {
		if (HTTP_Request::POST == $this->request->method()) {
			// Attempt to login user
			//$remember = array_key_exists('remember', $this->request->post()) ? (bool) $this->request->post('remember') : FALSE;
			$remember = false;
			$user = Auth::instance()->login($this->request->post('username'), $this->request->post('password'), $remember);
			
			if ($user) {
				// Login successfully
				$this->request->redirect('main');
				/* $view = View::factory('login');
				$this->response->body($view); */
			} else {
				// Fail to login
				$view = View::factory('login');
				$view->set('errorMessage', 'Login name / password is wrong.');
				$this->response->body($view);
			}
		} else {
			// Display
			$view = View::factory('login');
			$this->response->body($view);
		}
	}
	
	public function action_logout() {
		// Log user out
		Auth::instance()->logout();
		
		// Redirect to login page
		Request::current()->redirect('user/login');
	}
}