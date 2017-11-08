<?php
App::uses('AppController','Controller');
/**
* 
*/
class EstadosController extends AppController
{
	public $components = array('Flash');

	public $paginate = array(
			'limit'	=>10
	);

	public function admin_index()
	{
		$this->set('estados',$this->paginate());
	}

	public function admin_add()
	{
		if($this->request->is('post'))
		{
			$this->Estado->create();
			if($this->Estado->save($this->request->data))
			{
				$this->Flash->success('Estado creado','default',array('class'=>'success'));
				return $this->redirect(array('action'=>'index'));
			}
			$this->Flash->success('No se pudo crear el usuario');
		}
	}

	public function admin_edit($id = null)
	{
	    if (!$id) {
	        throw new NotFoundException(__('Detectamos un problema con el id del estado, por favor salga e intentelo nuevamente.'));
	    }

	    $estado = $this->Estado->findById($id);
	    if (!$estado) {
	        throw new NotFoundException(__('El estado no fue encontrado.'));
	    }

	    if ($this->request->is(array('post', 'put'))) {
	        $this->Estado->id = $id;
	        
	        if ($this->Estado->save($this->request->data)) {
	            $this->Flash->success(__('El estado se modificó correctamente.'));
	            return $this->redirect(array('action' => 'index'));
	        }
	        $this->Flash->error(__('No se pudo guardar el cambio.'));
	    }

	    if (!$this->request->data) {
	        $this->request->data = $estado;
	    }
	}

	public function admin_delete($id = null)
	{
		if ($this->request->is('get')) {
			throw new MethodNotAllowedException("Error al procesar la solicitud");
		}
		if ($this->Estado->delete($id)) {
			$this->Flash->success('El estado se ha eliminado correctamente','default',array('class'=>'success'));
			return $this->redirect(array('action' => 'index'));
		}
		$this->Flash->success('No se logró eliminar el estado, por favor intentelo más tarde.');
		$this->redirect(array('action'=>'admin_index'));

	}
}