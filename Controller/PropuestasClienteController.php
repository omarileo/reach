<?php
App::uses('AppController', 'Controller');
class PropuestasClienteController extends AppController
{
	public $components = array('Flash');

	public $uses = array('Cliente');

	public $paginate = array(
		//'limit' 	=> 10,
		'contain' 	=> array(	'descripcionorigen',
								'descripcionarea',
								'descripcioncatalogo',
								'descripcioncliente',
								'descripcionusuario',
								'descripcionestado',
								'descripcioncontacto'),
		'order'		=> array('Propuesta.id'	=>	'desc')
	);

	

	public function admin_busquedaenpropuesta()
	{
		//prx($this->request->data);
		if($this->request->data)
		{
			$desdebuscado = $this->request->data["desdebuscado"];
			$this->Session->write('desdebuscado',$desdebuscado);	
			
			$hastabuscado = $this->request->data["hastabuscado"];
			$this->Session->write('hastabuscado',$hastabuscado);	
			
			$clientesbuscado = $this->request->data["clientesbuscado"];
			$idclientebuscado = $this->request->data["idclientebuscado"];
			$this->Session->write('clientesbuscado',$clientesbuscado);
			$this->Session->write('idclientebuscado',$idclientebuscado);
			
			$contactosbuscado = $this->request->data["contactosbuscado"];
			$this->Session->write('contactosbuscado',$contactosbuscado);	
			
			$estadosbuscado = $this->request->data["estadosbuscado"];
			$this->Session->write('estadosbuscado',$estadosbuscado);	
		} 
		//
		return $this->redirect(array('controller' => 'PropuestasCliente','action' => 'index'));
	}

	public function admin_index(){
		//prx($this->Session->read('Auth.Usuario'));

		$clientes=$this->Session->read('Auth.Usuario');
		$idCliente = $clientes['Cliente']['id'];

		$this->loadModel('Propuesta');
		$query = $this->Propuesta;
		/*$this->set('propuestas',$this->Propuesta->find('all',array(
															'conditions'=> array('descripcioncliente.id' => $idCliente),
															'contain'	=> array(
																			'descripcioncliente',
																			'descripcioncontacto',
																			'descripcionrubro',
																			'descripcionsubrubro',
																			'descripcionarea',
																			'descripcionorigen',
																			'descripcioncatalogo',
																			'descripcionestado'
																			)
															)));*/


		$this->loadModel('Estado');
		$this->set('estados',$this->Estado->find('all',array('conditions'=> array('Estado.id in' => array(3,9,10,11)))));
		//$this->set('estados',$this->Estado->find('all'));

		//$query = $Propuesta;

		$desdebuscado = $this->Session->read('desdebuscado');
		$hastabuscado = $this->Session->read('hastabuscado');

		if (isset($desdebuscado)) {
			$fechabuscado = new DateTime($desdebuscado);
			$desdebuscado = $fechabuscado->format('Y-m-d H:i:s');
		} else{
			$fechadesde = new DateTime($desdebuscado);
			$desdebuscado = $fechadesde->format('2000-01-01 00:00:00');
		}
		if (isset($hastabuscado)) {
			$fechahasta = new DateTime($hastabuscado);
			$hastabuscado = $fechahasta->format('Y-m-d 23:59:59');
		} else{
			$hastabuscado = date('Y-m-d 23:59:59');

		}

		$soloCliente = ' and descripcioncliente.id='.$idCliente;
		$excluirEliminadas = " and Propuesta.erp_estados_id != 8";

		$condicionfechas = "Propuesta.fecha BETWEEN CAST('".$desdebuscado."' AS DATETIME) AND CAST('".$hastabuscado."' AS DATETIME)";
		//echo $condicionfechas;exit();
		
		$condicioncontacto= true;
		
		
		//$condicionestado= true;
		
		if($this->Session->read('estadosbuscado')&&trim($this->Session->read('estadosbuscado')!=='')){
			$condicionestado = ('descripcionestado.id like ("%'.$this->Session->read('estadosbuscado').'%")');
		}else{
			$condicionestado = ' and descripcionestado.id in (3,9,10,11)';
		}
		//prx($condicionestado);



		if(	$this->Session->read('estadosbuscado')&&trim($this->Session->read('estadosbuscado')!==''))
		{
			$condicion = $condicioncontacto. ' and '. $condicionestado . ' and ' . $condicionfechas;
			$this->set('propuestas', $this->paginate($query,$condicion.$excluirEliminadas.$soloCliente));
		} else {
			$this->set('propuestas', $this->paginate($query,$condicionfechas.$excluirEliminadas.$soloCliente.$condicionestado));
		}


	}

