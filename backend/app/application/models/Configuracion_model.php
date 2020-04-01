<?php
/*
	DESARROLLO Y PROGRAMACIÓN
	PROGRAMANDOWEB.NET
	LCDO. JORGE MENDEZ
	info@programandoweb.net
*/
defined('BASEPATH') OR exit('No direct script access allowed');

class Configuracion_model extends CI_Model {

	var $campos,$result,$message;

	public function campos_listado(){
		return $this->campos	=	[	"tipo_usuario_id"=>"ID",
															"tipo"=>"Tipo de Usuario",
															"estatus"=>"Estado",
															"edit"=>"Acción"];
	}

	public function getSolicitudes(){
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$tabla	=	DB_PREFIJO."op_solicitud_documentos t1";
		$this->db->select("SQL_CALC_FOUND_ROWS solicitud_id", FALSE)
							->select('	solicitud_id,
													t1.token as id,
													t2.*,
													t1.estatus,
													t3.nombres,
													t3.email,
													"prueba" as edit')
							->from($tabla)
							->join(DB_PREFIJO."ma_documentos t2","t1.documento_id=t2.documento_id")
							->join(DB_PREFIJO."usuarios t3","t1.solicitante_id=t3.usuario_id");
		if($search["value"]){
			$this->db->like("t2.documento",$search["value"]);
		}
		if($order){
			$this->db->order_by("t1.solicitud_id");
		}
		if($start && $length){
			$this->db->limit($length,$start);
		}
		$query	=	$this->db->get();
		$total_rows=$this->db->query('SELECT FOUND_ROWS() count;')->row()->count;
		$this->result["data"]		= foreach_edit($query->result_array(),$total_rows);
		$this->result["recordsTotal"]	=	$this->result["recordsFiltered"] =	$total_rows;
		$this->result["draw"]	=	get("draw");
		$this->message=	"";
	}


	public function TodasLasSolicitudes(){
		$tabla	=	DB_PREFIJO."op_solicitud_documentos t1";
		$this->db->select('*')->from($tabla)->join(DB_PREFIJO."ma_documentos t2","t1.documento_id=t2.documento_id")->where("solicitante_id",$this->user->usuario_id);
		$query	=	$this->db->get();
		return $row		= $query->result();
	}

	public function TipoDocumentos(){
		$tabla	=	DB_PREFIJO."ma_documentos";
		$this->db->select('*')->from($tabla);
		$query	=	$this->db->get();
		return $row		= $query->result();
	}

	public function Documento($token){
		$tabla	=	DB_PREFIJO."ma_documentos";
		$this->db->select('*')->from($tabla);
		$this->db->where("token",$token);
		$query	=	$this->db->get();
		return $row		= $query->row();
	}

	public function Documentos(){
		if(get("id")){
			$this->Tipos_de_Usuario(get("id"));
			return;
		}
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$tabla	=	DB_PREFIJO."ma_documentos";
		$this->db->select("SQL_CALC_FOUND_ROWS documento_id", FALSE)
							->select('documento_id,token as id,documento_id,documento,recaudos,token,estatus,"prueba" as edit')->from($tabla);
		if($search["value"]){
			$this->db->like("documento",$search["value"]);
		}
		if($order){
			$this->db->order_by("documento");
		}
		if($start && $length){
			$this->db->limit($length,$start);
		}
		$query	=	$this->db->get();
		$total_rows=$this->db->query('SELECT FOUND_ROWS() count;')->row()->count;
		$this->result["data"]		= foreach_edit($query->result_array(),$total_rows);
		$this->result["recordsTotal"]	=	$this->result["recordsFiltered"] =	$total_rows;
		$this->result["draw"]	=	get("draw");
		$this->message=	"";
	}

	public function SetDocumentos(){
		$post=post();
		$tabla	=	DB_PREFIJO."ma_documentos";
		$this->db->select('*')->from($tabla);
		$this->db->where("token",$post["token"]);
		$query	=	$this->db->get();
		$row		= $query->row();

		$insert	=	[
								"institucion_id"=>$this->user->institucion_id,
								"documento"=>$post["documento"],
								"recaudos"=>$post["recaudos"],
								"token"=>"Document_".token(),
								"estatus"=>$post["estatus"],
							];

		if (empty($row)) {
			$this->db->insert($tabla,$insert);
		}else{
			$this->db->where("token",$row->token);
			$this->db->update($tabla,$insert);
		}
	}

	function Auto_Configuración_Inicial(){

	}

	public function SendInvitacion()	{

		$var		=	array(
							"view"		=>	"invitacion",
							"data"		=>	array(		"email" =>	post("email"),
																			"url" =>	post("url"),
																	));
		$mensaje	=	set_template_mail($var);
		if($mensaje){
			$var		=	array(
								"recipient"	=>	post("email"),
								"subject"		=>	"Invitación de registro en ".SEO_NAME,
								"body"			=>	$mensaje
							);
			$sendmail	=	send_mail($var);
			if(!$sendmail['error']){
				$this->Response 		=			array(	"message_iframe"	=>	"Envío de invitación exitoso al correo:".post("email").", revise su correo electrónico y bandeja de spam o correos no deseados",
																					"code"	=>	"200",
																					"parent" =>  true);
			}else{
				$this->Response 		=			array(	"message_iframe"	=>	"Error, no se puedo enviar la invitación al correo:".post("email").", reintente más tarde",
																					"code"	=>	"203");
			}
		}
		logs($this->Response);
		$this->result	=	$this->Response;
	}

	public function saveHorario(){
		$tabla	=	DB_PREFIJO."op_materias_x_horarios";
		$post	=	post();
		$letras	=	letras();
		$this->db->where("token_materia",$post["token"][0]);
		$this->db->where("institucion_id",$this->user->institucion_id);
		$this->db->where("nivel_educativo_id",end($post["nivel_educativo_id"]));
		$this->db->where("grado_escolar_id",end($post["grado_escolar_id"]));
		$this->db->where("dia",end($post["dia"]));
		$this->db->where("letra",end($post["letra"]));
		$this->db->delete($tabla);

		$insert	=	["institucion_id"	=>	$this->user->institucion_id];
		$json_materias=	$post["dia"];
		/*NIVEL EDUCATIVO*/
		foreach ($json_materias as $key => $value) {
			$insert["dia"]								=	$value;
			$insert["nivel_educativo_id"]	=	$post["nivel_educativo_id"][$key];
			$insert["grado_escolar_id"]		=	$post["grado_escolar_id"][$key];
			$insert["hora_desde"]					=	$post["hora_desde"][$key];
			$insert["hora_hasta"]					=	$post["hora_hasta"][$key];
			$insert["letra"]					=	$post["letra"][$key];
			$insert["token_materia"]			=	$post["token"][0];
			$insert["token"]							=	md5($post["nivel_educativo_id"][$key].$post["grado_escolar_id"][$key].$post["hora_desde"][$key].$post["hora_hasta"][$key].$post["token"][0].$post["letra"][$key]);
			$insert["estatus"]						=	1;
			$this->db->insert($tabla,$insert);
		}
		$this->result["message"]	=	"Guardado";
	}

	public function SetConfiguracionMaterias(){
		$tabla	=	DB_PREFIJO."op_materias_x_grados";
		$post	=	post();
		$letras=letras();
		$this->db->where("institucion_id",$this->user->institucion_id);
		$this->db->delete($tabla);
		$insert	=	["institucion_id"	=>	$this->user->institucion_id];
		$json_materias=	$post["json"]["materia"];
		//pre($json_materias,false);
		/*NIVEL EDUCATIVO*/
		foreach ($json_materias as $key => $value) {
			//pre($json_materias[$key],false);
			/*NIVEL GRADO ESCOLAR*/
			foreach ($value as $key2 => $value2) {
				//pre($json_materias[$key][$key2],false);
				$contador=1;
				if($value2>"0"){
					//pre($value2,false);
					foreach ($value2 as $key3 => $value3) {
						$insert["materia"]	=	$value3;
						$insert["nivel_educativo_id"]	=	$key;
						$insert["grado_escolar_id"]	=	$key2;
						$insert["token"]	=	md5($value3.$key.$key2.date("Y"));
						$insert["estatus"]	=	1;
						$this->db->insert($tabla,$insert);
					}
				}
			}
		}
	}

	public function SetConfiguracionSalones(){
		$tabla	=	DB_PREFIJO."op_grados_x_instituciones";
		$post	=	post();
		$letras=letras();
		$this->db->where("institucion_id",$this->user->institucion_id);
		$this->db->delete($tabla);
		$insert	=	["institucion_id"	=>	$this->user->institucion_id];
		foreach ($post["grados"] as $key => $value) {
			foreach ($value as $key2 => $value2) {
				//pre($value2,false);
				$contador=1;
				if($value2>"0"){
					for ($i=0; $i < $value2 ; $i++) {
						if(!empty($value2)){
							$insert["letra"]	=	$letras[$contador];
							$insert["nivel_educativo_id"]	=	$key;
							$insert["grado_escolar_id"]	=	$key2;
							$insert["token"]	=	md5($letras[$contador].$key.$key2);
							$insert["estatus"]	=	1;
							$consulta_grados=consulta_grados(	$this->user->institucion_id,
							$letras[$contador],
							$key,
							$key2);
							$contador++;
							if (empty($consulta_grados)) {
								$this->db->insert($tabla,$insert);
							}else {
								unset($insert["token"]);
								$this->db->where("institucion_id",$this->user->institucion_id)
								->where("letra",$insert["letra"])
								->where("nivel_educativo_id",$insert["nivel_educativo_id"])
								->where("grado_escolar_id",$insert["grado_escolar_id"]);
								$this->db->update($tabla,$insert);
							}
						}
					}
				}else{
					$this->db->where("institucion_id",$this->user->institucion_id)
										->where("letra",$letras[$contador])
										->where("nivel_educativo_id",$key)
										->where("grado_escolar_id",$key2);
					$this->db->delete($tabla);
				}
			}
		}
	}

	public function Tipos_de_Usuarios(){
		if(get("id")){
			$this->Tipos_de_Usuario(get("id"));
			return;
		}
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$tabla	=	DB_PREFIJO."tipo_usuarios";
		$this->db->select("SQL_CALC_FOUND_ROWS tipo_usuario_id", FALSE)
							->select('tipo_usuario_id,token as id,tipo,estatus,"prueba" as edit')->from($tabla);
		if($search["value"]){
			$this->db->like("tipo",$search["value"]);
		}
		if($order){
			$this->db->order_by("tipo");
		}
		if($start && $length){
			$this->db->limit($length,$start);
		}
		$query	=	$this->db->get();
		$total_rows=$this->db->query('SELECT FOUND_ROWS() count;')->row()->count;
		$this->result["data"]		= foreach_edit($query->result_array(),$total_rows);
		$this->result["recordsTotal"]	=	$this->result["recordsFiltered"] =	$total_rows;
		$this->result["draw"]	=	get("draw");
		$this->message=	"";
	}

	function Tipos_de_Usuario($id){
		$tabla	=	DB_PREFIJO."tipo_usuarios";
		$this->db->select('tipo_usuario_id,tipo,estatus,privilegios')->from($tabla);
		$this->db->where("tipo_usuario_id>",0);
		$this->db->where("tipo_usuario_id",$id);
		$this->db->or_where("token",$id);
		$query	=	$this->db->get();
		$this->result	= $query->row();
		$this->message=	"";
	}

	function SetGrupo_de_Usuarios(){
		$var	=	post();
		unset($var["redirect"]);
		$row	=	$this->db->select('tipo_usuario_id,tipo,estatus')
								->from(DB_PREFIJO."tipo_usuarios")
								->where('tipo_usuario_id',$var["tipo_usuario_id"])
								->get()
								->row();

		$fields = $this->db->field_data(DB_PREFIJO."tipo_usuarios");
		$unbind	=	[];
		foreach ($fields as $field){
			if($field->primary_key){
				$unbind[$field->name]=1;
			}
		}
		$var["privilegios"]		=	menu_encode($var);
		unset($var["ver"]);
		if(empty($row)){
			unset($var['tipo_usuario_id']);
			$sql = $this->db->set($var)->get_compiled_insert(DB_PREFIJO."tipo_usuarios");
			$sql = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $sql);
			if($this->db->query($sql)){
				$this->result["unbind"]	=	$unbind;
				return $this->message="Tipo de usuario agregado";
			}else{
				return $this->message	= $this->db->error()["message"];
			}
		}else{
			$row	=	$this->db->select('tipo_usuario_id,tipo,estatus')
									->from(DB_PREFIJO."tipo_usuarios")
									->where('tipo',$var["tipo"])
									->where('tipo_usuario_id<>',$var["tipo_usuario_id"])
									->get()
									->row();
			if(empty($row)){
				$this->db->where('tipo_usuario_id',$var["tipo_usuario_id"]);
				unset($var['tipo_usuario_id']);
				if($this->db->update(DB_PREFIJO."tipo_usuarios",$var)){
					$this->result["unbind"]	=	$unbind;
					return $this->message= "Tipo de usuario actualizado";
				}else{
					return $this->message= $this->db->error()["message"];
				}
			}else{
				return $this->message=	"Tipo de usuario duplicado, no pudimos actualizar";
			}
		}
	}

	function response(){
		return $this->result;
	}

}
?>
