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

	function login(){
		$return	=	array();
		$row		=	$this->db->select("	usuario_id,
																	TO_BASE64(tipo_usuario_id) as access,
																	nombres,
																	apellidos,
																	email,
																	telefono,
																	login,
																	password,
																	institucion_id")
												->from(DB_PREFIJO."usuarios")
												->where("login",$this->post["login"])
												->or_where("email",$this->post["login"])
												->or_where("telefono",$this->post["login"])
												->get()
												->row();
		if(!empty($row) && md5($this->post["password"])==$row->password){
			unset($row->password);
			$this->result["store"]["user"]	=		$row;
			$this->result["store"]["token"]	=		genera_token($row->usuario_id);
			$this->message	=	 "Usuario sí existe";
			return true;
		}else{
			$this->result->store["user"]	=		NULL;
			$this->message	=		"Usuario no existe ó la contraseña es errada";
			return false;
		}
	}

	function usuarios($post){
		$this->db->select("usuario_id,TO_BASE64(tipo_usuario_id) as access,nombres,apellidos,email,telefono,login,password")
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
