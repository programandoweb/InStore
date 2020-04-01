<?php
/*
	DESARROLLO Y PROGRAMACIÓN
	PROGRAMANDOWEB.NET
	LCDO. JORGE MENDEZ
	info@programandoweb.net
*/
defined('BASEPATH') OR exit('No direct script access allowed');

class Instituciones_model extends CI_Model {

	var $fields,$result,$where,$total_rows,$pagination,$search,$response,$campos,$message;

	public function  campos_listado(){
		$this->campos	=	[	"usuario_id"=>"Usuario ID",
											"nombres"=>"Nombres y Apellidos"];
	}

	public function set_gradosescolares(){
		$post=post();
		$post["estatus"]=1;
		$this->db->select("id");
		$this->db->from(DB_PREFIJO."rel_alumnos_instituciones");
		$this->db->where("usuario_id",$post["usuario_id"]);
		$this->db->where("institucion_id",$post["institucion_id"]);
		//$this->db->where("grado_escolar_token",$post["grado_escolar_token"]);
		$row	=		$this->db->get()->row();

		if(empty($row)){
			$this->db->insert(DB_PREFIJO."rel_alumnos_instituciones",$post);
		}else{
			$this->db->where("id",$row->id);
			$this->db->update(DB_PREFIJO."rel_alumnos_instituciones",$post);
		}

		$this->result["message"]	=	" se ha inscrito satisfactoria";

	}

	public function get_gradosescolares(){
		$this->result["data"]	=	grados_x_institucion_x_nivelEducativo(post("id"),$this->user->institucion_id);
	}

	public function setRelMateriasDocente(){
		$post	=	post();
		$yo		=	usuarios($this->user->usuario_id);
		$row	=	$this->db->select("id")
											->from(DB_PREFIJO."rel_profesores_materias")
											->where("institucion_id",$yo->institucion_id)
											->where("seccion",$post["seccion"])
											->where("materia_token",$post["materia"])
											->where("grado_escolar_id",$post["grado"])
											->where("anoescolar",$post["anoescolar"])
											->get()
											->row();
		$insert		=	[
									"institucion_id"=>$yo->institucion_id,
									"docente_id"=>$post["docente_id"],
									"materia_token"=>$post["materia"],
									"grado_escolar_id"=>$post["grado"],
									"anoescolar"=>$post["anoescolar"],
									"seccion"=>$post["seccion"],
								]	;
		if(empty($row)){
			$this->db->insert(DB_PREFIJO."rel_profesores_materias",$insert);
		}else{
			$this->db->where("id",$row->id);
			$this->db->update(DB_PREFIJO."rel_profesores_materias",$insert);
		}
	}

	public function deleteHorario(){
		$this->db->where("token",get("id"));
		$this->db->delete(DB_PREFIJO."op_materias_x_horarios");
	}

	public function getHorarioMateria(){
		$this->result["items"]	=	materias_x_horarios(get("institucionId"),get("nivelEducativoId"),get("gradoEscolarId"),get("dia"),get("token_materia"),get("letra"));
	}

	public function SetInstitucion(){
		$institucion=institucion_x_token(post("token"));
		if($this->user->tipo_usuario_id==0){
			$tabla	=	DB_PREFIJO."instituciones";
			$post		=	post();
			unset($post["redirect"]);
			if(empty($institucion)){
				$this->db->insert($tabla,$post);
			}else{
				$this->db->where("token",$institucion->token);
				$this->db->update($tabla,$post);
			}
		}
	}

	public function GetInstituciones(){
		$tabla	=	DB_PREFIJO."instituciones";
		$data		=	$this->db->select("	* ")
												->from($tabla)
												->where("token",get("id"))
												->get()
												->row();

		if(!empty($data->json) && $data->json!=NULL){
			$data=campo_json_db($data);
		}
		$this->result	=	$data;
	}

	public function exc_List_Instituciones(){
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$tabla	=	DB_PREFIJO."instituciones t1";

		$this->db->select("SQL_CALC_FOUND_ROWS institucion_id", FALSE)
							->select('token as id,nombre_comercial,"prueba" as edit')
							->from($tabla);

		if($search["value"]){
			$this->db->like("nombre_comercial",$search["value"]);
			$this->db->or_like("nombre_legal",$search["value"]);
		}
		if($order){
			$this->db->order_by("nombre_comercial");
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
