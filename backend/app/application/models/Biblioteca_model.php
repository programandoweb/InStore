<?php
/*
	DESARROLLO Y PROGRAMACIÓN
	PROGRAMANDOWEB.NET
	LCDO. JORGE MENDEZ
	info@programandoweb.net
*/
defined('BASEPATH') OR exit('No direct script access allowed');

class Biblioteca_model extends CI_Model {

	var $fields,$result,$where,$total_rows,$pagination,$search,$response,$campos,$message;

	public function  campos_listado(){
		$this->campos	=	[	"usuario_id"=>"Usuario ID",
											"nombres"=>"Nombres y Apellidos"];
	}

	public function buscarLibro(){
		echo json_encode(libros(get("term")));exit;
	}

	public function Libros(){
		$return	=	new stdClass();
		echo $this->load->view("Template/tmpl/biblioteca/libros",["row"=>libro(get("id"))],true);
	}

	public function SetLibro(){
		//phpinfo();exit;
		$post	=	post();
		$tabla	=	"op_biblioteca";
		$key_		=	"notificacion_id";
		if (isset($_FILES["userfile"]) && !empty($_FILES["userfile"])) {
			$upload						=		upload_nativo($file='userfile',"biblioteca");
			if ($upload) {
				$post["json"]		=		json_encode($upload);
			}
		}
		$post["usuario_id"]		=	$this->user->usuario_id;
		unset($post["redirect"]);
		if(isset($post["token"]) && $post["token"]=="0" ){
			$post["token"]			=		"Biblioteca_".date("y-m-d")."_".token();
			$this->db->insert(DB_PREFIJO.$tabla,$post);
			$last_id = $this->db->insert_id();
		}else{
			$this->db->where("token",$post["token"]);
			$this->db->update(DB_PREFIJO.$tabla,$post);
		}
	}

	public function ListaLibros(){
		$count	=	0;
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;

		$tabla	=	DB_PREFIJO."op_biblioteca t1";
		$this->db->select("SQL_CALC_FOUND_ROWS  t1.biblioteca_id", FALSE)
							->select('	t1.biblioteca_id,
													t1.token as id,
													t1.texto,
													t1.fecha,
													t1.descripcion,
													CONCAT(t2.nombres," (",t2.identificacion,")") AS autor,
													"search" as view')
													->from($tabla)
													->join(DB_PREFIJO."usuarios t2","t1.usuario_id=t2.usuario_id");

		if($search["value"]){
			$this->db->like("texto",$search["value"]);
		}
		if($order){
			$this->db->order_by("fecha","DESC");
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
	 	}else{
			$count=$this->db->query('SELECT FOUND_ROWS() count;')->row()->count;
	 		$return_ = array(	"data"=>foreach_edit([],$count),
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
