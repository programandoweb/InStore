<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Apanel extends CI_Controller {

	/*DEBEMOS CREAR LLAMADOS A JS Y CSS CON LA FUNCION SET CSS O JS SE LA PASA
	UN ARRAY CON LOS ARCHIVOS QUE NECESITA SIN LA EXTENSIÓN*/

	var $util,$Apanel,$Menu;

	public function __construct(){
    parent::__construct();
		$this->util=new Util_model();
		$this->load->library('ControllerList');
		$this->Menu	=	$this->controllerlist->getControllers();
		$this->Breadcrumb	=	$this->uri->segment_array();
		$this->user	=	$this->session->userdata('User');

		$this->Apanel	=	true;
		if(ENVIRONMENT=='development'){
			$this->util->set_js(["bootstrap.min.js"]);
		}else{
			$this->util->set_js(["bootstrap.min.js"]);
		}
		if(empty($this->user) && $this->uri->segment(2)!='Login'){
			redirect(base_url("Autenticacion/login"));	return;
		}
  }

	public function index(){
		$this->util->set_title("Apanel - ".SEO_TITLE);
		if($this->user->tipo_usuario_id>0){
			$menu	=	menu($this->user->tipo_usuario_id,$this->Menu);
			$contador=0;
			$controlador="";
			$metodo="";
			//pre($this->user->tipo_usuario_id);
			foreach ($menu as $key => $value) {
				if($contador==0 && $key!="Biblioteca"){
					$controlador=$key;
					$metodo=reset($value);
					$contador++;
				}
			}
			$url	=	'Apanel/'.$controlador.'?m='.$metodo;
			redirect($url);
		}
		$this->util->view("Apanel/Inicio");
	}

	public function error(){
		$this->util->set_title("Apanel - ".SEO_TITLE);
		$this->load->view('welcome_message');
	}

}
