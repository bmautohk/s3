<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Error extends Controller {

	public function before()
	{
		parent::before();

		$this->response->status((int) $this->request->action());
	}

	public function action_401()
	{
		$view = View::factory('error/401');
		$view->set('errorCode', 401);
		$view->set('description', "You don't have authorization to do the action.");
		$this->response->body($view->render());
	}
}