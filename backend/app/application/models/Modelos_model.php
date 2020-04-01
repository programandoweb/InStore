<?php
/*
	DESARROLLO Y PROGRAMACIÓN
	PROGRAMANDOWEB.NET
	LCDO. JORGE MENDEZ
	info@programandoweb.net
*/
defined('BASEPATH') OR exit('No direct script access allowed');

class Modelos_model extends CI_Model {

	var $campos,$result,$message;

	public function campos_listado(){
		return $this->campos	=	[	"tipo_usuario_id"=>"ID",
															"tipo"=>"Tipo de Usuario",
															"estatus"=>"Estado",
															"edit"=>"Acción"];
	}

	public function exc_produccion(){
		$tabla1	=	DB_PREFIJO."op_importar_excel t1";
		$tabla2	=	DB_PREFIJO."usuarios t2";
		$tabla3	=	DB_PREFIJO."centros_de_costo t3";
		$rows		=	$this->db->select("	t1.*,t2.nombres,t3.nombre,t2.parent_id,t4.nombres as monitor, t1.tokens as monto ")
												->from($tabla1)
												->join($tabla2,"t1.modelo_id = t2.usuario_id","left")
												->join($tabla3,"t1.centro_de_costos_id = t3.centro_de_costos_id","left")
												->join(DB_PREFIJO."usuarios t4","t2.parent_id = t4.usuario_id","left");

		if($this->user->tipo_usuario_id>0){
			$this->db->where("t1.modelo_id",$this->user->usuario_id);
		}

		$rows		=	$this->db->get()->result();
		$return=[];
		foreach ($rows as $key => $value) {
			$return[$key]	=	$value;
			$return[$key]->monto	=	$value->tokens * 20;
		}
		$this->result["data"]		= foreach_edit($return);
		$this->result["recordsTotal"]	=	$this->result["recordsFiltered"] =	10;
		$this->result["draw"]	=	get("draw");
	}

	function response(){
		return $this->result;
	}

}
?>
