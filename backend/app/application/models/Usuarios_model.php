<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usuarios_model extends CI_Model {

	var $fields,$result,$where,$total_rows,$pagination,$search,$response,$campos,$message;

	public function  campos_listado(){
		$this->campos	=	[	"usuario_id"=>"Usuario ID",
											"nombres"=>"Nombres y Apellidos"];
	}

	public function exc_List_Usuarios(){
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$tabla	=	DB_PREFIJO."usuarios t1";
		$tabla2	=	DB_PREFIJO."tipo_usuarios t2";
		$tabla3	=	DB_PREFIJO."rel_alumnos_instituciones t3";
		$tabla4	=	DB_PREFIJO."usuarios t4";
		$tabla5	=	DB_PREFIJO."op_grados_x_instituciones t5";
		$tabla6	=	DB_PREFIJO."ma_grados_escolares t6";
		$tabla7	=	DB_PREFIJO."op_alumnos_notas t7";



		if( $this->user->tipo_usuario_id==0 && get("section")==''){
			$this->db->select("SQL_CALC_FOUND_ROWS t1.usuario_id", FALSE)
								->select('tipo,
													t1.nombres,
													IF(t4.nombres!="", t4.nombres, "NO REGISTRADO") as representante,
													t1.institucion_id,
													t1.login,
													t1.login as login_link,
													t1.usuario_id,
													t1.identificacion,
													t1.tipo_usuario_id,
													t1.token as id,"prueba" as edit_setuser')
								->from($tabla)
								->join($tabla2,"t2.tipo_usuario_id=t1.tipo_usuario_id","left")
								->join($tabla3,"t1.usuario_id=t3.usuario_id","left")
								->join($tabla4,"t3.acudiente_id=t4.usuario_id","left");
		}else{
			switch (get("t")) {
				case 5:
				$this->db->select("SQL_CALC_FOUND_ROWS t1.usuario_id", FALSE)
									->select('tipo,
														t1.nombres,
														IF(t4.nombres!="", t4.nombres, "NO REGISTRADO") as representado,
														t1.institucion_id,
														t4.identificacion as codigo_acudiente,
														t1.usuario_id,
														t1.login,
														t4.login as login_link_representante,
														t4.login as login_link,
														t1.identificacion,
														t1.tipo_usuario_id,
														t1.token as id,
														"prueba" as edit')
									->from($tabla)
									->join($tabla2,"t2.tipo_usuario_id=t1.tipo_usuario_id","left")
									->join($tabla3,"t1.usuario_id=t3.acudiente_id","left")
									->join($tabla4,"t3.usuario_id=t4.usuario_id","left");

				if(get("institucion_id")!=''){
					$this->db->where("t1.institucion_id",get("institucion_id"));
				}
				if(get("genero")!=''){
					$this->db->where("t1.genero",get("genero"));
				}

				if(get("edad_desde")!='' && get("edad_hasta")==''){
					$desde	=	(date("Y")-get("edad_desde")).date("-m-d");
					$this->db->where("t1.fecha_nacimiento>=",$desde);
				}else if(get("edad_desde")!='' && get("edad_hasta")!=''){
					$desde	=	(date("Y")-get("edad_desde")).date("-m-d");
					$hasta	=	(date("Y")-get("edad_hasta")).date("-m-d");
					$this->db->where("t1.fecha_nacimiento<=",$desde);
					$this->db->where("t1.fecha_nacimiento>=",$hasta);
					//echo 5;
				}
				break;
				case "5.2":
				$this->db->select("SQL_CALC_FOUND_ROWS t1.usuario_id", FALSE)
									->select('tipo,
														t1.nombres,
														IF(t4.nombres!="", t4.nombres, "NO REGISTRADO") as representado,
														t1.institucion_id,
														t1.identificacion as codigo_acudiente,
														t1.usuario_id,
														t1.login,
														t4.login as login_link_representante,
														t4.login as login_link,
														t1.identificacion,
														t1.tipo_usuario_id,
														t1.token as id,
														"prueba" as edit')
									->from($tabla)
									->join($tabla2,"t2.tipo_usuario_id=t1.tipo_usuario_id","left")
									->join($tabla3,"t1.usuario_id=t3.acudiente_id","right")
									->join($tabla4,"t3.usuario_id=t4.usuario_id","left");

				if(get("institucion_id")!=''){
					$this->db->where("t1.institucion_id",get("institucion_id"));
				}
				if(get("genero")!=''){
					$this->db->where("t1.genero",get("genero"));
				}

				if(get("edad_desde")!='' && get("edad_hasta")==''){
					$desde	=	(date("Y")-get("edad_desde")).date("-m-d");
					$this->db->where("t1.fecha_nacimiento>=",$desde);
				}else if(get("edad_desde")!='' && get("edad_hasta")!=''){
					$desde	=	(date("Y")-get("edad_desde")).date("-m-d");
					$hasta	=	(date("Y")-get("edad_hasta")).date("-m-d");
					$this->db->where("t1.fecha_nacimiento<=",$desde);
					$this->db->where("t1.fecha_nacimiento>=",$hasta);
					//echo 5;
				}
				break;
				case 4:
				$this->db->select("SQL_CALC_FOUND_ROWS t1.usuario_id", FALSE)
									->select('tipo,
														t1.nombres,
														IF(t4.nombres!="", t4.nombres, "NO REGISTRADO") as representado,
														t1.institucion_id,
														t1.usuario_id,
														t1.login,
														t4.login as login_link_representante,
														t4.login as login_link,
														t1.identificacion,
														t1.tipo_usuario_id,
														t1.token as id,
														"prueba" as edit')
									->from($tabla)
									->join($tabla2,"t2.tipo_usuario_id=t1.tipo_usuario_id","left")
									->join($tabla3,"t1.usuario_id=t3.acudiente_id","left")
									->join($tabla4,"t3.usuario_id=t4.usuario_id","left");
				if(get("institucion_id")!=''){
					$this->db->where("t1.institucion_id",get("institucion_id"));
				}
				if(get("genero")!=''){
					$this->db->where("t1.genero",get("genero"));
				}
				break;
				case 2:
				$this->db->select("SQL_CALC_FOUND_ROWS t1.usuario_id", FALSE)
									->select('tipo,
														t1.nombres,
														IF(t4.nombres!="", t4.nombres, "NO REGISTRADO") as representado,
														IF(t4.nombres!="", t4.nombres, "NO REGISTRADO") as representante,
														t1.institucion_id,
														t1.usuario_id,
														t1.login,
														t4.login as login_link_representante,
														t1.login as login_link,
														t1.identificacion,
														t1.tipo_usuario_id,
														t1.token as id,
														"prueba" as edit')
									->from($tabla)
									->join($tabla2,"t2.tipo_usuario_id=t1.tipo_usuario_id","left")
									->join($tabla3,"t1.usuario_id=t3.usuario_id","left")
									->join($tabla4,"t3.acudiente_id=t4.usuario_id","left");

				if(get("grado_escolar_id")!=''){
					$this->db->join($tabla5,"t3.grado_escolar_token=t5.token","left");
					$this->db->join($tabla6,"t5.grado_escolar_id=t6.grado_escolar_id","left");
					$this->db->where("t6.grado_escolar_id",get("grado_escolar_id"));
				}

				// if(get("notas_desde")!=''){
				// 	$this->db->join($tabla7,"t1.usuario_id=t7.alumno_id","left");
				// 	$this->db->where("t6.grado_escolar_id",get("grado_escolar_id"));
				// }

				if(get("institucion_id")!=''){
					$this->db->where("t1.institucion_id",get("institucion_id"));
				}
				if(get("genero")!=''){
					$this->db->where("t1.genero",get("genero"));
				}
				if(get("edad_desde")!='' && get("edad_hasta")==''){
					$desde	=	(date("Y")-get("edad_desde")).date("-m-d");
					$this->db->where("t1.fecha_nacimiento>=",$desde);
				}else if(get("edad_desde")!='' && get("edad_hasta")!=''){
					$desde	=	(date("Y")-get("edad_desde")).date("-m-d");
					$hasta	=	(date("Y")-get("edad_hasta")).date("-m-d");
					$this->db->where("t1.fecha_nacimiento<=",$desde);
					$this->db->where("t1.fecha_nacimiento>=",$hasta);
					//echo 5;
				}

				break;
				default:
					$this->db->select("SQL_CALC_FOUND_ROWS t1.usuario_id", FALSE)
										->select('tipo,
															t1.nombres,
															IF(t4.nombres!="", t4.nombres, "NO REGISTRADO") as representante,
															t1.institucion_id,
															t1.usuario_id,
															t1.login,
															t1.login as login_link,
															t1.identificacion,
															t1.tipo_usuario_id,
															t1.token as id,
															"prueba" as edit')
										->from($tabla)
										->join($tabla2,"t2.tipo_usuario_id=t1.tipo_usuario_id","left")
										->join($tabla3,"t1.usuario_id=t3.usuario_id","left")
										->join($tabla4,"t3.acudiente_id=t4.usuario_id","left");
				break;
			}

		}

		$this->db->where("t1.tipo_usuario_id>",0);
		if($this->user->tipo_usuario_id>0){
			$this->db->where("t1.institucion_id",$this->user->institucion_id);
		}
		if(get("t")!='' && get("t")!='5.2'){
			$this->db->where("t1.tipo_usuario_id",get("t"));
		}else if(get("t")=='5.2') {
			$this->db->where("t1.tipo_usuario_id",5);
		}
		if($search["value"]){
			$this->db->like("t1.nombres",$search["value"]);
			$this->db->or_like("t1.identificacion",$search["value"]);
			if ($this->user->tipo_usuario_id==0) {
				$this->db->or_like("tipo",$search["value"]);
			}
		}
		if($order){
			$this->db->order_by("t1.nombres");
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

	public function setSessionTemporal(){
		if (get("action")=='delete') {
			$Temporal = $this->session->userdata("Temporal");
			$this->session->unset_userdata('Temporal');
			redirect($Temporal->url_referencia);
		}else {
			$insert		=	new stdClass;
			$insert->url_referencia	=	$this->agent->referrer();
			$insert->controller	=	get("controller");
			$insert->me	=	get("me");
			$insert->view	=	get("view");
			$insert->id	=	get("id");
			$insert->extra	=	get("extra");
			$config	=	$this->session->set_userdata(['Temporal'  => $insert]);
			$url		=		"Apanel/".$insert->controller."?";
			$url		.=	(!empty($insert->me))?"m=".$insert->me:"";
			$url		.=	(!empty($insert->view))?"&view=".$insert->me:"";
			$url		.=	(!empty($insert->id))?"&id=".$insert->id:"";
			$url		.=	(!empty($insert->extra))?"&extra=".$insert->extra:"";
			if(get("message")){
				$this->session->set_flashdata('danger',get("message"));
			}
			redirect(base_url($url));
		}
	}

	public function confirm(){
		$this->db->where("token",get("token"));
		$this->db->update(DB_PREFIJO."usuarios",["estatus"=>1]);
		$this->session->set_flashdata('success',"Confirmación de usuario en ".SEO_NAME.", inicie sesión.");
		redirect(base_url("Autenticacion/login"));
	}

	public function registro_de_usuario_nuevo(){
		$post=post();
		//pre($post,false);
		list($login,$dominio)	=	explode("@",$post["email"]);
		$usuarios_x_login=usuarios_x_login($post);
		$identificacion 	= 	strtoupper(substr("R".token(),0,12));
		if(empty($usuarios_x_login)){
			$token	=	token();
			$insert	=	[	"nombres"=>$post["nombres"],
									"email"=>$post["email"],
									"login"=>$login,
									"password"=>md5($post["telefono"]),
									"estatus"=>1,
									"tipo_usuario_id"=>$post["tipo_usuario_id"],
									"institucion_id"=>$this->user->institucion_id,
									"identificacion"=>$identificacion,
									"cedula"=>($post["cedula"])?$post["cedula"]:$identificacion,
									"telefono"=>$post["telefono"],
									"token"=>$token,
									"json"=>json_encode($post["json"]),
									];
			$this->db->insert(DB_PREFIJO."usuarios",$insert);
			/*PENDIENTE ENVÍO DE CORREO DE CONFIRMACIÓN*/
		}
	}

	public function __register(){
		if(get("token")){
			return $this->confirm(get("token"));
		}
		$post=post();
		$usuarios_x_login=usuarios_x_login($post);
		list($login,$dominio)	=	explode("@",$post["email"]);
		//pre($usuarios_x_login,false);
		if(empty($usuarios_x_login)){
			$token	=	token();
			$insert	=	[	"nombres"=>$post["nombres"],
									"email"=>$post["email"],
									"login"=>$login,
									"password"=>md5($post["password"]),
									"estatus"=>1,
									"tipo_usuario_id"=>1,
									"token"=>$token,
									];

			if ($this->db->insert(DB_PREFIJO."usuarios",$insert)) {
				$lastId = $this->db->insert_id();
				/*FALTA ACÁ */
				$url								=	base_url("ApiRest/post?modulo=Usuarios&m=register&formato=json&token=".$token);
				$vars["body"]				= set_template_mail(["view"=>"register","data"=>["url"=>$url,"usuario"=>$post["nombres"].' ('.$login.') ']]);
				$vars["recipient"]	=	EMAIL_ADMIN;
				$vars["subject"]		=	"Registro de usuario";

				$vars["body"];
				$send_mail	=	send_mail($vars);
				if(empty($send_mail["error"])){
					$this->session->set_flashdata('success',"Registro de usuario en ".SEO_NAME.", revise su correo y confirme su solicitud, revise también su bandeja de correos no deseados.");
				}else{
					$this->session->set_flashdata('error',"Ocurrió un error al tratar de enviar el email de confirmación");
				}
			}
			$usuario=usuarios($lastId);
			$genera_token=genera_token(0);
			$usuario->session_id=$genera_token;
			$this->set_session_login($usuario);
			//pre($usuario);
			redirect(base_url("Apanel"));exit;
		}
	}

	public function register(){
		if(get("token")){
			return $this->confirm(get("token"));
		}
		$post=post();
		$usuario=usuarios_x_code(strtoupper($post["codigo"]),$post["institucion_id"]);
		if (!empty($usuario) && $usuario->tipo_usuario_id==$post["tipo_usuario_id"])  {
			$this->db->where("usuario_id",$usuario->usuario_id);
			//$this->db->update(DB_PREFIJO."usuarios",["email"=>$post["email"],"estatus"=>1,"password"=>md5($post["password"])]);
			$this->db->update(DB_PREFIJO."usuarios",["estatus"=>1,"password"=>md5($post["password"])]);
			logs("Registro del usuario:".$usuario->nombres." - ".@$post["email"]." con el código:".$post["codigo"]." institucion_id: ".$post["institucion_id"]);
			$usuario=usuarios($usuario->usuario_id);
			$genera_token	=	genera_token(0);
			$usuario->session_id=$genera_token;
			$this->set_session_login($usuario);
			redirect(base_url("Apanel"));exit;
		}else if (!empty($usuario) && $usuario->tipo_usuario_id!=$post["tipo_usuario_id"])  {
			logs("Error de registro del usuario:".$usuario->nombres." - ".$post["email"]." con el código:".$post["codigo"]." institucion_id: ".$post["institucion_id"]." El tipo de usuario no corresponde: su registro en la base de datos -> ".$usuario->tipo_usuario_id." y el registro que seleccionó ".$post["tipo_usuario_id"]);
			redirect(base_url("Apanel"));exit;
		}else {
			logs("Error registrando al usuario ".$post["email"]." con el código:".$post["codigo"]." institucion_id: ".$post["institucion_id"]);
			redirect($_SERVER['HTTP_REFERER']."?error=Sus datos no existen en el sistema, consulte con la institución");exit;
		}

	}


	public function recover(){
		$post=post();
		$usuarios_x_login=usuarios_x_login($post);

		if(!empty($usuarios_x_login)){
			$token		=	token();
			$password	=	substr($token,8);
			$insert	=	[
									"password"=>md5($password),
									"token"=>$token,
								];

			$this->db->where("token",get("token"));
			if ($this->db->update(DB_PREFIJO."usuarios",$insert)) {
				/*FALTA ACÁ */
				$vars["body"]				= set_template_mail(["view"=>"recover","data"=>["password"=>$password,]]);
				$vars["recipient"]	=	EMAIL_ADMIN;
				$vars["subject"]		=	"Generación de clave personal";
				$vars["body"];
				$send_mail	=	send_mail($vars);
				if(empty($send_mail["error"])){
					$this->session->set_flashdata('success',"Solicitud de nueva clave en ".SEO_NAME.", revise su correo y confirme su solicitud, revise también su bandeja de correos no deseados.");
				}else{
					$this->session->set_flashdata('error',"Ocurrió un error al tratar de enviar el email de confirmación");
				}
			}
			redirect(base_url("Autenticacion/login"));
		}
	}

	public function GetUsuarios(){
		$tabla	=	DB_PREFIJO."usuarios";
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

	function SetUsuario(){
		$tabla1	=	DB_PREFIJO."usuarios";
		/*recojo datos post*/
		$insert[$tabla1]	=	[];
		foreach (campos_tabla($tabla1) as $value) {
			if(!empty(post($value))){
				if($value=='json'){
					$insert[$tabla1][$value]	=	json_encode(post($value));
				}else if($value=='password'){
					$insert[$tabla1][$value]	=	md5($this->input->post("password",false));
				}else{
					$insert[$tabla1][$value]	=	post($value);
				}
			}
		}

		$insert[$tabla1]["json"]	=	json_encode(post("json"));
		$insert[$tabla1]["token"]	=	token();
		/*fin recoleccion*/

		/*Verifico existencia de colegio y usuario: creo o edito*/
		$institucion_id	=	update_data("usuario_id",$tabla1,["usuario_id"],post(),$insert[$tabla1]);
		$this->message=	"";
	}

	public function setPerfil(){
		//pre($this->user);
		$this->db->where("usuario_id",$this->user->usuario_id);
		$this->db->update(DB_PREFIJO."usuarios",array("ultimo_perfil_activo"=>get("id")));
		$this->user->ultimo_perfil_activo=get("id");
		$this->set_session_login($this->user);
		//$this->result["user"]	=	$this->user;
		$this->result["response"]	=	perfil_seleccionado(get("id"));
	}

	public function SearchUser(){
		$list		=		get("list",false);
		$query	=		get("query");
		$tabla 	= 	DB_PREFIJO."usuarios t1";
		$this->db->select("login");
		$this->db->from($tabla);
		$this->db->where('login',$query);
		$this->db->or_where('email',$query);
		if($list){
			$rows=$this->db->get()->result();
			$this->message=	"";
			return (!empty($rows)?array("response"=>$rows):array("response"=>"NULL"));
		}else{
			$row=$this->db->get()->row();
			if(!empty($row) && is_object($row)){
				$this->message	=	"No disponible";
				$this->result["response"]["login"]	=	$query;
				$this->result["response"]["success"]	=	0;
			}else{
				$this->message	=	"Disponible";
				$this->result["response"]["login"]	=	$query;
				$this->result["response"]["success"]	=	1;
			}
			return (!empty($row)?array("response"=>$row):array("response"=>"NULL"));
		}
	}

	public function logout(){
		$this->user=$this->session->userdata('User');
		destruye_session($this->user);
		$this->session->unset_userdata('User');
		$this->session->sess_destroy();
		if ($this->input->is_ajax_request()) {
			return array(	"message"=>"Hasta pronto, ".SEO_TITLE,
										"redirect"=>base_url("Autenticacion/login"));
		}else{
			redirect(base_url());
		}
	}

	public function login(){
		$return	=	array();
		$data		=	$this->db->select("	usuario_id,
																	TO_BASE64(tipo_usuario_id) as access,
																	tipo_usuario_id,
																	CASE
																		WHEN tipo_usuario_id = 1 THEN 'Rectores'
																		WHEN tipo_usuario_id = 2 THEN 'Alumnos'
																		WHEN tipo_usuario_id = 3 THEN 'Administrativos'
																		WHEN tipo_usuario_id = 4 THEN 'Profesores'
																		WHEN tipo_usuario_id = 5 THEN 'Acudientes'
																		WHEN tipo_usuario_id = 6 THEN 'Secretaria'
																		WHEN tipo_usuario_id = 7 THEN 'Psicorientador'
																		ELSE 'Profesores'
																	END as usuarios,
																	nombres,
																	apellidos,
																	email,
																	telefono,
																	login,
																	password,
																	estatus,
																	ultimo_perfil_activo,
																	institucion_id")
												->from(DB_PREFIJO."usuarios")
												->where("login",post("login"))
												->or_where("email",post("login"))
												->or_where("telefono",post("login"))
												->get()
												->row();

		if(!empty($data)){
			/*ESTE SCRIPT ES PARA ESTABLECER LA PRIMERA CONTRASEÑA*/
			if($data->password=='NULL' || $data->password==md5(post('password'))){
				$this->db->where("usuario_id",$data->usuario_id);
				$data->password = encriptar(post('password'));
				$update['password'] = $data->password;
				$this->db->update(DB_PREFIJO."usuarios",$update);
			}

			if(desencriptar($data->password)==post('password')){
				if($data->estatus==0){
						return array("message"=>"Error: Esta cuenta se encuentra inactiva, consulte con el administrador");
				}
				$session  = $this->db->select('*')
															->from(DB_PREFIJO."sesiones")
															->where('usuario_id',$data->usuario_id)
															->get()
															->row();
				if(empty($session) || 1==1){
					//usuario_id=0;
					$genera_token=genera_token($data->usuario_id);
					unset($data->password);
					if($genera_token){
						$data->session_id=$genera_token;
						$this->set_session_login($data);
						$data->token	=	$genera_token;
						$this->result["store"]["user"]			=	$data;
						$this->result["store"]["token"]			=	$genera_token;
						$this->result["redirect"]						=	base_url("Apanel");
						$this->message											=	"";
					}else{
						return false;
					}
				}else{
					$genera_token=genera_token($data->usuario_id);
					if($genera_token){
						$this->set_session_login($data);
						$data->token	=	$genera_token;
						$this->result["store"]["user"]			=	$data;
						$this->result["store"]["token"]			=	$genera_token;
						$this->result["redirect"]						=	base_url("Apanel");
						$this->message											=	"Ya existe otra sesión abierta con este usuario y será eliminada";
					}else{
						$this->result["store"]["user"]			=	"null";
						$this->message	=	 "Ha ocurrido un error por favor contacte al administrador de sistemas";
					}
				}
			}else{
					$this->result["store"]["user"]			=	"null";
	        $this->message	=	 "La contraseña es incorrecta";
	    }
		}else{
			$this->result["store"]["user"]			=	"null";
			$this->message	=	 "Error usuario no existe o clave es incorrecta";
		}
	}

	private function set_session_login($data){
		$this->session->set_userdata(array('User'=>$data));
	}

	public function get_all2(){
		$tabla="mae_cliente_joberp t1";
		$tabla2="usuarios t2";
		$tabla3=  "sys_roles t3";
		$this->db->select('t1.*,t2.*, t3.*')->from($tabla)
							->join($tabla2,"t1.institucion_id = t2.institucion_id","left")
							->join($tabla3,"t2.tipo_usuario_id = t3.tipo_usuario_id","left")
							->where("t2.estatus",1);
		if($this->user->tipo_usuario_id <> 1){
			$this->db->where("t1.institucion_id",$this->user->institucion_id);
		}
		$this->result["Activos"]=$this->db->get()->result();

		$tabla=  "mae_cliente_joberp t1";
		$tabla2	="usuarios t2";
		$tabla3=  "sys_roles t3";
		$this->db->select('t1.*,t2.*, t3.*')->from($tabla)
							->join($tabla2,"t1.institucion_id = t2.institucion_id","left")
							->join($tabla3,"t2.tipo_usuario_id = t3.tipo_usuario_id","left")
							->where("t2.estatus",0);
		if($this->user->tipo_usuario_id <> 1){
			$this->db->where("t1.institucion_id",$this->user->institucion_id);
		}
		$this->result["Inactivos"]=$this->db->get()->result();
	}

	function response(){
		return $this->result;
	}
}
?>
