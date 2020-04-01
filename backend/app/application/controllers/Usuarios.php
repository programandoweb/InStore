<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Usuarios extends CI_Controller{

	var $util,$user,$ModuloActivo,$path,$listar,$Usuarios,$Breadcrumb,$Uri_Last,$Listado;

	public function __construct(){
    parent::__construct();
		$this->load->library('ControllerList');
		$this->Menu			=	$this->controllerlist->getControllers();
		$this->util 		= 	new Util_model();
		$this->Breadcrumb 	=	$this->uri->segment_array();
		$this->Uri_Last		=	$this->uri->segment($this->uri->total_rsegments());
		$this->user			=	$this->session->userdata('User');
		$this->ModuloActivo	=	'Usuarios';
		$this->Path			=	PATH_VIEW.'/Template/'.$this->ModuloActivo;
		$this->listar		=	new stdClass();
		$this->Apanel		=	true;

		if(empty($this->user)){
			redirect(base_url("Main"));	return;
		}
		$this->load->model("Usuarios_model");
		$this->Usuarios	= 	new Usuarios_model();
		chequea_session($this->user);
		if(ENVIRONMENT=='development'){
			$this->util->set_js(["bootstrap.min.js"]);
			$this->util->set_thirdParty([	"js"=>[	"DataTables/datatables.min",

																					],
																		"css"=>["DataTables/datatables.min",
																						"DataTables/DataTables-1.10.18/css/dataTables.bootstrap4.min",
																						]
																	]);
		}else{
			$this->util->set_thirdParty([	"js"=>[	"DataTables/datatables.min",

																					],
																		"css"=>["DataTables/datatables.min",
																						"DataTables/DataTables-1.10.18/css/dataTables.bootstrap4.min",
																						]
																	]);
		}
  }

	public function Index(){
		$clase	=	$this->uri->segment(2)."_model";
		$metodo	=	get("m");
		if(method_exists($this,$metodo)){
			$this->$metodo();
		}
	}

	public function exc_setSesionUsuario(){
		$usuarios=usuarios(get("id"));
		if(isset($usuarios->institucion_id) && $usuarios->institucion_id>0 && $this->user->tipo_usuario_id==0){
			$set	=	new stdClass;
			foreach ($this->user as $key => $value) {
				$set->$key	=	$value;
			}
			foreach ($usuarios as $key => $value) {
				$set->$key	=	$value;
			}
			$usuarios_temp	=	usuarios($this->user->usuario_id);
			$this->session->set_userdata(array('UserTemp'=>$usuarios_temp));
			$this->session->set_userdata(array('User'=>$set));
		}else {

			$set	=	new stdClass;
			foreach ($this->user as $key => $value) {
				$set->$key	=	$value;
			}
			foreach ($usuarios as $key => $value) {
				$set->$key	=	$value;
			}
			$this->session->unset_userdata('UserTemp');
			$this->session->set_userdata(array('User'=>$set));

		}
		redirect(base_url("Apanel"));
	}

	public function Gestion_usuarios(){
		if(get("view")=='add_Gestion_usuarios'){
			$this->util->set_thirdParty([	"js"=>[	"colorlib-wizard-1/js/jquery.steps",
																						"colorlib-wizard-1/js/main",],
																		"css"=>["colorlib-wizard-1/css/style",]]);
		}
		if( $this->user->tipo_usuario_id==0){
			$this->campos_listado=	[	"usuario_id"=>"ID",
																"nombres"=>"Usuario",
																"tipo"=>"Tipo de Usuario",
																"edit_setuser"=>"Acción"];
		}else{
			$this->campos_listado=	[	"usuario_id"=>"ID",
																"nombres"=>"Usuario",
																"tipo"=>"Tipo de Usuario",
																"edit"=>"Acción"];
		}

		$this->util->exec();
	}

	public function Gestion_instituciones(){
		if(get("view")=='add_Gestion_usuarios'){
			$this->util->set_thirdParty([	"js"=>[	"colorlib-wizard-1/js/jquery.steps",
																						"colorlib-wizard-1/js/main",],
																		"css"=>["colorlib-wizard-1/css/style",]]);
		}

		$this->campos_listado=	[	"institucion_id"=>"ID",
															"nombre_comercial"=>"Institución",
															"edit"=>"Acción"];


		$this->util->exec();
	}

	public function Roles_usuarios(){
		$this->skipAdd=true;
		$this->campos_listado=[	"tipo_usuario_id"=>"ID",
														"tipo"=>"Tipo de Usuario",
														"estatus"=>"Estado",
														"edit"=>"Acción"];
		$this->util->exec();
	}

	public function exc_Tipo_de_Usuario(){
		$this->util->exec();
	}

}
?>
