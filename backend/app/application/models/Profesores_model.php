<?php
/*
	DESARROLLO Y PROGRAMACIÓN
	PROGRAMANDOWEB.NET
	LCDO. JORGE MENDEZ
	info@programandoweb.net
*/
defined('BASEPATH') OR exit('No direct script access allowed');

class Profesores_model extends CI_Model {

	var $fields,$result,$where,$total_rows,$pagination,$search,$response,$campos,$message;

	public function  campos_listado(){

	}

	public function Calificar(){
		$post	=	post();
		$tabla1	=		DB_PREFIJO."op_profesores_evaluaciones";
		$row		= 	$this->db->select('*')
													->from($tabla1)
													->where("evaluacion_id",$post["evaluacion_id2"])
													->get()
													->row();
		if(!empty($row)){
			$insert	=	[
									"docente_id"=>$this->user->usuario_id,
									"alumno_id"=>$post["alumno_id"],
									"evaluacion_id"=>$post["evaluacion_id2"],
									"nota"=>$post["evaluacion"],
									"periodo"=>$row->periodo,
									"fecha"=>date("Y-m-d"),
									"token"=>"Evaluacion_".token(),
									"estatus"=>1,
									"observacion"=>$post["observacion"],
			];

			$row2		= 	$this->db->select('*')
														->from(DB_PREFIJO."op_alumnos_notas")
														->where("evaluacion_id",$post["evaluacion_id2"])
														->where("alumno_id",$post["alumno_id"])
														->get()
														->row();
			if (empty($row2)) {
				$this->db->insert(DB_PREFIJO."op_alumnos_notas",$insert);
			}else {
				$this->db->where("alumnos_nota_id",$row2->alumnos_nota_id);
				$this->db->update(DB_PREFIJO."op_alumnos_notas",$insert);
			}

			$insert["fecha"]						=	fecha(date("Y-m-d"),"d/m/Y");
			$this->result["callBack"]		=	"extraData";
			$this->result["extraData"]	=	$insert;

			$alumnos 			= usuarios($post["alumno_id"]);
			$evaluacion 	= evaluacion_x_id($insert["evaluacion_id"]);
			$message			=	'Obtuvo '.$post["evaluacion"].' Ptos en '.$evaluacion->evaluacion;
			$recipientes	=	get_recipientes_alumnos($post["alumno_id"]);
			curl_push('Evaluación a '.$alumnos->nombres,$message,$recipientes);
		}
	}

	public function setInfo(){
		$componentes	=	explode(",",get("component"));
		$return_			=	[];
		foreach ($componentes as $key => $value) {
			$_string		=		"App_".$value;
			$return_[$value]	=		$this->{$_string}();
		}
		$this->result	=	$return_;
	}

	private function App_ListaProfesores(){
		$return=[];
		$contador=0;
		foreach (alumno_acudiente($this->user->usuario_id) as $key => $value) {
			foreach (Lista_Profesores_X_Grados($value->grado_escolar_token) as $key2 => $value2) {
				$return[$contador]= $value2;
				$contador++;
			}
		}
		return $return;
	}

	private function App_ListaAlumnosNotas(){
		$return=[];
		foreach (alumno_acudiente($this->user->usuario_id) as $key => $value) {
			$objeto	=	new stdClass;
			$objeto->materias	=	materias_x_alumnos($value->usuario_id);
			$objeto->alumno	=	$value;
			$return[$value->usuario_id]	=	$objeto;
		}
		return $return;
	}

