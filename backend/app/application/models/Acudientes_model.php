<?php
/*
	DESARROLLO Y PROGRAMACIÓN
	PROGRAMANDOWEB.NET
	LCDO. JORGE MENDEZ
	info@programandoweb.net
*/
defined('BASEPATH') OR exit('No direct script access allowed');

class Acudientes_model extends CI_Model {

	var $fields,$result,$where,$total_rows,$pagination,$search,$response,$campos,$message;

	public function  campos_listado(){

	}

	public function GetAlumno(){
		echo $this->load->view("Template/tmpl/Alumnos",[],true);
	}

	public function ListaDeAlumnos(){
		if(get("id")){
			return $this->getEntrevista(get("id"));
		}
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;

		$tabla	=	DB_PREFIJO."usuarios t1";
		$this->db->select("SQL_CALC_FOUND_ROWS  t1.usuario_id", FALSE)
							->select('	t1.usuario_id,
													t1.token as id,
													t1.nombres as alumno,
													t3.grado_escolar as grado,
													"search" as view')
													->from($tabla)
													->join(DB_PREFIJO."usuarios_rel_instituciones t2","t1.usuario_id=t2.usuario_id")
													->join(DB_PREFIJO."ma_grados_escolares t3","t2.grado_escolar_id=t3.grado_escolar_id");

		$this->db->like("t1.tipo_usuario_id",2);

		if($search["value"]){
			$this->db->like("nombres",$search["value"]);
		}
		if($order){
			$this->db->order_by("usuario_id");
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
