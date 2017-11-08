<?php 
App::uses('AppController', 'Controller');
class PerfilsController extends AppController
{
	public function admin_index()
	{
		$this->set('perfils',$this->Perfil->find('all'));
	}
}