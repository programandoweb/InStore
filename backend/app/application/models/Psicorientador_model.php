<?php
/*
	DESARROLLO Y PROGRAMACIÓN
	PROGRAMANDOWEB.NET
	LCDO. JORGE MENDEZ
	info@programandoweb.net
*/
defined('BASEPATH') OR exit('No direct script access allowed');

class Psicorientador_model extends CI_Model {

	var $fields,$result,$where,$total_rows,$pagination,$search,$response,$campos,$message;

	public function  campos_listado(){

	}

	public function SetAsistencia(){
		$post	=	post();
		if (!empty($post["alumno"])) {
			foreach ($post["alumno"] as $key => $value) {
				if (isset($post["asistencia"][$key])) {
					$asistencia	=	1;
				}else {
					$asistencia	=	0;
				}
				$insert=[
									"institucion_id"=>$post["institucion_id"],
									"docente_id"=>$this->user->usuario_id,
									"alumno_id"=>$value,
									"grado_escolar_id"=>$post["grado_escolar_id"],
									"grado_escolar"=>$post["grado_escolar"],
									"materia_token"=>$post["materia_token"],
									"materia"=>$post["materia"],
									"seccion"=>$post["seccion"],
									"periodo"=>date("Y"),
									"fecha"=>date("Y-m-d"),
									"token"=>"asistencia_".token(),
									"estatus"=>$asistencia,
									"observacion"=>@$post["observacion"][$key],
				];

				if ($post["alumnos_asistencia_id"]==0) {
					$this->db->insert(DB_PREFIJO."op_alumnos_asistencia",$insert);
				}else {
					$this->db->where("alumnos_asistencia_id",$post["alumnos_asistencia_id"]);
					$this->db->update(DB_PREFIJO."op_alumnos_asistencia",$insert);
				}
			}
		}
	}

	public function Resultados_x_materias_x_periodos(){
		$post	=	post();
		$evaluaciones		=		evaluacions_x_materias_x_alumnos($post["materia"],$post["seccion"]);
		$return	=	new stdClass();
		if (isset($post["periodos"]) && !empty($post["periodos"])) {
			$periodos=$post["periodos"];
			for ($i=1; $i <=$periodos ; $i++) {
				if (isset($evaluaciones[$i])) {
					$evaluaciones__	=	$evaluaciones[$i];
				}else {
					$evaluaciones__	=	[];
				}
				$return->contenedor_materias[$i]	=	$this->load->view("Template/tmpl/alumnos/tabla_evaluaciones_periodos_materias",["row"=>$evaluaciones__],true);
			}
		}
		$asistencias	=		asistencia($post["alumno"],$post["materia"],$post["seccion"]);
		$return->contenedor_asistencia	=		$this->load->view("Template/tmpl/alumnos/tabla_asistencias",["row"=>$asistencias],true);
		$this->result		=	$return;
		//pre($evaluaciones);
		//echo $this->load->view("Template/tmpl/alumnos/tabla_evaluaciones_periodos_materias",["post"=>post()],true);
	}

	public function GetAlumno(){
		echo $this->load->view("Template/tmpl/Alumnos",[],true);
	}

	public function GetEvaluacion(){
		echo $this->load->view("Template/Profesores/add_Evaluaciones",[],true);
	}

	public function corregir_evaluaciones(){
		$post	=	post();
		unset($post["href"],$post["target"],$post["toggle"]);
		//pre($post);
		$evaluacion_alumnos=evaluacion_alumnos($post);
		if(empty($evaluacion_alumnos)){
			$this->db->insert(DB_PREFIJO."op_alumnos_notas",$post);
		}else{
			$this->db->where("alumnos_nota_id",$evaluacion_alumnos->alumnos_nota_id);
			$this->db->update(DB_PREFIJO."op_alumnos_notas",$post);
			$evaluacion_alumnos->observacion=(!empty($evaluacion_alumnos->observacion))?"Observación: ".$evaluacion_alumnos->observacion:$evaluacion_alumnos->observacion;
		}

		$return_ = array(	"data"=> (empty($evaluacion_alumnos))?$post:$evaluacion_alumnos);
		$this->result	=	$return_;

	}