	private function App_marcar_asistencia(){
		$post = post();
		$post["alumno"]	=	json_decode($post["alumnos"]);

		if (!empty($post["alumno"])) {
			foreach ($post["alumno"] as $key => $value) {
				$asistencia	=	asistencia_por_alumno($key,$post["materia_token"],date("Y-m-d"));
				$insert=[
									"institucion_id"=>$post["institucion_id"],
									"docente_id"=>$this->user->usuario_id,
									"alumno_id"=>$key,
									"grado_escolar_id"=>$post["grado_escolar_id"],
									"grado_escolar"=>$post["grado_escolar"],
									"materia_token"=>$post["materia_token"],
									"materia"=>$post["materia"],
									"seccion"=>$post["letra"],
									"periodo"=>date("Y"),
									"fecha"=>date("Y-m-d"),
									"token"=>"asistencia_".token(),
									"estatus"=>($value=="card")?0:1,
									"observacion"=>"Procesado por APP",
				];
				if (empty($asistencia)) {
					$this->db->insert(DB_PREFIJO."op_alumnos_asistencia",$insert);
				}
			}
		}

		$insert=[
							"institucion_id"=>$post["institucion_id"],
							"docente_id"=>$this->user->usuario_id,
							"alumno_id"=>$post["usuario_id"],
							"grado_escolar_id"=>$post["grado_escolar_id"],
							"grado_escolar"=>$post["grado_escolar"],
							"materia_token"=>$post["materia_token"],
							"materia"=>$post["materia"],
							"seccion"=>$post["letra"],
							"periodo"=>date("Y"),
							"fecha"=>date("Y-m-d"),
							"token"=>"asistencia_".token(),
							"estatus"=>$post["asistio"],
							"observacion"=>"Procesado por APP",
		];
		$asistencia	=	asistencia_por_alumno($post["usuario_id"],$post["materia_token"],date("Y-m-d"));

		if (empty($asistencia)) {
			$this->db->insert(DB_PREFIJO."op_alumnos_asistencia",$insert);
		}else {
			$this->db->where("alumnos_asistencia_id",$asistencia->alumnos_asistencia_id);
			$this->db->update(DB_PREFIJO."op_alumnos_asistencia",$insert);
		}
		$recipientes	=	get_recipientes_alumnos($post["usuario_id"]);
		//pre($recipientes,false);
		curl_push($post["title"],$post["message"],$recipientes);
	}

	public function getInfo(){
		$componentes	=	explode(",",get("component"));
		$return_			=	[];
		foreach ($componentes as $key => $value) {
			$_string		=		"App_".$value;
			if(method_exists($this,$_string)){
				$return_[$value]	=		$this->{$_string}();
			}else{
				$return_[$value]	=		[];
			}
		}
		$this->result	=	$return_;
	}

	private function App_ListaTareasDeMiHijo(){
		return	alumno_acudiente_evaluaciones(get("id"),$this->user->usuario_id);
	}

	private function App_ListaAlumnoAcudiente(){
		return $alumnos			= alumno_acudiente($this->user->usuario_id);
		$evaluaciones	=	[];
		foreach ($alumnos as $key => $value) {
			$push	=	new stdClass;
			$value->avatar			=		avatar($value->genero);
			$push->alumno				=		$value;
			$push->evaluaciones	=		alumno_acudiente_evaluaciones($value->grado_escolar_token,$this->user->usuario_id);
			$evaluaciones[$value->usuario_id]			=		$push;
		}
		return $evaluaciones;
	}

	private function App_ListaDeAlumnosAsistenciaSinAsistencia(){
		return	$this->App_ListaDeAlumnosAsistencia(true);
	}

	private function App_NotasEvaluacion(){
		list($token_materia,$grado_escolar_id,$seccion,$evaluacion_id)	=	explode("::",get("id"));
		$tabla1	=	DB_PREFIJO."op_alumnos_notas";
		$rows		= 	$this->db->select('*,DATE_FORMAT(fecha,"%d/%m/%Y") as fecha')
													->from($tabla1)
													->where("evaluacion_id",$evaluacion_id)
													->get()
													->result();
		$return=[];
		foreach ($rows as $key => $value) {
			$return[$value->alumno_id]	=	$value;
		}
		return $return;
	}

