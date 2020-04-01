<?php
/*
	DESARROLLO Y PROGRAMACIÓN
	PROGRAMANDOWEB.NET
	LCDO. JORGE MENDEZ
	info@programandoweb.net
*/
defined('BASEPATH') OR exit('No direct script access allowed');

class Centros_de_costos_model extends CI_Model {

	var $campos,$result,$message;

	public function campos_listado(){
		return $this->campos	=	[	"tipo_usuario_id"=>"ID",
															"tipo"=>"Tipo de Usuario",
															"estatus"=>"Estado",
															"edit"=>"Acción"];
	}

	function set(){
		$tabla	=	DB_PREFIJO."centros_de_costo";
		$post		=	post();
		$post["json"]	=	json_encode($post["json"]);
		$post["institucion_id"]=1;
		$post["token"]=token();
		unset($post["redirect"]);
		unset($post["celular"]);
		if(post("token") && post("token")!='0'){
			$this->db->where("token",post("token"));
			$this->db->update($tabla,$post);
		}else{
			$this->db->insert($tabla,$post);
		}
	}

	function centro_de_costo($token){
		$this->result	=	centro_de_costo($token);
	}

	function get(){
		if(get("id")){
			return $this->centro_de_costo(get("id"));
		}
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;

		$tabla	=	DB_PREFIJO."centros_de_costo";
		$this->db->select("SQL_CALC_FOUND_ROWS centro_de_costos_id", FALSE)
							->select('	centro_de_costos_id,
													token as id,
													nombre,
													email,
													"prueba" as edit')->from($tabla);

		if(empty(get("estatus"))){
				$this->db->where("estatus<",9);
		}
		if($search["value"]){
			$this->db->like("nombre",$search["value"]);
		}
		if($order){
			$this->db->order_by("centro_de_costos_id");
		}
		if($start && $length){
			$this->db->limit($length,$start);
		}
		$query	=	$this->db->get();
		$rows		=	$query->result_array();
		if(!empty($rows)){
			$count=$this->db->query('SELECT FOUND_ROWS() count;')->row()->count;
	 		$return_ = array(	"data"=>foreach_edit($rows,$count),
	 											"recordsTotal"=>$count,
												"recordsFiltered"=>$count,
	 											"limit"=>$limit);
			$this->result	=	$return_;
			return $return_;
	 	}
	}

	function response(){
		return $this->result;
	}

}
?>