	public function admin_guardar(){
		$this->Propuesta->set($this->request->data['Propuesta']);
		
		if( $this->request->is('post') ){
			$propuesta  = $this->request->data['Propuesta'];

			$fecha1 = new DateTime($propuesta['fecha']);
			$fecha = $fecha1->format('Y-m-d H:i:s');
			$vencimiento = new DateTime($propuesta['vencimiento']);
			$propuesta['fecha'] = $fecha;
			$propuesta['vencimiento'] = $vencimiento->format('Y-m-d H:i:s');
			$propuesta['erp_estados_id'] = 1;
			$this->loadModel('Propuesta');
			if($this->Propuesta->save($propuesta)){
				$id = $this->Propuesta->getLastInsertID();
				$largo = $this->request->data['guardar'];
				if($this->request->data['Propuestadetalle'])
				{
					$propuestaDetalle = $this->request->data['Propuestadetalle'];
					for($i=1; $i<=$largo;$i++) {
						if(array_key_exists($i, $propuestaDetalle))
						{
							$propuestaDetalle[$i]['erp_propuestas_id']=$id;
							$propuestaDetalle[$i]['fecha']=$propuesta['fecha'];
							$propuestaDetalle[$i]['vigente']=true;	
						}
						
					}
					$this->loadModel('PropuestasDetalle');
					$this->PropuestasDetalle->saveAll($propuestaDetalle);
					return $this->redirect(array('controller' => 'propuestas', 'action' => 'editar', $id));
				}
			}else{
				$this->Flash->success('Error al crear la propuesta, favor intentelo nuevamente','default',array('class'=>'success'));
			}
		}
		return $this->redirect(array('action' => 'index'));
	}

	public function admin_ingreso(){

		$this->loadModel('Rubro');
		$rubros=$this->Rubro->find(	'all',
									array(	'contain' => array('Subrubros'),
											'conditions' => array('Rubro.vigente'	=>true)
								)

									);
		$this->loadModel('Area');
		$areas=$this->Area->find('all', array('conditions'=>array('Area.vigente' => true)));
		$this->loadModel('Origen');
		$origenes=$this->Origen->find('all', array('conditions'=>array('Origen.vigente' => true)));
		$this->loadModel('Catalogo');
		$catalogo=$this->Catalogo->find('all');
		$this->loadModel('Responsable');
		$responsables=$this->Responsable->find('all');

		$this->set('rubros', $rubros);
		$this->set('areas', $areas);
		$this->set('origenes', $origenes);
		$this->set('catalogo', $catalogo);
		$this->set('responsables', $responsables);
	}

	public function admin_buscarProductos()
	{
		$this->loadModel('Catalogo');
		if ($this->request->isAjax())
		//if (1)
		{
			$this->autoRender = false;
			$nombreProducto = $this->request->query('term');

			$results = $this->Catalogo->find('all', array(
											'contain'	=> array('Descripcionfamilia'),
											'conditions'=> array(
												'CONCAT(Catalogo.descripcion,Descripcionfamilia.nombre) LIKE ' => '%'.$nombreProducto.'%'),
											'recursive' => -1
											));
			$resultArr = array();
			foreach ($results as $result) {
				$resultArr[] = array(
						'label' => $result['Descripcionfamilia']['nombre'] . "/ " .$result['Catalogo']['descripcion'], 
						'value' => $result['Descripcionfamilia']['nombre'] . "/ " .$result['Catalogo']['descripcion'],
						'id' => $result['Catalogo']['id'], 
						'precio' => $result['Catalogo']['preciolista'],
						'duracion' => $result['Catalogo']['duracion']
						);
			}

			echo json_encode($resultArr);
		}
	}

	public function admin_propuestaPdf($id=null)
	{
		$this->loadModel('Propuesta');
		
		if(!$this->Propuesta->exists($id))
		{
			throw new NotFoundException("Ha ocurrido un error, la propuesta no fue encontrada");
		}
		
		$this->pdfConfig = array(
			'download' => true,
			'filename' => 'propuesta_' . $id . '.pdf'
		);
		$this->set('propuesta',$this->Propuesta->find('first',array(
															'conditions'=> array('Propuesta.id' => $id),
															'contain'	=> array(
																			'descripcioncliente',
																			'descripcioncontacto',
																			'descripcionrubro',
																			'descripcionsubrubro',
																			'descripcionarea',
																			'descripcionorigen',
																//			'descripciondetalle',
																			'descripcioncatalogo'
																			)
															)));

	}