	private function App_ListaDeAlumnosAsistencia($skip=false){
		$dias		=	array_dias();
		list($token_materia,$grado_escolar_id,$seccion)	=	explode("::",get("id"));
		$return["Lista_Alumnos"]				=	Lista_Alumnos($token_materia,$grado_escolar_id,$seccion);
		$asistencias_hoy  = asistencias_hoy($this->user->usuario_id,$token_materia,$grado_escolar_id,$seccion);
		$return_asistencia_hoy	=	[];
		foreach ($return["Lista_Alumnos"] as $key => $value) {
			if ($value->genero=="masculino") {
				$return["Lista_Alumnos"][$key]->avatar	=	'design/nino.png';
			}
			else {
				$return["Lista_Alumnos"][$key]->avatar	=	'design/nina.png';
			}
			$alumno_id	=	$value->usuario_id;
			if (isset($asistencias_hoy[$alumno_id]) && $asistencias_hoy[$alumno_id]->estatus==1) {
				$return_asistencia_hoy[$alumno_id]	=	"card2";
			}else {
				$return_asistencia_hoy[$alumno_id]	=	"card";
			}
		}
		if (!$skip) {
			$return["asistencias_hoy"]	=		$return_asistencia_hoy;
		}
		return $return;
	}

	private function App_ListaDeMaterias(){
		$Docentes_x_Materias_evaluacion=Docentes_x_Materias_evaluacion($this->user->usuario_id);
		return $Docentes_x_Materias_evaluacion;
	}

	private function App_ListaDeAsistencia(){
		$Horario_x_profesores=Horario_x_profesor($this->user->institucion_id,$this->user->usuario_id);
		return $Horario_x_profesores;
	}

	private function App_ListaDeMisAlumnos(){
		$ListaDeMisAlumnos	=		$this->ListaDeMisAlumnos();
		return reset($ListaDeMisAlumnos["data"]);
	}

	private function App_ListaDeEvaluaciones(){
		if ($this->user->tipo_usuario_id==5) {
			return alumno_acudiente($this->user->usuario_id);
		}else if ($this->user->tipo_usuario_id==4) {
			$ListaDeEvaluaciones	=	$this->ListaDeEvaluaciones();
			if (!empty($ListaDeEvaluaciones["data"])) {
				$return	=	[];
				foreach ($ListaDeEvaluaciones["data"] as $key => $value) {
					foreach ($value as $key2 => $value2) {
						$return[$value2["grado_id"]][]	=	$value2;
					}
				}
				return $return;
			}else {
				return new stdClass;
			}
		}else{

		}
	}

	private function App_ListaDeGrados(){
		$grado	=	[];
		$ListaDeEvaluaciones	=	$this->ListaDeEvaluaciones();
		if (isset($ListaDeEvaluaciones["data"]) && !empty($ListaDeEvaluaciones["data"])) {
			foreach ($ListaDeEvaluaciones["data"] as $key => $value) {
				if (!isset($grado[$key])) {
					$grado[$key]	=	$value[0]["grado_escolar"];
				}
			}
		}
		return $grado;
	}

	public function uploadFile($doc=false){
		pre($_FILES);
		if (isset($_FILES["userfile"]) && !empty($_FILES["userfile"])) {
			echo 100;exit;
			$upload						=		upload_nativo('userfile',"documentos");
			echo $json = json_decode(file_get_contents('php://input'), true);return;
			echo json_encode($_FILES);exit;
			if ($upload) {
				$post["json"]		=		json_encode($upload);
			}
		}
	}

	public function APP_AddEvaluacion(){
		$tabla	=	"op_profesores_evaluaciones";
		$post	=	post();
		$post["biblioteca"]	="";
		if (isset($post["archivo_datos"])) {
			$json	=	json_decode($post["archivo_datos"]);
			$is_image=false;
			$md5	=	$this->user->usuario_id."::".md5($json->name).'.'.$post["archivo_ext"];
			$file	=	$this->base64ToImage($post["archivo_base64"], PATH_BASE.'images/uploads/documentos/'.$md5);
			if (	$post["archivo_ext"]=="jpg" ||
						$post["archivo_ext"]=="jpeg"	||
						$post["archivo_ext"]=="gif"	||
						$post["archivo_ext"]=="png"
			){
					$is_image=true;
			}
			$post["biblioteca"]	=	DOMINIO.'images/uploads/documentos/'.$md5;
		}


		$post["docente_id"]	=	$this->user->usuario_id;
		list($materia_token,$seccion)	=	explode("::",$post["materia_token"]);
		if (!empty($seccion)) {
			$post["seccion"]				=	$seccion;
			$post["materia_token"]	=	$materia_token;
		}
		unset($post["archivo_base64"],$post["archivo_datos"],$post["archivo_ext"]);
		if(isset($post["token"]) && $post["token"]=="0"){
			if (isset($post["pagina"])) {
				unset($post["pagina"]);
			}
			$post["token"]			=	"Evaluacion_".md5($this->user->usuario_id.post("materia_token").date("y-m-d").token());
			$post["estatus"]		=	1;
			$this->db->insert(DB_PREFIJO.$tabla,$post);
			$last_id = $this->db->insert_id();
		}

		$tabla						=	DB_PREFIJO."op_profesores_evaluaciones";
		$this->db->select("*")->from($tabla);
		$this->db->where("evaluacion_id",$last_id);
		$this->result	=	$this->db->get()->row();
	}

