<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Reportes extends CI_Controller{

	var $util,$user,$ModuloActivo,$path,$listar,$Usuarios,$Breadcrumb,$Uri_Last,$Listado;

	public function __construct(){
    parent::__construct();
		$this->util 		= 	new Util_model();
		$this->Breadcrumb 	=	$this->uri->segment_array();
		$this->Uri_Last		=	$this->uri->segment($this->uri->total_rsegments());
		$this->user			=	$this->session->userdata('User');
		$this->ModuloActivo	=	'Usuarios';
		$this->Path			=	PATH_VIEW.'/Template/'.$this->ModuloActivo;
		$this->listar		=	new stdClass();

		if(empty($this->user)){
			redirect(base_url("Main"));	return;
		}
		$this->load->model("Usuarios_model");
		$this->Usuarios	= 	new Usuarios_model();
		chequea_session($this->user);
  }

	public function Index(){
		$clase	=	$this->uri->segment(2)."_model";
		$metodo	=	get("m");
		if(method_exists($this,$metodo)){
			$this->$metodo();
		}
	}

}
?>