	public function admin_ver($id=null)
	{	
		$this->loadModel('Propuesta');
		$propuesta = $this->Propuesta->find('first',
											array('conditions'=>array('Propuesta.id' => $id),
													'contain' => array(	'descripcioncliente',
																		'descripcioncontacto',
																		'descripcionrubro',
																		'descripcionsubrubro',
																		'descripcionarea',
																		'descripcionorigen',
																		'descripcioncatalogo',
																		'descripcionresponsable')));
		$this->set('propuesta',$propuesta);

		if ( $this->request->is('post') ){
				
			if(isset($this->request->data['referencia'])) {
				$this->Propuesta->id = $this->request->data['id'];
				$referencia = $this->request->data['referencia'];
				if ($this->Propuesta->saveField('referencia', $referencia)) {
					echo 1; exit;
				}
				
			}

			if (isset($this->request->data['horareal'])) {
				$this->loadModel('PropuestasDetalle');
				$this->PropuestasDetalle->id = $this->request->data['id'];
				$horareal = $this->request->data['horareal'];
				$this->PropuestasDetalle->saveField('horaReal', $horareal);
			}

			if ($this->request->data['eliminarDetalle']){
				$this->loadModel('PropuestasDetalle');
				$this->PropuestasDetalle->id = $this->request->data['id'];
				
				if ($this->PropuestasDetalle->saveField('vigente', 0)){
					echo 1;exit;
				}
			}
		}
	
	}

	public function admin_viewpdf($id=null) {
	    App::import('Vendor', 'Fpdf', array('file' => 'fpdf/fpdf.php')); 
	    $this->layout = 'pdf'; //this will use the pdf.ctp layout
	    $this->response->type('pdf');
	    $this->set('fpdf', new FPDF('P','mm','A4'));
	    $this->set('data', 'Hello, PDF world');

	    $this->render('pdf');

	  

	}


	// Roberto Contardo 24-10-2017 16:54

	public function admin_editar_estado(){

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

		if ( isset( $data['id_propuesta'] ) ){


			$this->loadModel('Estado');

			$estados = $this->Estado->find('all', array(

					'conditions' 	=> array(

						'Estado.vigente' 	=> 1
					)
				)
			);

			array_walk($estados, function(&$item){

				$item = $item['Estado'];
			});

			$this->set('estados', $estados);



			$this->loadModel('Analisis');

			$analisis = $this->Analisis->find('all', array(

					'fields' 	=> array(

						'id',
						'estado_id',
						'nombre',
					),
					'conditions' 	=> array(

						'Analisis.vigente' 	=> 1
					)
				)
			);
			
			array_walk($analisis, function(&$item){

				$item = $item['Analisis'];
			});

			$this->set('analisis', $analisis);



			$propuesta = $this->Propuesta->find('first', array(

					'conditions' 	=> array(

						'Propuesta.id' 	=> $data['id_propuesta']
					),
					'contain' 	=> array(

						'descripcionestado'
					)
				)
			);

			$this->set('propuesta', $propuesta);

		}
	}

	public function admin_eliminar($id_propuesta = null){

		$this->Propuesta->id = $id_propuesta;

		if ( ! $this->Propuesta->exists() )
		{
			$this->Session->setFlash('Propuesta inválida.', null, array(), 'danger');
			$this->redirect(array('action' => 'index'));
		}

		if ( $this->Propuesta->saveField('erp_estados_id', 8) )
		{
			$this->Session->setFlash('Propuesta eliminada correctamente.', null, array(), 'success');
			
			$this->redirect(array('action' => 'index'));
		}

		$this->Session->setFlash('Error al eliminar la Propuesta. Por favor intenta nuevamente.', null, array(), 'danger');

		$this->redirect(array('action' => 'index'));
	}


	// Roberot Contardo 31-10-2017 18:35

	public function admin_actualizar_estado(){

		$data = $this->request->data;

		$result = array(

			"status"					=> 'danger',
			"message"					=> 'Ha ocurrido un error.'
		);

		if ( isset( $data['id_propuesta'] ) && isset( $data['id_estado'] ) ){

			try {

				$this->Propuesta->id = $data['id_propuesta'];

				$this->Propuesta->saveField('erp_estados_id', $data['id_estado']);

				$result = array(

					"status"					=> 'success',
					"message"					=> 'Registro guardado exitosamente.'
				);			
			}
			catch (PDOException $e) {

				$result['message'] = $e->errorInfo[2];
			}			
		}

		echo json_encode($result);
		exit;		
	}