	public function SetEvaluaciones(){
		$post	=	post();
		$tabla	=	"op_profesores_evaluaciones";
		$key_		=	"notificacion_id";
		if (isset($_FILES["userfile"]) && !empty($_FILES["userfile"])) {
			$upload					=		upload_nativo($file='userfile',"documentos");
			if ($upload) {
				$post["json"]		=		json_encode($upload);
			}
		}
		$post["docente_id"]	=	$this->user->usuario_id;
		unset($post["redirect"]);
		list($materia_token,$seccion)	=	explode("::",$post["materia_token"]);

		if (!empty($seccion)) {
			$post["seccion"]				=	$seccion;
			$post["materia_token"]	=	$materia_token;
		}

		if(isset($post["token"]) && $post["token"]=="0"){
			if (isset($post["pagina"])) {
				unset($post["pagina"]);
			}
			$post["token"]			=	md5($this->user->usuario_id.post("materia_token").date("y-m-d"));
			$this->db->insert(DB_PREFIJO.$tabla,$post);
			$last_id = $this->db->insert_id();
			$alumnos_x_manteria	=	alumnos_x_manteria($materia_token,$seccion);
			foreach ($alumnos_x_manteria as $key => $value) {
				$notificacion	=	new stdClass();
				$notificacion->asunto		=		"Notificación de nueva evaluacíón";
				$notificacion->mensaje	=		"Nueva evaluación para el ".$post["fecha"].' Período:'.$post["periodo"].' al alumno:'.$value->alumno;
				$notificacion->nombre		=		$value->alumno;
				$notificacion->token		=		$post["token"];
				$notificacion->tabla		=		$tabla;
				$notificacion->key			=		$key_;
				$notificacion->value		=		$last_id;
				enviar_notificacion($this->user->usuario_id,$value->alumno_id,$notificacion);

				$notificacion	=	new stdClass();
				$notificacion->asunto		=		"Notificación de nueva evaluacíón";
				$notificacion->mensaje	=		"Nueva evaluación para el ".$post["fecha"].' Período:'.$post["periodo"].' al alumno:' .	$value->acudiente;
				$notificacion->nombre		=		$value->acudiente;
				$notificacion->token		=		$post["token"];
				$notificacion->tabla		=		$tabla;
				$notificacion->key			=		$key_;
				$notificacion->value		=		$last_id;

				$post["nombre"]	=	$value->acudiente;

				enviar_notificacion($this->user->usuario_id,$value->acudiente_id,$notificacion);
			}
		}else{
			$this->db->where("token",$post["token"]);
			$this->db->update(DB_PREFIJO."op_profesores_evaluaciones",$post);
			$alumnos_x_manteria	=	alumnos_x_manteria($materia_token,$seccion);
			foreach ($alumnos_x_manteria as $key => $value) {
				$notificacion	=	new stdClass();
				$notificacion->asunto		=		"Notificación de nueva evaluacíón";
				$notificacion->mensaje	=		"Nueva evaluación para el ".$post["fecha"].' Período:'.$post["periodo"].' al alumno:'.$value->alumno;
				$notificacion->nombre		=		$value->alumno;
				$notificacion->token		=		$post["token"];
				$notificacion->tabla		=		$tabla;
				$notificacion->key			=		$key_;
				enviar_notificacion($this->user->usuario_id,$value->alumno_id,$notificacion);

				$notificacion	=	new stdClass();
				$notificacion->asunto		=		"Notificación de nueva evaluacíón";
				$notificacion->mensaje	=		"Edición de la evaluación para el ".$post["fecha"].' Período:'.$post["periodo"].' al alumno:' .	$value->acudiente;
				$notificacion->nombre		=		$value->acudiente;
				$notificacion->token		=		$post["token"];
				$notificacion->tabla		=		$tabla;
				$notificacion->key			=		$key_;

				$post["nombre"]	=	$value->acudiente;
				enviar_notificacion($this->user->usuario_id,$value->acudiente_id,$notificacion);
			}
		}
	}

