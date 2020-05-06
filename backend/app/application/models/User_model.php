<?php
/*
	DESARROLLO Y PROGRAMACIÓN
	PROGRAMANDOWEB.NET
	LCDO. JORGE MENDEZ
	info@programandoweb.net
*/
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

	var $user,$result,$post,$get,$message;

	function __construct(){
    parent::__construct();
		$this->user			=		$this->session->userdata('User');
		$this->post			=		post();
		$this->result		=		[];
	}

	public function json(){
		 $string 		= file_get_contents("http://localhost/tienda/backend/paises.json");
		 $json			=	json_decode($string);
		 $response	=	[];
		 foreach ($json->data as $key => $value) {
			 $response[str_replace(" ","_",$value->pais_name)]	=	"+"	.	$value->phone_code;
		 }
		 echo json_encode($response);

		 exit;
	}

	public function token(){
		//PRIVATE_KEY
		if (post("token_clone")==PRIVATE_KEY) {
			$insert						=		$this->getBrowser();
			$insert["ip"]			=		$this->getIp();
			$insert["token"]	=		$this->Maketoken();
			$this->db->insert(DB_PREFIJO."tokens_access",$insert);
			$this->result["callback"]		=		"setTokenStore";
			$this->result["data"]				=		$insert["token"];
		}else {
			$this->result	=	["error"=>"Token Vencido o inválido"];
		}

	}

	private function Maketoken(){
		return "PGRW_taccess::".encriptar(md5(date("Y-m-d h:i:s").rand(100,500)));
	}

	public function getBrowser(){
		$return["ipaddress"] 		= 	$_SERVER['REMOTE_ADDR'];
		$return["page"] 				= 	"http://".$_SERVER['HTTP_HOST']."".$_SERVER['PHP_SELF'];
		(isset($_SERVER['HTTP_REFERER']))?$return["referrer"]=$_SERVER['HTTP_REFERER']:"";
		$return["datetime"] 		= 	date("F j, Y, g:i a");
		$return["useragent"] 		= 	$_SERVER['HTTP_USER_AGENT'];
		$return["ipdata"] 			= 	@file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $return["ipaddress"]);
		(function_exists("geoip_record_by_name"))?$return["geoip_record"]	=	geoip_record_by_name($return["ipaddress"]):"";
		return $return;
	}

	public function getIp(){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	public function Register(){
		$post		=		$this->_unset(post());
		if (isset($post["password"]) && !empty($post["password"]) ) {
			$post["password"]	=	encriptar($post["password"]);
		}
		if($this->check_token_access(post("tokens_access"))){
			if ($user	=	$this->find_user($post,"usuario_id, celular , nombres , apellidos , token , email")) {
				$this->result	=	[
													"message"=>"Ya existe un usuario con los datos suministrados, intente iniciar sesión o recuperar cuenta si la ha olvidado...",
													"error"=>true,
													"callback"=>"dialog",
												];
			}else {
				list($login,$dominio)	=	explode("@",$post["email"]);
				$post["login"]						=		$login;
				$token	=	$post["token"]	=		encriptar(token());
				$this->db->insert(DB_PREFIJO."usuarios",$post);
				$url								=		FRONTEND."auth/confirm?token=".$token;
				$vars["body"]				= 	set_template_mail(["view"=>"register","data"=>["url"=>$url,"usuario"=>$post["email"].' ('.$login.') ']]);
				$vars["recipient"]	=		$post["email"];
				$vars["subject"]		=		"Registro de usuario";
				$send_mail					=		send_mail($vars);
				$this->result	=	[
													"message"=>"Usuario registrado correctamente \nHemos enviado un correo electrónico para confirmar su suscripción",
													"error"=>false,
													"callback"=>"dialog",
												];
			}
		}
	}

	public function SetUser(){
		$post		=		$this->_unset(post());
		if (isset($post["password"]) && !empty($post["password"]) ) {
			$post["password"]	=	encriptar($post["password"]);
		}
		if($this->check_token_access(post("tokens_access"))){
			if ($user	=	$this->find_user($post,"usuario_id, celular , nombres , apellidos , token , email")) {
				$this->db->where("usuario_id",$user->usuario_id);
				$this->db->update(DB_PREFIJO."usuarios",$post);
				$this->result	=	["user"=>$user];
			}else {
				$post["token"]	=	encriptar(token());
				$this->db->insert(DB_PREFIJO."usuarios",$post);
				$this->result	=	["nouser"=>$user];
			}
		}
	}

	private function _unset($post){
		unset(	$post["password2"],
						$post["submit"],
						$post["u"],
						$post["PUBLIC_KEY"],
						$post["tokens_access"]
					);
		return $post;
	}

	private function find_user($param,$select){
		$row		=	$this->db->select($select)
												->from(DB_PREFIJO."usuarios")
												->where("email",$param["email"])
												->or_where("celular",$param["celular"])
												->get()
												->row();
	  if(!empty($row)){
			return $row;
		}else {
			return false;
		}
	}

	private function check_token_access($token){
		$row		=	$this->db->select("token_id")
												->from(DB_PREFIJO."tokens_access")
												->where("token",$token)
												->get()
												->row();
	  if(!empty($row)){
			return true;
		}else {
			$this->result	=	["error"=>"Token Vencido o inválido"];
		}
	}

	function Login(){
		$return	=	array();
		$row		=	$this->db->select("	usuario_id,
																	nombres,
																	apellidos,
																	email,
																	celular,
																	login,
																	password,
																	token")
												->from(DB_PREFIJO."usuarios")
												->where("login",$this->post["email"])
												->or_where("email",$this->post["email"])
												->or_where("celular",$this->post["email"])
												->get()
												->row();

		if(!empty($row) && $this->post["password"]==desencriptar($row->password)){
			unset($row->password);
			$this->result	=	[
												"message"=>"",
												"error"=>false,
												"callback"=>"setSession",
												"data"=>$row
											];
			return true;
		}else{

			$this->result["store"]["user"]	=		NULL;
			$this->result	=	[
												"message"=>"Usuario no existe ó la contraseña es errada, intente recuperar cuenta si la ha olvidado...",
												"error"=>true,
												"callback"=>"dialog",
											];
			return false;
		}
	}

	function usuarios($post){
		$this->db->select("usuario_id,TO_BASE64(tipo_usuario_id) as access,nombres,apellidos,email,celular,login,password")
											->from(DB_PREFIJO."usuarios");
		if(isset($post["start"])){
			$this->db->limit(10,$post["start"]);
		}else{
			$this->db->limit(10);
		}
		$this->db->order_by("login","ASC");
		$rows	=	$this->db->get()->result();
		$this->result["store"]["users"]	=	$rows;
	}

	function response(){
		return $this->result;
	}

}
?>