	// Roberto Contardo 03-11-2017 10:15

	public function admin_agregar_cliente(){

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


	// Roberto Contardo. Fin Edición.




	public function admin_exportarExcel(){

		
		

		App::import('Vendor', 'PHPExcel/Classes/PHPExcel');
		App::import('Helper', 'TimeHelper');
		App::uses('CakeTime', 'Utility');
		$this->loadModel('Propuesta');
		$clientes=$this->Session->read('Auth.Usuario');
		$idCliente = $clientes['Cliente']['id'];

		try{

				// Create new PHPExcel object
				echo date('H:i:s') . " Create new PHPExcel object\n";
				$objPHPExcel = new PHPExcel();

				// Set properties
				echo date('H:i:s') . " Set properties\n";
				$objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
				$objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
				$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
				$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
				$objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");


				// Add some data
				echo date('H:i:s') . " Add some data\n";
				$objPHPExcel->setActiveSheetIndex(0);

					/*$this->loadModel('Estado');
					$this->set('estados',$this->Estado->find('all'));
					$query = $this->Propuesta;*/

					if($this->request->params["named"]["export_data"]) $desdebuscado = $this->request->params["named"]["export_data"]["desdebuscado"];
					$this->Session->write('desdebuscado',$desdebuscado);
					
					if($this->request->params["named"]["export_data"]) $hastabuscado = $this->request->params["named"]["export_data"]["hastabuscado"];
					$this->Session->write('hastabuscado',$hastabuscado);

					/*if($this->request->params["named"]["export_data"]) $clientesbuscado = $this->request->params["named"]["export_data"]["clientesbuscado"];
					$this->Session->write('clientesbuscado',$clientesbuscado);

					if($this->request->params["named"]["export_data"]) $contactosbuscado = $this->request->params["named"]["export_data"]["contactosbuscado"];
					$this->Session->write('contactosbuscado',$contactosbuscado);*/

					if($this->request->params["named"]["export_data"]) $estadosbuscado = $this->request->params["named"]["export_data"]["estadosbuscado"];
					$this->Session->write('estadosbuscado',$estadosbuscado);

					if (isset($desdebuscado)) {
						$fechabuscado = new DateTime($desdebuscado);
						$desdebuscado = $fechabuscado->format('Y-m-d H:i:s');
					} else{
						$fechadesde = new DateTime($desdebuscado);
						$desdebuscado = $fechadesde->format('2000-01-01 00:00:00');
					}
					if (isset($hastabuscado)) {
						$fechahasta = new DateTime($hastabuscado);
						$hastabuscado = $fechahasta->format('Y-m-d 23:59:59');
					} else{
						$hastabuscado = date('Y-m-d H:i:s');
					}

					$soloCliente = ' and descripcioncliente.id='.$idCliente;

					$excluirEliminadas = " and Propuesta.erp_estados_id != 8";

					$condicionfechas = "Propuesta.fecha BETWEEN CAST('".$desdebuscado."' AS DATETIME) AND CAST('".$hastabuscado."' AS DATETIME)";

					$condicioncliente= true;
					//if($this->Session->read('clientesbuscado')&&trim($this->Session->read('clientesbuscado')!==''))
					/*if($clientesbuscado!=='')
					{
						//$clientesbuscado	= $clientesbuscado;
						$condicioncliente = ('(CONCAT(descripcioncliente.nombres, " ",
													descripcioncliente.paterno, " ",
													descripcioncliente.materno) like ("%'.$clientesbuscado.'%"))'
											);
					}*/
					$condicioncontacto= true;
					/*if(trim($contactosbuscado)!=='')
						$condicioncontacto = ('descripcioncontacto.nombre like ("%'.$contactosbuscado.'%")');*/
						$condicionestado= '1=1';
					if($estadosbuscado && trim($estadosbuscado)!=='')
						$condicionestado = ('descripcionestado.id like ("%'.$estadosbuscado.'%")');

					if(	/*$clientesbuscado&&trim($clientesbuscado!=='') ||
						$contactosbuscado&&trim($contactosbuscado!=='') ||*/
						$estadosbuscado&&trim($estadosbuscado!==''))
					{
						$condicion = /*$query.' '.*/$condicioncliente. ' and '. $condicioncontacto. ' and '. $condicionestado . ' and ' . $condicionfechas.$excluirEliminadas.$soloCliente;
						//$this->set('propuestas', $this->paginate($query,$condicion));
					} else {
						//$this->set('propuestas', $this->paginate($query,$condicionfechas));
						$condicion = /*$query.' '.*/$condicionfechas.$excluirEliminadas.$soloCliente;
					}
				

					//echo 'CONDICION : '.$condicion;
				//exit;

					

					$results = $this->Propuesta->find('all', array(
													//'conditions'=> array('Contacto.nombre LIKE ' => '%'.$nombreContacto.'%'),
													'conditions'=> array($condicion),
													'contain'	=> array('descripcioncliente','descripcionorigen','descripcionarea','descripcionestado','descripcioncatalogo','descripcioncontacto'),
													'recursive' => -1
													));

					//prx($results);
					//exit;


					$objPHPExcel->getActiveSheet()->SetCellValue('A1','Nro.');
					$objPHPExcel->getActiveSheet()->SetCellValue('B1','F.Emis.');
					$objPHPExcel->getActiveSheet()->SetCellValue('C1','F.Venc.');
					$objPHPExcel->getActiveSheet()->SetCellValue('D1','H.Pres.');
					$objPHPExcel->getActiveSheet()->SetCellValue('E1','H.Real');
					$objPHPExcel->getActiveSheet()->SetCellValue('F1','Cliente');
					$objPHPExcel->getActiveSheet()->SetCellValue('G1','Origen');
					$objPHPExcel->getActiveSheet()->SetCellValue('H1','Area');
					$objPHPExcel->getActiveSheet()->SetCellValue('I1','Estado');

					$rowActual = 1;
					foreach ($results as $result) {
						$rowActual = $rowActual+1;
						$objPHPExcel->getActiveSheet()->SetCellValue('A'.($rowActual),$result['Propuesta']['id'])->getColumnDimension('A')->setAutoSize(true);
						$objPHPExcel->getActiveSheet()->SetCellValue('B'.($rowActual),CakeTime::format($result['Propuesta']['created'], '%d-%m-%Y'))->getColumnDimension('B')->setAutoSize(true);
						$objPHPExcel->getActiveSheet()->SetCellValue('C'.($rowActual),CakeTime::format($result['Propuesta']['vencimiento'], '%d-%m-%Y'))->getColumnDimension('C')->setAutoSize(true);
						$duracion= 0;
						$horaReal= 0;
						for($i=0;$i< count($result['descripcioncatalogo']);$i++){
							//prx($result['descripcioncatalogo'][$i]['duracion']);
							$duracion = $duracion + $result['descripcioncatalogo'][$i]['duracion'];
							$horaReal = $horaReal + $result['descripcioncatalogo'][$i]['ErpPropuestasdetall']['horaReal'];
							//exit;
						}
						$objPHPExcel->getActiveSheet()->SetCellValue('D'.($rowActual),$duracion)->getColumnDimension('D')->setAutoSize(true);
						$objPHPExcel->getActiveSheet()->SetCellValue('E'.($rowActual),$horaReal)->getColumnDimension('E')->setAutoSize(true);
						$objPHPExcel->getActiveSheet()->SetCellValue('F'.($rowActual),$result['descripcioncliente']['nombres'].' '.$result['descripcioncliente']['paterno'].' '.$result['descripcioncliente']['materno'])->getColumnDimension('F')->setAutoSize(true);
						$objPHPExcel->getActiveSheet()->SetCellValue('G'.($rowActual),$result['descripcionorigen']['descripcion'])->getColumnDimension('G')->setAutoSize(true);
						$objPHPExcel->getActiveSheet()->SetCellValue('H'.($rowActual),$result['descripcionarea']['descripcion'])->getColumnDimension('H')->setAutoSize(true);
						$objPHPExcel->getActiveSheet()->SetCellValue('I'.($rowActual),$result['descripcionestado']['estado'])->getColumnDimension('I')->setAutoSize(true);
						



						
						
					}
					
					

				// Rename sheet
				echo date('H:i:s') . " Rename sheet\n";
				$objPHPExcel->getActiveSheet()->setTitle('Simple');


				// Save Excel 2007 file
				echo date('H:i:s') . " Write to Excel2007 format\n";
				//$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
				//$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
				$filePath = '/var/www/html/crm/reach/webroot/tmp/prueba.xlsx';
				//$objWriter->save($filePath);

			    
			    $objWriter->save($filePath);

			    $this->redirect('http://192.168.1.15/crm/reach/tmp/prueba.xlsx');

		}catch(Exception $e){
  			echo $e->__toString();
		}
       
			

				
	}


}