	public function ListaDeEvaluaciones(){
		if(get("id")){
			return $this->getEntrevista(get("id"));
		}
		$count	=	0;
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;

		$tabla	=	DB_PREFIJO."op_profesores_evaluaciones t1";
		$this->db->select("SQL_CALC_FOUND_ROWS  t1.evaluacion_id", FALSE)
							->select('	t1.evaluacion_id,
													t1.token as id,
													t1.evaluacion,
													t1.fecha,
													t2.materia,
													t1.periodo,
													t3.grado_escolar,
													"search" as edit_evaluacion')
													->from($tabla)
													->join(DB_PREFIJO."op_materias_x_grados t2","t1.materia_token=t2.token")
													->join(DB_PREFIJO."ma_grados_escolares t3","t2.grado_escolar_id=t3.grado_escolar_id");

		if($search["value"]){
			$this->db->like("nombres",$search["value"]);
		}
		if($this->user->tipo_usuario_id>0){
			$this->db->where("docente_id",$this->user->usuario_id);
		}
		if($order){
			$this->db->order_by("evaluacion_id");
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

	public function ListaDeAlumnos(){
		if(get("id")){
			//return $this->getEntrevista(get("id"));
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
													t4.grado_escolar as grado,
													"search" as view')
													->from($tabla)
													->join(DB_PREFIJO."rel_alumnos_instituciones t2","t1.usuario_id=t2.usuario_id")
													->join(DB_PREFIJO."op_grados_x_instituciones t3","t2.grado_escolar_token=t3.token")
													->join(DB_PREFIJO."ma_grados_escolares t4","t3.grado_escolar_id=t4.grado_escolar_id")
													->join(DB_PREFIJO."op_materias_x_grados t5","t3.grado_escolar_id=t5.grado_escolar_id")
													->join(DB_PREFIJO."rel_profesores_materias t6","t5.token=t6.materia_token");


		$this->db->like("t1.tipo_usuario_id",2);
		if($search["value"]){
			$this->db->like("nombres",$search["value"]);
		}
		$this->db->group_by("usuario_id");
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

	public function ListaDeMisAlumnos(){
		if(get("id")){
			//return $this->getEntrevista(get("id"));
		}
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;
		$rows		=	[];

		$tabla	=	DB_PREFIJO."usuarios t1";
		$this->db->select("SQL_CALC_FOUND_ROWS  t1.usuario_id", FALSE)
							->select('	t1.usuario_id,
													t1.token as id,
													t1.nombres as alumno,
													t4.grado_escolar as grado,
													"search" as view')
													->from($tabla)
													->join(DB_PREFIJO."rel_alumnos_instituciones t2","t1.usuario_id=t2.usuario_id")
													->join(DB_PREFIJO."op_grados_x_instituciones t3","t2.grado_escolar_token=t3.token")
													->join(DB_PREFIJO."ma_grados_escolares t4","t3.grado_escolar_id=t4.grado_escolar_id")
													->join(DB_PREFIJO."op_materias_x_grados t5","t3.grado_escolar_id=t5.grado_escolar_id")
													->join(DB_PREFIJO."rel_profesores_materias t6","t5.token=t6.materia_token");


		$this->db->like("t1.tipo_usuario_id",2);
		if($this->user->tipo_usuario_id>0){
			$this->db->where("docente_id",$this->user->usuario_id);
		}
		if($search["value"]){
			$this->db->like("nombres",$search["value"]);
		}
		$this->db->group_by("usuario_id");
		if($order){
			$this->db->order_by("usuario_id");
		}
		if($start && $length){
			$this->db->limit($length,$start);
		}
		$query	=	$this->db->get();
		$rows		=	$query->result_array();
		if (empty($rows)) {
			$this->result= new stdClass();
			$this->result->data=[];
			return;
		}
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
