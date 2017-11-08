<?php
/**
* 
*/
App::uses('AppController','Controller');
class ContactosController extends AppController
{
	public function admin_index()
	{

	}
	public function admin_buscarContactos()
	{
		
		
		if ($this->request->isAjax())
		{
			$this->autoRender = false;
			$nombreContacto = $this->request->query('term');
			$nombreCliente = $this->request->query('cliente');
			
			
			$results = $this->Contacto->find('all', array(
											'conditions'=> array('Contacto.nombre LIKE ' => '%'.$nombreContacto.'%'),
											'recursive' => -1
											));

			$resultArr = array();
			foreach ($results as $result) {
				$resultArr[] = array(
						'label' => $result['Contacto']['nombre'], 
						'value' => $result['Contacto']['nombre']
						);
			}

			echo json_encode($resultArr);
		}
	}
	public function admin_buscarContactosxId()
	{
		//prx($this->request->params['named']['id']);
		if ($this->request->isAjax())
		//if (1)
		{
			$this->autoRender = false;
			$nombreContacto = $this->request->query('term');
			$idCliente = $this->request->query['idcliente'];
			
			
			$results = $this->Contacto->find('all', array(
											'conditions'=> array('Contacto.nombre LIKE ' => '%'.$nombreContacto.'%',
																 'Contacto.clientes_id' => $idCliente ),
											'recursive' => -1
											));

			$resultArr = array();
			foreach ($results as $result) {
				$resultArr[] = array(
						'label' => $result['Contacto']['nombre'], 
						'value' => $result['Contacto']['nombre']
						);
			}

			echo json_encode($resultArr);
		}
	}
}