	function base64ToImage($base64_string, $output_file) {
		  $file = fopen($output_file, "wb");
			fwrite($file, base64_decode($base64_string));
	    fclose($file);
	    return $output_file;
	}

	public function SetAsistencia(){
		$post	=	post();
		if (!empty($post["alumno"])) {
			foreach ($post["alumno"] as $key => $value) {
				if (in_array($value, $post["asistencia"])) {
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

				$asistencia	=	asistencia_por_alumno($value,$post["materia_token"],date("Y-m-d"));
				if (empty($asistencia)) {
					$this->db->insert(DB_PREFIJO."op_alumnos_asistencia",$insert);
				}else {
					$this->db->where("alumnos_asistencia_id",$asistencia->alumnos_asistencia_id);
					$this->db->update(DB_PREFIJO."op_alumnos_asistencia",$insert);
				}
			}
		}
	}

	public function Resultados_x_materias_x_periodos(){
		$post	=	post();
		$evaluaciones		=		evaluacions_x_materias_x_alumnos($post["materia"],$post["seccion"]);
		//pre($evaluaciones);
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
		unset($post["href"],$post["target"],$post["toggle"],$post["notarel"],$post["response"]);
		//pre($post);
		$evaluacion_alumnos	=	evaluacion_alumnos($post);
		$evaluacion					=	evaluacion($post["token"]);
		$alumno							=	usuarios($post["alumno_id"]);
		$acudiente					=	get_acudiente_x_alumno($alumno->usuario_id);
		//pre($acudiente);
		if(empty($evaluacion_alumnos)){
			$this->db->insert(DB_PREFIJO."op_alumnos_notas",$post);
		}else{
			$this->db->where("alumnos_nota_id",$evaluacion_alumnos->alumnos_nota_id);
			$this->db->update(DB_PREFIJO."op_alumnos_notas",$post);
			$evaluacion_alumnos->observacion=(!empty($evaluacion_alumnos->observacion))?"Observación: ".$evaluacion_alumnos->observacion:$evaluacion_alumnos->observacion;
		}

		$notificacion	=	new stdClass();
		$notificacion->asunto		=		"Resultados de evaluación o taller ";
		$notificacion->mensaje	=		"Resultados de ".$evaluacion->evaluacion.'<br/>alumno:'.$alumno->nombres.'<br/>Nota Alcanzada:'.$post["nota"]." Puntos";
		$notificacion->nombre		=		$acudiente->nombres;
		$notificacion->token		=		$post["token"];
		enviar_notificacion($this->user->usuario_id,$acudiente->acudiente_id,$notificacion);
		$return_ = array(	"data"=> (empty($evaluacion_alumnos))?$post:$evaluacion_alumnos);
		$this->result	=	$return_;
	}

	public function SetEvaluaciones($doc=false){
		$post	=	post();
		$tabla	=	"op_profesores_evaluaciones";
		$key_		=	"notificacion_id";
		if (!$doc) {
			if (isset($_FILES["userfile"]) && !empty($_FILES["userfile"])) {
				$upload						=		upload_nativo($file='userfile',"documentos");
				if ($upload) {
					$post["json"]		=		json_encode($upload);
				}
			}
		}else {
			$post["biblioteca"]	=		$doc;
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
			$post["token"]			=	"Evaluacion_".md5($this->user->usuario_id.post("materia_token").date("y-m-d").token());
			$this->db->insert(DB_PREFIJO.$tabla,$post);
			$last_id = $this->db->insert_id();
			$alumnos_x_manteria	=	alumnos_x_manteria($materia_token,$seccion);
			foreach ($alumnos_x_manteria as $key => $value) {
				$notificacion	=	new stdClass();
				$notificacion->asunto		=		"Notificación de nueva evaluacíón";
				$notificacion->mensaje	=		"Nueva evaluación para el ".$post["fecha"].' Período:'.$post["periodo"].' al alumno:'.$value->alumno;
				$notificacion->nombre		=		$value->alumno;
				$notificacion->token		=		$post["token"];
				$notificacion->key			=		$key_;
				$notificacion->tabla							=		$tabla;
				$notificacion->key_referencia			=		"evaluacion_id";
				$notificacion->value							=		$last_id;
				enviar_notificacion($this->user->usuario_id,$value->alumno_id,$notificacion);

				$notificacion	=	new stdClass();
				$notificacion->asunto		=		"Notificación de nueva evaluacíón";
				$notificacion->mensaje	=		"Nueva evaluación para el ".$post["fecha"].' Período:'.$post["periodo"].' al alumno:' .	$value->acudiente;
				$notificacion->nombre		=		$value->acudiente;
				$notificacion->token		=		$post["token"];
				$notificacion->tabla		=		$tabla;
				$notificacion->key			=		$key_;
				$notificacion->key_referencia			=		"evaluacion_id";
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
				$notificacion->key_referencia			=		"evaluacion_id";
				enviar_notificacion($this->user->usuario_id,$value->alumno_id,$notificacion);

				$notificacion	=	new stdClass();
				$notificacion->asunto		=		"Notificación de nueva evaluacíón";
				$notificacion->mensaje	=		"Edición de la evaluación para el ".$post["fecha"].' Período:'.$post["periodo"].' al alumno:' .	$value->acudiente;
				$notificacion->nombre		=		$value->acudiente;
				$notificacion->token		=		$post["token"];
				$notificacion->tabla		=		$tabla;
				$notificacion->key			=		$key_;
				$notificacion->key_referencia			=		"evaluacion_id";

				$post["nombre"]	=	$value->acudiente;
				enviar_notificacion($this->user->usuario_id,$value->acudiente_id,$notificacion);
			}
		}
		if (!$doc) {
			$this->session->set_flashdata('event', 'recarga_nuevos_datos');
		}
	}

	public function ListaDeEvaluaciones(){
		$count	=	0;
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;

		$tabla	=	DB_PREFIJO."op_profesores_evaluaciones t1";
		$this->db->select("SQL_CALC_FOUND_ROWS  t1.evaluacion_id", FALSE)
							->select('	t1.*,
													t1.evaluacion_id,
													t1.token as id,
													t1.evaluacion,
													DATE_FORMAT(t1.fecha,"%d/%m/%Y") as fecha,
													t2.materia,
													t4.token as grado_id,
													t1.periodo,
													CONCAT(t3.grado_escolar," (", t1.seccion,") ") AS grado_escolar,
													t3.grado_escolar_id,
													"search" as edit_evaluacion')
													->from($tabla)
													->join(DB_PREFIJO."op_materias_x_grados t2","t1.materia_token=t2.token")
													->join(DB_PREFIJO."ma_grados_escolares t3","t2.grado_escolar_id=t3.grado_escolar_id")
													->join(DB_PREFIJO."op_grados_x_instituciones t4","t2.grado_escolar_id=t4.grado_escolar_id AND t1.seccion=t4.letra");

		if(@$search["value"]){
			$this->db->like("nombres",$search["value"]);
		}
		if($this->user->tipo_usuario_id>0){
			$this->db->where("docente_id",$this->user->usuario_id);
		}
		if($order){
			$this->db->order_by("evaluacion_id" ,"DESC");
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
			if (get("u")=='') {
				$this->result	=	$return_;
			}else {
				$return_movile	=	[];
				foreach (array_reverse($rows) as $key => $value) {
					$return_movile[$value["grado_id"]][]	=	$value;
				}
				$return_["data"]=$return_movile;
				$return_["data_peticiones"]["materias"]	=	Docentes_x_Materias_evaluacion($this->user->usuario_id);
				$this->result	=	$return_;
			}
			return $return_;
	 	}else{
			$count=$this->db->query('SELECT FOUND_ROWS() count;')->row()->count;
	 		$return_ = array(	"data"=>foreach_edit([],$count),
	 											"recordsTotal"=>$count,
												"recordsFiltered"=>$count,
	 											"limit"=>$limit);
			$return_["data_peticiones"]["materias"]	=	Docentes_x_Materias_evaluacion($this->user->usuario_id);
			$this->result	=	$return_;
			return $return_;
		}
	}

	public function ListaDeAlumnos($token,$seccion=false){
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
		$this->db->where("t5.token",$token);
		if ($seccion) {
				$this->db->where("t3.letra",$seccion);
		}
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
		//pre(post());
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
													t1.tipo_usuario_id,
													t1.genero,
													t1.telefono,
													t2.grado_escolar_token,
													t4.grado_escolar as grado,
													t4.grado_escolar,
													t3.token as grado_id,
													t3.letra as seccion,
													t2.acudiente_id,
													t7.nombres as acudiente,
													t7.telefono as acudiente_telefono,
													"search" as view')
													->from($tabla)
													->join(DB_PREFIJO."rel_alumnos_instituciones t2","t1.usuario_id=t2.usuario_id")
													->join(DB_PREFIJO."op_grados_x_instituciones t3","t2.grado_escolar_token=t3.token")
													->join(DB_PREFIJO."ma_grados_escolares t4","t3.grado_escolar_id=t4.grado_escolar_id")
													->join(DB_PREFIJO."op_materias_x_grados t5","t3.grado_escolar_id=t5.grado_escolar_id")
													->join(DB_PREFIJO."rel_profesores_materias t6","t5.token=t6.materia_token")
													->join(DB_PREFIJO."usuarios t7","t2.acudiente_id=t7.usuario_id");


		$this->db->like("t1.tipo_usuario_id",2);
		if($this->user->tipo_usuario_id>0){
			$this->db->where("docente_id",$this->user->usuario_id);
		}
		if(@$search["value"]){
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
			$return_=[];
			if (get("u")=='') {
		 		$return_ = array(	"data"=>foreach_edit($rows,$count),
		 											"recordsTotal"=>$count,
													"recordsFiltered"=>$count,
		 											"limit"=>$limit);
				$this->result	=	$return_;
			}else {
				$return_movile	=	[];
				$skipUsuario		=	[];
				//foreach (array_reverse($rows) as $key => $value) {
				foreach ($rows as $key => $value) {
					$skipUsuario[]=$value["usuario_id"];
					$data	=	$value;
					switch ($value["tipo_usuario_id"]) {
						/*ALUMNOS*/
						case 2:
							if ($value["genero"]=="masculino") {
								$data["avatar"]	=	'design/nino.png';
							}else {
								$data["avatar"]	=	'design/nina.png';
							}
						break;
						/*PROFESORES*/
						case 4:
							if ($value["genero"]=="masculino") {
								$data["avatar"]	=	'design/profe.png';
							}else {
								$data["avatar"]	=	'design/profesora.png';
							}
						break;
						/*ACUIDIENTES*/
						case 5:
							if ($value["genero"]=="masculino") {
								$data["avatar"]	=	'design/profe.png';
							}else {
								$data["avatar"]	=	'design/profesora.png';
							}
						break;
						/*LOS DEMAS*/
						default:
							$data["avatar"]	=	'design/profe.png';
						break;
					}
					unset($return_["limit"],$return_["recordsFiltered"],$return_["recordsTotal"],$data["acudiente_id"],$data["acudiente_id"],$data["view"],$data["usuario_id"],$data["grado_escolar"],$data["tipo_usuario_id"],$data["genero"]);
					$return_movile[]	=	$data;
				}

				$return_["data"][post("metodo")]	=	$return_movile;
				$this->result	=	$return_;
			}
			return $return_;


			return $return_;
	 	}
	}

	function response(){
		return $this->result;
	}

}
?>
