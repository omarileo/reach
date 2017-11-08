<?php
App::uses('AppController','Controller');
class FinanzasController extends AppController {
	public function admin_index(){
		$this->loadModel('Estado');
		$estados = $this->Estado->find('all',array('conditions' => array('id in' => array('9','10','11'))));
		$this->set('estados',$estados);
	}
}