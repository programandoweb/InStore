<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Secretaria extends CI_Controller{

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

	public function Gestion_instituciones(){
		redirect("Apanel/Secretaria?m=exc_Gestion_instituciones&view=Panel&back=history&tab=false&sv=Solicitudes");
	}

	public function exc_Gestion_instituciones(){
		$this->util->exec();
	}

}
?>
