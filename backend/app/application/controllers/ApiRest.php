<?php
/*
	PENDIENTES:
	Crear una respuesta de dos sesiones abiertas.
*/
defined('BASEPATH') OR exit('No direct script access allowed');

class ApiRest extends CI_Controller {

	var $html;

	public function __construct(){
		parent::__construct();
		$simple_html_dom	=	PATH_LIBRARIES. '/simple_html_dom.php';
		include_once($simple_html_dom);
		$this->html		=		new simple_html_dom();
  }

	function index($mensaje="Ok",$array=""){
		echo json_response($array,$mensaje, $code = 200);
	}

	public function apirequest(){
		if(		PUBLIC_KEY ==	post("PUBLIC_KEY") ||  PRIVATE_KEY ==	post("token") || get("testing")=="json") {
			$this->exec();
		}else{
			$this->index($this->uri->segment(3).' Sin Autorización, La clave pública está vencida ');
		}
	}

	private function  exec(){
    /*PARÁMETROS A PASARLE A REST*/
		$clase	=	get("modulo")."_model";
		$metodo	=	get("m");
    $view 	= PATH_MODEL.'/'.$clase.'.php';
		/*VERIFICO SI EXISTE EL MODELO*/
    if(file_exists($view)){
			/*IMPORTO EL MODELO*/
			$this->load->model($clase);
			/*LLENO UNA VARIABLE CON LA CLASE RESPECTIVA*/
			$clase=new $clase;
			/*CHEQUEO SI EXISTE EL MODELO*/
			// pre($metodo,false) ;
			// pre($clase) ;
			if(method_exists($clase,$metodo)){
				$rows	=	$clase->$metodo();
				switch(get("formato")){
					/*
						POR AHORA ESTE ES EL FORMATO DE RESPUESTA,
						LO DEJO EN UN SWITCH POR SI LUEGO NECESITAMOS OTRO
						FORMATO DE RESPUESTA
					*/
					case 'none':
						return;
					break;
					case 'redirect':
						if(post("redirect")!=''){
							redirect(post("redirect"));
						}else{
							redirect($this->agent->referrer());
						}
					break;
					case 'json':
					default:
						echo json_response($clase->response(),$clase->message,200,@$clase->callback);
					break;
				}
			}else{
				$this->index($this->uri->segment(3).' Sin Autorización ',["data"=>[],"recordsTotal"=>0,"recordsFiltered"=>0]);
			}
		}
	}

	function get(){
		if($metodo	=	traer_metodo($this)){
			$this->$metodo();
		}else{
			$this->index($this->uri->segment(3).' No existe ');
		}
	}

	function post(){
		if($metodo	=	traer_metodo($this)){
			$this->$metodo();
		}else{
			$this->index($this->uri->segment(3).' No existe ');
		}
	}

	function pull(){
		if($metodo	=	traer_metodo($this)){
			$this->$metodo();
		}else{
			$this->index($this->uri->segment(3).' No existe ');
		}
	}

	function push(){
		if($metodo	=	traer_metodo($this)){
			$this->$metodo();
		}else{
			$this->index($this->uri->segment(3).' No existe ');
		}
	}

	function token(){
		echo json_response(array("token"=>genera_token()),"Token", $code = 200);
	}

	function login(){
		$this->load->model("User_model");
		$this->User	= new User_model();
		$this->User->login();
		echo json_response($this->User->response(),$this->User->message, $code = 200);
	}

	function usuarios(){
		$this->load->model("User_model");
		$yo	=	quien_hace_la_peticion(post("token"));
		if(empty($yo)){
			$this->index($this->uri->segment(3).' No existe ');
			return ;
		}
		if($yo->tipo_usuario_id>1){
			$this->index($this->uri->segment(3).' No existe ');
			return;
		}
		$this->User	= new User_model();
		$this->User->usuarios(post());
		echo json_response($this->User->response(),$this->User->message, $code = 200);
	}

}
?>
