<?php 
App::uses('AppController','Controller');
class MantenedoresController extends AppController{
	public $uses = array('Usuario');
	public function admin_index(){
		$this->set->Usuario;
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

		$perfil = $this->Usuario->Perfil->find('list');
		$this->set('idperfil', $perfil);
	}

	public function admin_edit($id = null)
	{
		$this->Usuario->id = $id;
		if (!$this->Usuario->exists()) {
			throw new NotFoundException('El usuario no existe!');
		}
		$usuario = $this -> Usuario -> findById($id);
		if (!$usuario) {
			throw new NotFoundException('El usuario no existe en nuestra base de datos' );
		}
		if ($this->request->is(array('post','put'))) {
			$this->Usuario->id = $id;
			if ($this->Usuario->save($this->request->data)) {
				$this->Flash->success('El usuario se ha modificado.');
				return $this->redirect(array('action'=>'admin_index'));
			}
			$this->Flash->success('No se pudo actualizar el usuario, favor intentelo más tarde.');
		}

		if ($this->request->data) {
			$this->request->data = $usuario;
		}
		

	}
}
?>