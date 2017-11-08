<?php
App::uses('Controller', 'Controller');

class AppController extends Controller
{
	public $helpers		= array('Session', 'Html', 'Form', 'Js', 'Paginator');

	public $components	= array(
		'Session',
		'Auth'		=> array(
			'loginAction'		=> array('controller' => 'usuarios', 'action' => 'login', 'admin' => true),
			'loginRedirect'		=> '/',
			'logoutRedirect'	=> '/login',
			'authError'			=> 'No tienes permisos para entrar a esta sección.',
			'authenticate'		=> array(
				'Form'				=> array(
					'userModel'			=> 'Usuario',
					
					'contain'   		=> array( 
						'ModuloDelUsuario', 
						'PerfilDelUsuario', 
						'MenuDelUsuario' => array(

							'Submenu'
						),
						'Cliente'),
					
					'scope'				=> array( 'Usuario.vigente' => 1 ),

					'fields'			=> array(
						'username'			=> 'username',
						'password'			=> 'password'
					)
				)
			)
		),
		'DebugKit.Toolbar',
		'RequestHandler'
	);

	public $uses = array( 'Usuario');

	public function beforeFilter()
	{
		/**
		 * Layout administracion y permisos publicos
		 */
		AuthComponent::$sessionKey		= 'Auth.Usuario';

		if ( ! empty($this->request->params['admin']) ) {

			$this->layoutPath				= 'backend';
		}
		else {

			$this->Auth->allow('login');
		}

		$version = '?v=' . APP_VER;

		$this->set( 'version', $version );

		$usuariologeado = $this->Session->read('Auth.Usuario');

		if ( $usuariologeado ){

			$modulos = $usuariologeado['ModuloDelUsuario'];
			$permisos = $usuariologeado['PerfilDelUsuario'];
			$menus = $usuariologeado['MenuDelUsuario'];

			$dato = array(); 
			$primerSlug = "";
			$moduloActual = array();
			$menuActual = array();

			if ( !empty( $modulos ) ){

				$primerSlug = $this->params['controller'];

				$segundoSlug = str_replace( "admin_", "", $this->params['action']);

				foreach ($modulos as $module) {

					if ( $module['url'] == $primerSlug ){

						$moduloActual = $module['id'];

						if ( isset( $moduloActual ) ){

							foreach ($menus as $item) {

								if($item['acc_modulos_id']==$moduloActual){

									$menuActual[] = $item;
								}
								/*
								if ( $item['url'] == $segundoSlug ){
									$dato = $menuActual = $item;
								}
								*/
							}
						}

					}
				}
			}


			$acc_menus_acc_modulos_id = $usuariologeado['MenuDelUsuario'];

			array_walk($acc_menus_acc_modulos_id, function( &$item, $index ){

				$item = $item['AccMenuperfil']['acc_menus_acc_modulos_id'];
			});

			$this ->set('acc_menus_acc_modulos_id', $acc_menus_acc_modulos_id);

			$this ->set('dato', $primerSlug);

			$this->set( 'usuariologeado', $usuariologeado );

			$this->set( 'modulos', $modulos );

			$this->set( 'moduloActual', $moduloActual );

			$this->set( 'menuActual', $menuActual );

			$this->set( 'permisos', $permisos);			
		}

		/**
		 * Cookies IE
		 */
		header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
	}
}