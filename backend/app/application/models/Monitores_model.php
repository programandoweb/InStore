<?php
/*
	DESARROLLO Y PROGRAMACIÓN
	PROGRAMANDOWEB.NET
	LCDO. JORGE MENDEZ
	info@programandoweb.net
*/
defined('BASEPATH') OR exit('No direct script access allowed');

class Monitores_model extends CI_Model {

	var $campos,$result,$message;

	public function campos_listado(){
		return $this->campos	=	[	"tipo_usuario_id"=>"ID",
															"tipo"=>"Tipo de Usuario",
															"estatus"=>"Estado",
															"edit"=>"Acción"];
	}

	public function Clock(){
		$usuarios_x_token=usuarios_x_token(get("id"));
		unset($usuarios_x_token->password);
		$this->result["data"]		=	$usuarios_x_token;
		$clock	=	reloj($this->result["data"]->usuario_id);
		$insert=[];
		if(empty($clock) && get("set")==1){
			$insert=[	"modelo_id"=>$this->result["data"]->usuario_id,
								"tipo"=>1,
								"fecha"=>date("Y-m-d"),
								"hora"=>date("H:i:s"),
								"token"=>token(),
							];
			$this->db->insert(DB_PREFIJO."op_seguimiento_modelos_reloj",$insert);
		}elseif (!empty($clock) && $clock->tipo==1 && get("set")==2) {
			$insert=[	"modelo_id"=>$this->result["data"]->usuario_id,
								"tipo"=>2,
								"fecha"=>date("Y-m-d"),
								"hora"=>date("H:i:s"),
								"token"=>token(),
							];
			$this->db->insert(DB_PREFIJO."op_seguimiento_modelos_reloj",$insert);
		}elseif (!empty($clock) && ($clock->tipo==1 || $clock->tipo==2) && get("set")==3) {
			$insert=[	"modelo_id"=>$this->result["data"]->usuario_id,
								"tipo"=>3,
								"fecha"=>date("Y-m-d"),
								"hora"=>date("H:i:s"),
								"token"=>token(),
							];
			$this->db->insert(DB_PREFIJO."op_seguimiento_modelos_reloj",$insert);
		}elseif (!empty($clock) && ($clock->tipo==1 || $clock->tipo==2) && get("set")==1) {
			$insert=[	"modelo_id"=>$this->result["data"]->usuario_id,
								"tipo"=>1,
								"fecha"=>date("Y-m-d"),
								"hora"=>date("H:i:s"),
								"token"=>token(),
							];
			$this->db->insert(DB_PREFIJO."op_seguimiento_modelos_reloj",$insert);
		}
		$this->result["clock"]	= $insert;
	}

	public function GetModelos(){
		$usuarios_x_token=usuarios_x_token(get("id"));
		unset($usuarios_x_token->password);
		$this->result["data"]		=	$usuarios_x_token;
		$this->result["clock"]	=	reloj($this->result["data"]->usuario_id);
	}

	public function exc_List_Usuarios(){
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$tabla	=	DB_PREFIJO."usuarios";
		$this->db->select("SQL_CALC_FOUND_ROWS usuario_id", FALSE)
							->select('nombres,usuario_id,usuario_id as id,"prueba" as edit')->from($tabla);
		$this->db->like("tipo_usuario_id",get("t"));
		if($search["value"]){
			$this->db->like("nombres",$search["value"]);
		}
		if($order){
			$this->db->order_by("nombres");
		}

		if($length){
			$this->db->limit($length,$start);
		}
		$query	=	$this->db->get();
		$total_rows=$this->db->query('SELECT FOUND_ROWS() count;')->row()->count;
		$this->result["data"]		= foreach_edit($query->result_array(),$total_rows);
		$this->result["recordsTotal"]	=	$this->result["recordsFiltered"] =	$total_rows;
		$this->result["draw"]	=	get("draw");
		$this->message=	"";
	}

	function response(){
		return $this->result;
	}

}
?>
