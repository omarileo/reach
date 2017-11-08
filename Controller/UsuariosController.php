<?php
App::uses('AppController', 'Controller');
class UsuariosController extends AppController
{
	public $helpers = array('Html', 'Form');
	public $components = array('Session','Flash');

	public function admin_login()
	{	
		if ( $this->request->is('post') )
		{
			if ( $this->Auth->login() )
			{	
				//$this->Auth->redirectUrl(); ---> Ultima URL visitada por el usuario
				$this->redirect('/');
			}
			else
			{
				$this->Flash->Success('Nombre de usuario y/o clave incorrectos.', null, array(), 'danger');
			}
		}
		$this->layout	= 'login';
	}

	public function admin_logout()
	{
		$this->Session->destroy();
		$this->redirect($this->Auth->logout());
	}

	public function admin_lock()
	{
		$this->layout		= 'login';

		if ( ! $this->request->is('post') )
		{
			if ( ! $this->Session->check('Admin.lock') )
			{
				$this->Session->write('Admin.lock', array(
					'status'		=> true,
					'referer'		=> $this->referer()
				));
			}
		}
		else
		{
			$administrador		= $this->Administrador->findById($this->Auth->user('id'));

			if ( $this->Auth->password($this->request->data['Administrador']['clave']) === $administrador['Administrador']['clave'] )
			{
				$referer		= $this->Session->read('Admin.lock.referer');
				$this->Session->delete('Admin.lock');
				$this->redirect($referer);
			}
			else
				$this->Flash->success('Clave incorrecta.', null, array(), 'danger');
		}
	}

	public function admin_index()
	{
		$this->set('usuarios', $this->Usuario->find('all'));
	}

	public function admin_view($id = null)
	{
		$this->Usuario->id = $id;
		if(!$this->Usuario->exists())
		{
			throw new NotFoundException("Usuario no existe!.");
		}
		$this->set('usuario',$this->Usuario->findById($id));
	}

	

	public function admin_delete($id = null) 
	{
		if ($this->request->is('get')) {
			throw new MethodNotAllowedException('Error...');
		}
		if ($this->Usuario->delete($id)) {
			$this->Flash->success('El usuario ha sido eliminado', $element = 'default', $params = array('class'=>'success'));
			$this->redirect(array('action'=>'admin_index'));
		}
		$this->Flash->success('No se logró eliminar el usuario, por favor intentelo más tarde.');
		$this->redirect(array('action'=>'admin_index'));
	}

	public function admin_add()
	{
		if($this->request->is('post'))
		{
			$this->Usuario->create();
			if($this->Usuario->save($this->request->data))
			{
				$this->Flash->success('Usuario creado', 'default', array('class'=>'success'));
				return $this->redirect(array('action'=>'admin_index'));
			}
			$this->Flash->success('No se pudo crear el usuario');
		}
		/*
		$perfil = $this->Usuario->Perfil->find('list');
		$this->set('idperfil', $perfil);
		*/
	}
}