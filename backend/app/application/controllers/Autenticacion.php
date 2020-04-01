<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Autenticacion extends CI_Controller {

	/*DEBEMOS CREAR LLAMADOS A JS Y CSS CON LA FUNCION SET CSS O JS SE LA PASA
	UN ARRAY CON LOS ARCHIVOS QUE NECESITA SIN LA EXTENSIÓN*/

	public function __construct(){
		parent::__construct();
		$this->load->library('CI_Minifier');
		$this->util 		= 	new Util_model();
  }

	public function index(){
		if(empty($this->user) && $this->uri->segment(2)!='Login'){
			redirect(base_url("Autenticacion/login"));	return;
		}
	}

	public function login(){
		if(ENVIRONMENT=='development'){
			$this->util->set_js(["popper.min.js","bootstrap.min.js","jquery.pgrw.js"]);
		}else{
			$this->util->set_js(["popper.min.js","bootstrap.min.js","jquery.pgrw.min.js"]);
		}
		$this->util->view("Autenticacion/login");
	}

	public function salir(){
		@destruye_session($this->user);
		$this->session->unset_userdata('User');
		$this->session->sess_destroy();
		if ($this->input->is_ajax_request()) {
			$this->Response 		=			array(	"message"	=>	"Cierre de sesión satisfactorio, será redirigido",
																				"code"		=>	"200");
			echo answers_json($this->Response);
		}else{
			redirect(base_url());
		}
	}

	public function error(){
		$this->util->view("Error_NoView");
	}

	public function Recover(){
		$this->util->view("Recover");
	}

	public function Register(){
		$this->util->view("Register");
	}

	public function Respuesta(){
		$this->util->view("Autenticacion/Respuesta");
	}

	public function registertoken(){
		if(method_exists($this->Autenticacion,"set_user_by_token") && $this->uri->segment(3)){
			$this->Autenticacion->set_user_by_token($this->uri->segment(3));
			redirect(base_url("autenticacion/registertoken"));
			return;
		}
		//print_r($this->session);
		$this->util->set_title("Register - ".SEO_TITLE);
		$this->load->view('Template/Header');
		$this->load->view('Template/Flash');
		$this->load->view('Template/Autenticacion/ActiveUser');
		$this->load->view('Template/Footer');
	}

	public function recovertoken(){
		if(post()){
			$set	=	$this->Autenticacion->setrecovertoken(post());
			if ($this->input->is_ajax_request()) {
				if($set){
					$this->Response 		=			array(	"message"	=>	"Los datos han sido guardados correctamente",
																						"code"		=>	"200");
				}else{
					$this->Response 		=			array(	"message"	=>	"Lo siento, presentamos un problema y no pudimos guardar los datos",
																						"code"		=>	"203");
				}
				echo answers_json($this->Response);
			}
			return;
		}
		$data	=	user_x_token($this->uri->segment(3));
		if(empty($data)){

		}else{

			$this->util->set_title("Restablecer Contraseña - ".SEO_TITLE);
			$this->load->view('Template/Header');
			$this->load->view('Template/Autenticacion/RestablecerContrasena',array("row",$data));
			$this->load->view('Template/Footer');
		}
	}

	public function recoverpass(){
		$this->load->model('Autenticacion_model');
		$this->Autenticacion 	= 	new Autenticacion_model();
		if($this->Autenticacion){
			if(method_exists($this->Autenticacion,"get_user_by_email")){
				$user			=	$this->Autenticacion->get_user_by_email(post());
				if(empty($user)){
					echo json_encode(array(	"message_iframe"	=>	"Error, Usuario no encontrado",
																				"code"		=>	"203"));
					return;
				}
				$md5	=	md5(date("Y-m-d H:i:s"));
				$rand	=	rand(100000,999999);
				$user->token	=	$codigo_aleatorio_6_numeros		=	$rand.'::'.$md5;
				$this->db->where('user_id', $user->user_id)->update("usuarios", array("token"=>$user->token));

				$var		=	array(
									"view"		=>	"recover",
									"data"		=>	array(		"userName" =>	$user->primer_nombre.' '.$user->segundo_nombre.' '.$user->primer_apellido.' '.$user->segundo_apellido,
																					"href" =>	base_url("autenticacion/recovertoken/".$user->token),
																					"codigo_aleatorio_6_numeros"	=>	$rand
																					));
				$mensaje	=	set_template_mail($var);
				//print($mensaje);return;
				if($mensaje){
					$var		=	array(
										"recipient"	=>	$user->email,
										"subject"		=>	"Recuperación de contraseña en ".SEO_NAME,
										"body"			=>	$mensaje
									);
					$sendmail	=	send_mail($var);
					if(!$sendmail['error']){
						$this->Response 		=			array(	"message_iframe"	=>	"Envío de reinicio de clave exitoso, revise su correo electrónico",
																							"code"	=>	"200",
																							"parent" =>  true);
					}else{
						$this->Response 		=			array(	"message_iframe"	=>	"Error, no se puedo reiniciar la clave, reintente más tarde",
																							"code"	=>	"203");
					}

				}else{
					$this->Response 		=			array(		"message_iframe"	=>	"Error, no se puedo reiniciar la clave, reintente más tarde",
																							"code"	=>	"203");
				}
			}else{
				$this->Response 		=			array(	"message_iframe"	=>	"Error, método no encontrado",
																					"code"	=>	"203");
			}
		}else{
			$this->Response 		=			array(	"message_iframe"	=>	"Error, Clase no encontrado",
																				"code"	=>	"203");
		}
		$this->util->set_title("Restablecer Contraseña - ".SEO_TITLE);
		//$this->load->view('Template/Autenticacion/',array("response",$this->Response));
		redirect(base_url("Autenticacion/Respuesta/".$md5));
	}
}
