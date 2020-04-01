<?php
/*
	DESARROLLO Y PROGRAMACIÓN
	PROGRAMANDOWEB.NET
	LCDO. JORGE MENDEZ
	info@programandoweb.net
*/
defined('BASEPATH') OR exit('No direct script access allowed');

class Biblioteca extends CI_Controller {

	var $util,$user,$ModuloActivo,$path,$listar,$Operavita,$Breadcrumb,$Uri_Last,$View,$Menu,$Configuracion;

	public function __construct(){
    parent::__construct();
		$this->load->library('ControllerList');
		$this->Menu				=		$this->controllerlist->getControllers();
		$this->util 			=	 	new Util_model();
		$this->Breadcrumb =		NULL;
		$this->user				=		$this->session->userdata('User');
		$this->ModuloActivo	=	$this->router->fetch_class();
		$this->listar		=	new stdClass();
		$this->View			=	$this->uri->segment(2).(1)?"":"";
		$this->Apanel		=	true;
		if(empty($this->user)){
			redirect(base_url("Main"));	return;
		}
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
		$this->load->model("Operatividad_model");
		$this->Operatividad		=	 	new Operatividad_model();

		/*
			EN ESTE MÓDULO DEBO ESTAR LOGUEADO CON UNA institucion_id
			DE LO CONTRARIO ME LLEVA AL MÓDULO PARA SELECCIONAR UN PERFIL
		*/
		if( ($this->user->tipo_usuario_id==0 || $this->user->tipo_usuario_id>1) && ($this->user->institucion_id=='' || $this->user->institucion_id==0)){
			redirect(base_url("ApiRest/post?modulo=Usuarios&m=setSessionTemporal&formato=none&controller=Usuarios&me=Gestion_usuarios&view=&id=&message=Debes seleccionar un perfil para asociar los registros con una empresa."));
		}
  }

	public function Index(){
		$clase	=	$this->uri->segment(2)."_model";
		$metodo	=	get("m");
		if(method_exists($this,$metodo)){
			$this->$metodo();
		}
	}

	public function Principal(){
		$this->util->skipBtnAdd();
		$this->util->exec();
	}

	public function Subir_Libro_o_Texto(){
		$this->util->skipBtnAdd();
		$this->util->exec();
	}

}

?>
