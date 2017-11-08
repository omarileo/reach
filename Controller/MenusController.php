<?php
class MenusController extends AppController
{
	public $helpers = array('Html');

	function index()
	{
		//$this -> set('menus', $this->Post->find('all'));
	}
	public function admin_manejoDeMenu()
	{
		$menuActual=array('menu1','menu2','menu3');
		return $this->redirect(array('controller' => 'Dashboard', 'action' => 'admin_index'));
	}
}