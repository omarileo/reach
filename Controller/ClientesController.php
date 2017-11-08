<?php
/**
* 
*/
App::uses('AppController','Controller');
class ClientesController extends AppController
{
	public function admin_index()
	{

	}

	public function admin_buscarClientes()
	{
		if ($this->request->isAjax())
		//if (1)
		{
			$this->autoRender = false;
			$nombreCliente = $this->request->query('term');
			
			$this->loadModel('Cliente');

			$results = $this->Cliente->find('all', array(
											'conditions'=> array('CONCAT(Cliente.nombres,Cliente.paterno,Cliente.materno) 					LIKE ' => '%'.$nombreCliente.'%'),
											'recursive' => -1,
											'contain'	=> array('contactosdecliente')
											));
			$resultArr = array();
			foreach ($results as $result) {
				$resultArr[] = array(
						'label' => $result['Cliente']['nombres']." ".$result['Cliente']['paterno']." ".$result['Cliente']['materno'], 
						'value' => $result['Cliente']['nombres']." ".$result['Cliente']['paterno']." ".$result['Cliente']['materno'],
						'id' => $result['Cliente']['id'], 
						'dni' => $result['Cliente']['dni'],
						'contactos' => $result['contactosdecliente']
						);
			}





			echo json_encode($resultArr);
		}
	}

	// Roberto Contardo 03-11-2017 10:15

	public function admin_creacion_cliente(){

		$this->layout = 'dialog';

		$data = $this->request->data;

		$id_dialog = 'main-dialog';

		if ( isset( $data['id_dialog'] ) ){

			$id_dialog = $data['id_dialog'];
		}

		$title = '';

		if ( isset( $data['title'] ) ){

			$title = $data['title'];
		}

		$classes = '';

		if ( isset( $data['classes'] ) ){

			$classes = $data['classes'];
		}

		$this->set('id_dialog', $id_dialog);

		$this->set('title', $title);	
		
		$this->set('classes', $classes);

		// Combos

		$this->loadModel('Region');

		$regiones = $this->Region->find('all');

		array_walk($regiones, function(&$item){

			$item = $item['Region'];
		});

		$this->set('regiones', $regiones);	

		$this->loadModel('Comuna');

		$comunas = $this->Comuna->find('all', array(

				'fields' 	=> array(

					'id',
					'ciudades_id',
					'nombre',
				)
			)
		);
		
		array_walk($comunas, function(&$item){

			$item = $item['Comuna'];
		});

		$this->set('comunas', $comunas);
	}

	public function admin_crear_cliente(){

		$this->layout = 'json';

		$data = $this->request->data;

		$result = array(

			"status"					=> 'danger',
			"message"					=> 'Ha ocurrido un error.'
		);

		$nombres_cliente = $data['Cliente']['nombres'];
		$paterno_cliente = $data['Cliente']['paterno'];
		$materno_cliente = $data['Cliente']['materno'];
		$razonsocial_cliente = $data['Cliente']['razonsocial'];

		$username_usuariocliente = "";

		$nombre_usuariocliente = $data['Cliente']['nombres'] . ' ' . $data['Cliente']['paterno'] . ' ' . $data['Cliente']['materno'];

		if ( $data['Cliente']['tipocliente'] == 1 ){ // Cliente es Empresa

			$nombres_cliente = "";
			$paterno_cliente = "";
			$materno_cliente = "";

			$nombre_usuariocliente = $data['Cliente']['razonsocial'];			
		}
		else{

			$razonsocial_cliente = "";
		}

		$nombrecorto_usuariocliente = "";
		$password_usuariocliente = "";
		$email_usuariocliente = $data['Cliente']['email'];
		$dni_usuariocliente = $data['Cliente']['dni'];
		$vigente_usuariocliente = 0;
		$acc_perfiles_id_usuariocliente = 3;

		$nuevo_cliente = array(

			"Cliente" 	=> array(

				"dni" 			=> $data['Cliente']['dni'],
				"tipocliente" 	=> $data['Cliente']['tipocliente'],
				"nombres" 		=> $nombres_cliente,
				"paterno" 		=> $paterno_cliente,
				"materno" 		=> $materno_cliente,
				"razonsocial" 	=> $razonsocial_cliente,
				"calle" 		=> $data['Cliente']['calle'],
				"numero" 		=> $data['Cliente']['numero'],
				"depto" 		=> $data['Cliente']['depto'],
				//"comunas_id" 	=> $data['Cliente']['comunas_id'],
				"codigopostal" 	=> $data['Cliente']['codigopostal'],
				"telefono" 		=> $data['Cliente']['telefono'],
				"celular" 		=> $data['Cliente']['celular'],
				"email" 		=> $data['Cliente']['email'],
			),
			"UsuarioCliente" => array(

				"username" 		=> $username_usuariocliente,
				"nombre" 		=> $nombre_usuariocliente,
				"nombrecorto" 	=> $nombrecorto_usuariocliente,
				"password" 		=> $password_usuariocliente,
				"email" 		=> $email_usuariocliente,
				"dni" 			=> $dni_usuariocliente,
				"vigente" 		=> $vigente_usuariocliente,
				"acc_perfiles_id"=> $acc_perfiles_id_usuariocliente,
			)
		);

		try {

			$this->Cliente->create();

			$this->Cliente->save( $nuevo_cliente );
				
			$result = array(

				'estatus' => 'exito',
				'mensaje' => 'Los datos del vehículo han sido actualizados.'
			);	
		}
		catch (PDOException $e) {

			$result['mensaje'] = $e->errorInfo[2];
		}

		$this->set('result', $nuevo_cliente);

		$this->render(false);
	}

	// Roberto Contardo 03-11-2017 10:15

	public function admin_creacion_contacto(){

		$this->layout = 'dialog';

		$data = $this->request->data;

		$id_dialog = 'main-dialog';

		if ( isset( $data['id_dialog'] ) ){

			$id_dialog = $data['id_dialog'];
		}

		$title = '';

		if ( isset( $data['title'] ) ){

			$title = $data['title'];
		}

		$classes = '';

		if ( isset( $data['classes'] ) ){

			$classes = $data['classes'];
		}

		$this->set('id_dialog', $id_dialog);

		$this->set('title', $title);	
		
		$this->set('classes', $classes);	
	}
}