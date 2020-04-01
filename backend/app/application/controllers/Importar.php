<?php
/*
	DESARROLLO Y PROGRAMACIÓN
	PROGRAMANDOWEB.NET
	LCDO. JORGE MENDEZ
	info@programandoweb.net
*/
defined('BASEPATH') OR exit('No direct script access allowed');

class Importar extends CI_Controller {

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

  }

	public function Index(){
		$clase	=	$this->uri->segment(2)."_model";
		$metodo	=	get("m");
		if(method_exists($this,$metodo)){
			$this->$metodo();
		}
	}

	public function Relacionar(){
		return;
		$rows		=	$this->db->select('*')
													->from(DB_PREFIJO."usuarios t1")
													->where("tipo_usuario_id",2)
													->get()
													->result();
		foreach ($rows as $key => $value) {
			$row	=		$this->get($value->nombres);
			if (isset($row) && !empty($row)) {

				$rel	=		$this->rel($value->usuario_id,$row->usuario_id);
				if (empty($rel)) {
					$grados	=	$this->grados();
					$insert	=	[	"usuario_id"=>$value->usuario_id,
											"acudiente_id"=>$row->usuario_id,
											"institucion_id"=>5,
											"grado_escolar_token"=>$grados->token,
											"estatus"=>1,
										];
					$this->db->insert(DB_PREFIJO."rel_alumnos_instituciones",$insert);
					//pre($insert);
				}
			}
		}
	}
	private function get($q){
		return	$this->db->select('*')
													->from(DB_PREFIJO."usuarios t1")
													->where("tipo_usuario_id",5)
													->like("nombres",$q)
													->get()
													->row();
	}

	private function rel($usuario_id,$acudiente_id){
		return	$this->db->select('*')
													->from(DB_PREFIJO."rel_alumnos_instituciones t1")
													->where("usuario_id",$usuario_id)
													->where("acudiente_id",$acudiente_id)
													->get()
													->row();
	}

	private function grados(){
		return	$this->db->select('*')
													->from(DB_PREFIJO."op_grados_x_instituciones t1")
													->order_by('id', 'random')
													->get()
													->row();
	}

	public function Alumnos_de_excel(){
		exit;
		$simple_html_dom=PATH_LIBRARIES. '/simple_html_dom.php';
		if (file_exists($simple_html_dom)) {
			include_once($simple_html_dom);
		}
		$string	=	$this->load->view("Importar/alumnos",[],true);
		$html = str_get_html($string);
		foreach($html->find('td[class=ewRptGrpSummary1]') as $k=> $element){
			if (strstr($element->innertext, "Sumatoria por apellidos:")) {
				$nuevo_str				= 	str_replace("Sumatoria por apellidos: ","",$element->innertext);
				$pos1 						= 	stripos($nuevo_str, " (");
				$rest 						= 	substr($nuevo_str, 0 ,	$pos1);
				$identificacion 	= 	strtoupper(substr("R".token(),0,12));

				/*PARA ALUMNOS*/
				// $insert			=		[
				// 									"institucion_id"=>"5",
				// 									"parent_id"=>"5",
				// 									"tipo_usuario_id"=>2,
				// 									"identificacion"=>$identificacion,
				// 									"nombres"=>$rest,
				// 									"email"=>"no_email@gmail.com",
				// 									"login"=>$identificacion,
				// 									"password"=>md5($identificacion),
				// 									"telefono"=>"0000000000",
				// 									"fecha_ingreso"=>date("Y-m-d"),
				// 									"token"=>token(),
				// 									"ultimo_perfil_activo"=>"0",
				// 									"estatus"=>0,
				// 									];

				/*PARA ACUDIENTES*/
				$insert			=		[
													"institucion_id"=>"5",
													"parent_id"=>"5",
													"tipo_usuario_id"=>5,
													"identificacion"=>$identificacion,
													"nombres"=>"ACUDIENTE: ".$rest,
													"email"=>"no_email@gmail.com",
													"login"=>$identificacion,
													"password"=>md5($identificacion),
													"telefono"=>"0000000000",
													"fecha_ingreso"=>date("Y-m-d"),
													"token"=>token(),
													"ultimo_perfil_activo"=>"0",
													"estatus"=>0,
													];

				//$this->db->insert(DB_PREFIJO."usuarios",$insert);
				pre($insert,false);
			}
		}
	}

}

?>
