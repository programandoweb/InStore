<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat_model extends CI_Model {

	var $fields,$result,$where,$total_rows,$pagination,$search,$response,$campos,$message;

	public function registerForPushNotifications(){
		/*VOY A REGISTRAR EL PUSH TOKEN EN AMBOS LUGARES*/
		$this->db->where("token",get("u"));
		$this->db->update(DB_PREFIJO."sesiones",["push_token"=>post("push_token")]);

		$this->db->where("usuario_id",$this->user->usuario_id);
		$this->db->update(DB_PREFIJO."usuarios",["push_token"=>post("push_token")]);
		echo json_encode([post()]);exit;
	}

	public function socketJavacript(){
		$post=json_encode(post());
		if (empty(post())) {
			$post=json_encode(["Dato1"=>1,"Dato2"=>2]);
		}
	?>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.3.0/socket.io.js"></script>
		<script type="text/javascript">
						const port 		= "<?php echo SOCKET_PORT ?>";
						let ipAddress = "<?php echo SOCKET_SERVER ?>";
						const socketIoAddress = `https://${ipAddress}:${port}`;
						socket	= io(socketIoAddress);
						socket.emit("recarga_nuevos_datos",<?php echo $post ?>);
		</script>
	<?php
	}

	public function  campos_listado(){
		$this->campos	=	[	"usuario_id"=>"Usuario ID",
											"nombres"=>"Nombres y Apellidos"];
	}

	private function buscar_nombre_usuario($nombre_usuario){
		return	$this->db->select("*")
											->from(DB_PREFIJO."usuarios")
											->like("login",$nombre_usuario)
											->get()
											->row();
	}

	public function GetComments(){
		$tabla1	=	DB_PREFIJO."op_chats_mensajes t1";
		$rows		=	$this->db->select(' *	,	DATE_FORMAT(fecha, "%d/%m/%Y") as fecha')
																					->from($tabla1)
																					->join(DB_PREFIJO."usuarios t2","t1.emisor_id=t2.usuario_id","left")
																					->where("t1.token",get("id"))
																					->get()->result();
		$this->result["mensajes"]	=	(!empty($rows))?$rows:[];
	}

	public function SetComments(){
		$insert	=	[
								"fecha"=>date("Y-m-d"),
								"mensaje"=>post("mensaje"),
								"emisor_id"=>$this->user->usuario_id,
								"receptor_id"=>0,
								"estatus"=>0,
								"token"=>post("token_commentario"),
								"mensaje_token"=>"App_comment_".token(),
		];
		$this->db->insert(DB_PREFIJO."op_chats_mensajes",$insert);
		if (post("callBack")) {
			$this->result["callBack"]	=	post("callBack");
		}
		$insert["nombres"]	=	$this->user->nombres;
		$insert["fecha"]		=	fecha($insert["fecha"],"d/m/Y");
		$this->result["data"]	=	$insert;
	}

	public function crearSolicitud(){
		$post	=	post();
		$row	=	$this->db->select("*")
												->from(DB_PREFIJO."op_solicitud_documentos")
												->like("token",$post["token"])
												->get()
												->row();
		$insert	=	[
									"fecha_solicitud"=>date("Y-m-d H:i:s"),
									"solicitante_id"=>$this->user->usuario_id,
									"institucion_id"=>$this->user->institucion_id,
									"documento_id"=>$post["documento_id"],
									"observacion"=>$post["observacion"],
									"estatus"=>0,
									"token"=>"Solicitud_".token(),
								];

		if (empty($row)) {
			$this->db->insert(DB_PREFIJO."op_solicitud_documentos",$insert);
		}else {
			if ($this->user->usuario_id!=$row->solicitante_id) {
				if (isset($post["estatus"]) && $post["estatus"]==1) {
					$insert["fecha_aceptada"]=date("Y-m-d H:i:s");
				}else if(isset($post["estatus"]) && $post["estatus"]==2){
					$insert["fecha_completada"]=date("Y-m-d H:i:s");
				}
			}
			$this->db->where("token",$row->token);
			$this->db->update(DB_PREFIJO."op_solicitud_documentos",$insert);
		}
	}

	public function DetailNotification(){
		$data=[];
		$tabla1=	DB_PREFIJO."op_notificacion t1";
		$tabla2=	DB_PREFIJO."usuarios t2";
		$rows	=		$this->db->select("	t1.notificacion,
																t1.fecha,
																t1.token,
																t1.estatus,
																t1.json,
																t1.notificacion_id,
																t3.nombres as nombre_emisor,
																t3.usuario_id as emisor_id,
																t4.nombres as nombre_receptor,
																t4.usuario_id as receptor_id")
											->from($tabla1)
											->join(DB_PREFIJO."usuarios t3","t1.emisor_id=t3.usuario_id","left")
											->join(DB_PREFIJO."usuarios t4","t1.usuario_id=t4.usuario_id","left")
											->where("t1.token",get("id"))
											->get()
											->row();

		$rows=campo_json_db($rows);
		$obj	=	new stdClass;
		$obj->tabla		=	"op_notificacion";
		$obj->key			=	"notificacion_id";
		$obj->value		=	$rows->notificacion_id;
		$notificacion	=	busqueda_dinamica($obj);
		$this->db->where($obj->key,$obj->value);
		$this->db->update(DB_PREFIJO."".$obj->tabla,["estatus"=>1]);
		echo tmpl("chat/DetailNotification",$notificacion);
	}

	private function buscar_grupo($token){
		return	$this->db->select("*")
											->from(DB_PREFIJO."op_chats_grupos")
											->like("token",$token)
											->get()
											->row();
	}

	private function usuario_grupo_exists($token,$usuario_id){
		$row=$this->db->select("*")
											->from(DB_PREFIJO."op_chats_grupos")
											->like("token",$token)
											->where("usuario_id",$usuario_id)
											->get()
											->row();
		if (empty($row)) {
			return false;
		}else {
			return $row;
		}
	}

	public function agregarusuarioGrupos(){
		$post=post();
		$yo					=		$this->user;
		$usuario		=		$this->buscar_nombre_usuario($post["usuario"]);
		//p($usuario);
		$grupos			=		$this->buscar_grupo($post["ventana"]);
		$usuario_grupo_exists			=		$this->usuario_grupo_exists($post["ventana"],$usuario->usuario_id);
		if (empty($usuario)) {
			$this->result["ventana"]	=	["ventana"=>post("ventana")];
			$this->result["message"]	=	"Usuario no se encuentra en la base de datos";
			$this->result["crear_usuario"]	=	"NO";
			return;
		}else if(!empty($usuario) && !$usuario_grupo_exists ){
			$insert	=	[
										"fecha"=>date("Y-m-d H:i:s"),
										"usuario_id"=>$usuario->usuario_id,
										"grupo"=>$grupos->grupo,
										"fecha"=>date("Y-m-d H:i:s"),
										"estatus"=>1,
										"token"=>$post["ventana"],
									];
			$this->db->insert(DB_PREFIJO."op_chats_grupos",$insert);

			$ventana						=	$this->amigos_x_token_ventana($post["ventana"]);
			$lista_amigos_grupo	=	$this->lista_amigos_grupo($ventana->token);

			if (!empty($lista_amigos_grupo)) {
				$mensaje_bienvenida="message_".token();
				foreach ($lista_amigos_grupo as $key => $value) {
					$this->db->insert(DB_PREFIJO."op_chats_mensajes",[
																															"reply"=>0,
																															"fecha"=>date("Y-m-d H:i:s"),
																															"mensaje"=>"Fue agregado ".$usuario->login." al grupo ".$grupos->grupo." por ".$yo->login,
																															"emisor_id"=>$yo->usuario_id,
																															"receptor_id"=>$value->usuario_id,
																															"estatus"=>0,
																															"attachment"=>0,
																															"ogg"=>"",
																															"token"=>$post["ventana"],
																															"mensaje_token"=>$mensaje_bienvenida,
																															]);
				}
			}

			$this->result["ventana"]	=	["ventana"=>post("ventana")];
			$obj	=	new stdClass;
			$obj->token	=	$usuario->token;
			$obj->uid		=	$usuario->usuario_id;
			$obj->name	=	$usuario->login;
			$this->result["usuario"]	=	$obj;
			$this->result["crear_usuario"]	=	"SI";
			$this->Chats("DESC",1);
		}else if(!empty($usuario) && $usuario_grupo_exists ){
			$this->result["ventana"]	=	["ventana"=>post("ventana")];
			if ($usuario_grupo_exists->estatus==1) {
				$this->result["message"]	=	"Usuario ya se encuentra en la lista de los miembros";
			}else {
				$this->result["message"]	=	"Usuario ya se encuentra en la lista de los miembros, pero se encuentra inactivo";
			}
			$this->result["crear_usuario"]	=	"NO";
			return;
		}


	}

	public function uploadAudio(){
		$data 					= file_get_contents($_FILES['audio']['tmp_name']);
		$nombre_archivo	=	"file_".date("Y-m-d-H-i-s").token();
		$file='/uploads/audios/'.$nombre_archivo.'.ogg';

		$fp 		= fopen(PATH_IMG.$file, 'wb');
		fwrite($fp, $data);
		fclose($fp);
		$yo				=		$this->user;
		$post			=	post();
		$post["mensaje"]	=	'<audio controls><source src="'.IMG.$file.'" type="audio/ogg"></audio> ';
		$emisor		=	$this->user;
		$ventana	=	$this->amigos_x_token_ventana($post["ventana"]);
		$receptor	=	"";
		$reply		=	"";


		if(!empty($ventana)){
			if($emisor->usuario_id==$ventana->peticion_usuario_id){
				$receptor	=	$ventana->receptor_usuario_id;
			}else if($emisor->usuario_id==$ventana->receptor_usuario_id){
				$receptor	=	$ventana->peticion_usuario_id;
			}
		}


		if (isset($ventana->is_grupo)&&$ventana->is_grupo==0) {
			/*BUSCO CONVERSACION PREVIA PARA SACAR EL token*/
			$sql="SELECT *
							FROM `pgrw_op_chats_mensajes` `t1`
								WHERE (`t1`.`emisor_id` = '".$emisor->usuario_id."' AND `t1`.`receptor_id` = '".$receptor."')
									OR (`t1`.`emisor_id` = '".$receptor."' AND `t1`.`receptor_id` = '".$emisor->usuario_id."')
										ORDER BY `t1`.`mensaje_id` DESC LIMIT 1";
			$query 	=		$this->db->query($sql);
			$data		=		$query->row();
			$this->db->insert(DB_PREFIJO."op_chats_mensajes",[
																													"fecha"=>date("Y-m-d H:i:s"),
																													"mensaje"=>$post["mensaje"],
																													"emisor_id"=>$emisor->usuario_id,
																													"receptor_id"=>$receptor,
																													"mensaje_token"=>"message_".$emisor->usuario_id.'_'.$receptor."_".token(),
																													"estatus"=>0,
																													"token"=>"NO-GROUP",
																													]);
			$this->Chats("DESC",1);
		}else if(isset($ventana->is_grupo)&&$ventana->is_grupo==1){
			$mensaje_token	= "message_".token();
			$lista_amigos_grupo	=	$this->lista_amigos_grupo($ventana->token);
			foreach ($lista_amigos_grupo as $key => $value) {
				$insert=[
									"fecha"=>date("Y-m-d H:i:s"),
									"mensaje"=>$post["mensaje"],
									"emisor_id"=>(int)$emisor->usuario_id,
									"receptor_id"=>$value->usuario_id,
									"estatus"=>0,
									"token"=>$ventana->token,
									"mensaje_token"=>$mensaje_token,
								];

				$this->db->insert(DB_PREFIJO."op_chats_mensajes",$insert);
			}
			$this->Chats("DESC",1);
		}
	}

	public function borrar_usuario_grupo($value=''){
		$post				=		post();
		$usuario		=		usuarios_x_token($post["usuario_delete"]);
		$yo					=		$this->user;
		/*RECUERDA ARREGLAR TOKENS REPETIDOS*/
		if ($post["uid"]) {
			$usuario		=		usuarios($post["uid"]);
		}

		$ventana						=	$this->amigos_x_token_ventana($post["ventana"]);
		$lista_amigos_grupo	=	$this->lista_amigos_grupo($ventana->token);



		if (!empty($lista_amigos_grupo)) {
			$mensaje_bienvenida="message_user_del_".token();
			foreach ($lista_amigos_grupo as $key => $value) {
				$this->db->insert(DB_PREFIJO."op_chats_mensajes",[
																														"reply"=>0,
																														"fecha"=>date("Y-m-d H:i:s"),
																														"mensaje"=>"Fue eliminado ".$usuario->login." del grupo por ".$yo->login,
																														"emisor_id"=>$yo->usuario_id,
																														"receptor_id"=>$value->usuario_id,
																														"estatus"=>0,
																														"attachment"=>0,
																														"ogg"=>"",
																														"token"=>$post["ventana"],
																														"mensaje_token"=>$mensaje_bienvenida,
																														]);
			}
		}

		$this->db->where("token",$post["ventana"]);
		$this->db->where("usuario_id",$usuario->usuario_id);
		$this->db->delete(DB_PREFIJO."op_chats_grupos");

		$this->Chats("DESC",1);

	}

	/*CAMBIO DE NOMBRE DE VENTANA*/
	public function Alias(){
		$post				=		post();
		$usuario		=		usuarios_x_session($post["token"]);
		$this->db->where("token",$post["ventana"]);
		$this->db->update(DB_PREFIJO."op_chats_amigos",["alias"=>$post["value"]]);
		$this->result["token"]=$post["token"];
		$this->result["ventana"]=$post["ventana"];
		$this->result["alias"]=$post["value"];
	}

	public function SolicitudAmistad(){
		$usuario1	=	usuarios_x_token(get("token"));
		$yo				=	$this->user;
		if(!empty($usuario1)){
			$insert	=	[
										"fecha"=>date("Y-m-d H:i:s"),
										"peticion_usuario_id"=>$yo->usuario_id,
										"receptor_usuario_id"=>$usuario1->usuario_id,
										"estatus"=>1,
									];
			$solicitud	=	$this->ChequeaAmistad($yo->usuario_id,$usuario1->usuario_id);
			if(empty($solicitud)){
				$insert["token"]	=	token();
				$this->db->insert(DB_PREFIJO."op_chats_amigos",$insert);
			}else{
				$this->db->where("amistad_id",$solicitud->amistad_id);
				$this->db->update(DB_PREFIJO."op_chats_amigos",$insert);
			}
			redirect(base_url("Eventos/Panel"));exit;
		}else{
			echo 'Token vencido';
		}
	}

	public function Invitacion(){
		$usuario1	=	usuarios_x_token(post("token"));
		$yo				=	$this->user;
		$insert	=	[
									"fecha"=>date("Y-m-d H:i:s"),
									"peticion_usuario_id"=>$yo->usuario_id,
									"receptor_usuario_id"=>$usuario1->usuario_id,
									"estatus"=>0,
								];
		$solicitud	=	$this->ChequeaAmistad($yo->usuario_id,$usuario1->usuario_id);
		if(empty($solicitud)){
			$insert["token"]	=	token();
			if($this->db->insert(DB_PREFIJO."op_chats_amigos",$insert)){
				$message	=	"Solicitud Realizada, a la espera de confirmación";
			}else{
				$message	=	"Problemas para crear solicitud";
			}
		}else{
			$this->db->where("amistad_id",$solicitud->amistad_id);
			if($this->db->update(DB_PREFIJO."op_chats_amigos",$insert)){
				$message	=	"Solicitud Realizada, a la espera de confirmación";
			}else {
				$message	=	"Problemas para crear solicitud";
			}
		}
		$this->result["data"]			=	["token"=>post("token")];
		$this->result["message"]	=	$message;

	}

	public function ChequeaAmistad($yo,$amigo){
		$post			=	post();
		$tabla	=	DB_PREFIJO."op_chats_amigos";
		$sql="SELECT *
						FROM ".$tabla." `t1`
							WHERE (`t1`.`peticion_usuario_id` = '".$yo."' AND `t1`.`receptor_usuario_id` = '".$amigo."')
								OR (`t1`.`peticion_usuario_id` = '".$amigo."' AND `t1`.`receptor_usuario_id` = '".$yo."')";
		$query 	=		$this->db->query($sql);
		return $query->row();
	}

	public function Search(){
		$post			=	post();
		$tabla	=	DB_PREFIJO."usuarios t1 ";
		$data		=	$this->db->select("t1.nombres,t1.primer_apellido,t1.token,t1.telefono,t1.usuario_id,t2.estatus")
											->from($tabla)
											->join(DB_PREFIJO."op_chats_amigos t2","t1.usuario_id=t2.receptor_usuario_id","left")
											->where("nombres!=","")
											->where("nombres!=","null")
											->where("lower(login)",strtolower($post["q"]))
											// ->like("lower(nombre_usuario)",strtolower($post["q"]))
											// ->or_like("lower(email)",strtolower($post["q"]))
											// ->or_like("identificacion",strtolower($post["q"]))
											->get()
											->result();
		$return =[];
		if (!empty($data)) {
			foreach ($data as $key => $value) {
				if ($this->user->usuario_id!=$value->usuario_id) {
					$obj	=		$value;
					$obj->avatar	=	$this->img_profile($value->usuario_id);
					$return[]	=	$obj;
				}
			}
		}
		$this->result["data"]	=	$return;
	}

	public function Chats($order="DESC",$limit=30){
		$post			=	post();
		$emisor		=	$this->user;
		$ventana	=	$this->amigos_x_token_ventana($post["ventana"]);
		$receptor	=	"";
		$return=[];
		$participantes	=		[];

		if ($ventana->is_grupo==0) {
			if(!empty($ventana)){
				if($emisor->usuario_id==$ventana->peticion_usuario_id){
					$receptor	=	$ventana->receptor_usuario_id;
				}else if($emisor->usuario_id==$ventana->receptor_usuario_id){
					$receptor	=	$ventana->peticion_usuario_id;
				}
			}
			$tabla	=	DB_PREFIJO."op_chats_mensajes t1";
			$sql="SELECT t1.*,t2.login as emisor,t2.token as token_emisor,t3.alias
							FROM `pgrw_op_chats_mensajes` `t1`
								LEFT JOIN ".DB_PREFIJO."usuarios t2 ON t1.emisor_id=t2.usuario_id
								LEFT JOIN ".DB_PREFIJO."op_chats_amigos t3 ON t1.token=t3.token
									WHERE ((`t1`.`emisor_id` = '".$emisor->usuario_id."' AND `t1`.`receptor_id` = '".$receptor."')
										OR (`t1`.`emisor_id` = '".$receptor."' AND `t1`.`receptor_id` = '".$emisor->usuario_id."'))
											AND t1.token='NO-GROUP'
												ORDER BY `t1`.`mensaje_id` ".$order." LIMIT ".$limit;
			$query 	=		$this->db->query($sql);
			$data		=		$query->result();

			$cantidad		=	count($data);
			if (!empty($data)) {
				foreach ($data as $key => $value) {
					$return[$cantidad] =	$value;
					$return[$cantidad]->class	=	($value->emisor_id==$emisor->usuario_id)?"row justify-content-end":"row justify-content-start";
					$return[$cantidad]->align	=	($value->emisor_id==$emisor->usuario_id)?"text-right":"text-left";
					$participantes[$value->emisor_id]	= new	stdClass();
					$participantes[$value->emisor_id]->name		=	$value->emisor;
					$participantes[$value->emisor_id]->token	=	$value->token_emisor;
					$participantes[$value->emisor_id]->uid		=	$value->emisor_id;
					$cantidad--;
				}
			}
		}else if($ventana->is_grupo==1){
			$sql="SELECT t1.*,t2.login as emisor,t2.token as token_emisor,t3.alias
							FROM `pgrw_op_chats_mensajes` `t1`
								LEFT JOIN ".DB_PREFIJO."usuarios t2 ON t1.emisor_id=t2.usuario_id
								LEFT JOIN ".DB_PREFIJO."op_chats_amigos t3 ON t1.token=t3.token
									WHERE `t1`.`token` = '".$ventana->token."'
										AND t1.token!='NO-GROUP'
											GROUP BY	`t1`.`mensaje_token`
												ORDER BY `t1`.`mensaje_id` ".$order." LIMIT ".$limit;
			$query 	=		$this->db->query($sql);
			$data		=		$query->result();
			$cantidad		=	count($data);
			if (!empty($data)) {
				foreach ($data as $key => $value) {
					$return[$cantidad] =	$value;
					$return[$cantidad]->class	=	($value->emisor_id==$emisor->usuario_id)?"row justify-content-end":"row justify-content-start";
					$return[$cantidad]->align	=	($value->emisor_id==$emisor->usuario_id)?"text-right":"text-left";
					$participantes[$value->emisor_id]	= new	stdClass();
					$participantes[$value->emisor_id]->name		=	$value->emisor;
					$participantes[$value->emisor_id]->token	=	$value->token_emisor;
					$participantes[$value->emisor_id]->uid		=	$value->emisor_id;
					$cantidad--;
				}
			}

				$lista_amigos_grupo=$this->lista_amigos_grupo($ventana->token,1);
				if (!empty($lista_amigos_grupo)) {
					foreach ($lista_amigos_grupo as $key => $value) {
						$usuario																		=	usuarios($value->usuario_id);
						$participantes[$value->usuario_id]					= new	stdClass();
						$participantes[$value->usuario_id]->name		=	$usuario->login;
						$participantes[$value->usuario_id]->token		=	$usuario->token;
						$participantes[$value->usuario_id]->uid			=	$value->usuario_id;
					}
				}

		}
		$this->result["participantes"]	=	$participantes;
		$this->result["is_grupo"]	=	$ventana->is_grupo;
		$this->result["token"]		=	$post["token"];
		$this->result["ventana"]	=	$post["ventana"];
		$this->result["data"]			=	$return;
	}


	private function lista_amigos_grupo($token,$estatus=false){
		$tabla						=	DB_PREFIJO."op_chats_grupos";
		if ($estatus) {
			return $this->db->select("*")
												->from($tabla)
												->where("token",$token)
												->where("estatus",$estatus)
												->get()
												->result();
		}else {
			return $this->db->select("*")
												->from($tabla)
												->where("token",$token)
												->get()
												->result();
		}
	}


	public function Upload(){
		$this->result["data"]	= event_upload('userfile','images/uploads/chat_files/',array("allowed_types"=>'application/vnd.oasis.opendocument.spreadsheet | application/vnd.oasis.opendocument.text | application/vnd.oasis.opendocument.presentation |
application/vnd.openxmlformats-officedocument.wordprocessingml.document | application/vnd.ms-excel | application/vnd.openxmlformats-officedocument.presentationml.presentation | txt | gif|jpg|jpeg|png|doc|docm|docx|dot|dotm|dotx|pdf|rtf|txt|csv|xls|xlsb|xlsm|xlt|xltm|xps'));
	}

	private function message_by_reply($token){
		$tabla						=	DB_PREFIJO."op_chats_mensajes";
		return $this->db->select("*")
											->from($tabla)
											->where("mensaje_token",$token)
											->get()
											->row();
	}

	public function message(){
		$post			=	post();
		$emisor		=	$this->user;
		$ventana	=	$this->amigos_x_token_ventana($post["ventana"]);
		$receptor	=	"";
		$reply		=	"";

		if (isset($post["reply"])) {
			$reply=$this->message_by_reply($post["reply"]);
			if (!empty($reply)) {
				$reply=$reply->mensaje_id;
			}else{
				$reply=0;
			}
		}

		if(!empty($ventana)){
			if($emisor->usuario_id==$ventana->peticion_usuario_id){
				$receptor	=	$ventana->receptor_usuario_id;
			}else if($emisor->usuario_id==$ventana->receptor_usuario_id){
				$receptor	=	$ventana->peticion_usuario_id;
			}
		}


		if (isset($ventana->is_grupo)&&$ventana->is_grupo==0) {
			/*BUSCO CONVERSACION PREVIA PARA SACAR EL token*/
			$sql="SELECT *
							FROM `pgrw_op_chats_mensajes` `t1`
								WHERE (`t1`.`emisor_id` = '".$emisor->usuario_id."' AND `t1`.`receptor_id` = '".$receptor."')
									OR (`t1`.`emisor_id` = '".$receptor."' AND `t1`.`receptor_id` = '".$emisor->usuario_id."')
										ORDER BY `t1`.`mensaje_id` DESC LIMIT 1";
			$query 	=		$this->db->query($sql);
			$data		=		$query->row();
			$this->db->insert(DB_PREFIJO."op_chats_mensajes",[
																													"fecha"=>date("Y-m-d H:i:s"),
																													"mensaje"=>$post["mensaje"],
																													"emisor_id"=>$emisor->usuario_id,
																													"receptor_id"=>$receptor,
																													"reply"=>$reply,
																													"mensaje_token"=>"message_".$emisor->usuario_id.'_'.$receptor."_".token(),
																													"estatus"=>0,
																													"token"=>"NO-GROUP",
																													]);
			$this->Chats("DESC",1);
		}else if(isset($ventana->is_grupo)&&$ventana->is_grupo==1){
			$mensaje_token	= "message_".token();
			$lista_amigos_grupo	=	$this->lista_amigos_grupo($ventana->token);
			foreach ($lista_amigos_grupo as $key => $value) {
				$insert=[
									"fecha"=>date("Y-m-d H:i:s"),
									"mensaje"=>$post["mensaje"],
									"emisor_id"=>(int)$emisor->usuario_id,
									"receptor_id"=>$value->usuario_id,
									"estatus"=>0,
									"token"=>$ventana->token,
									"reply"=>$reply,
									"mensaje_token"=>$mensaje_token,
								];

				$this->db->insert(DB_PREFIJO."op_chats_mensajes",$insert);
			}
			$this->Chats("DESC",1);
		}
	}

	private function amigos_x_token_ventana($token){
		$tabla						=	DB_PREFIJO."op_chats_amigos";
		return $this->db->select("*")
											->from($tabla)
											->where("token",$token)
											->get()
											->row();
	}

	private function usuario_x_token($token){
		$tabla						=	DB_PREFIJO."usuarios";
		return $this->db->select("*")
											->from($tabla)
											->where("token",$token)
											->get()
											->row();
	}

	public function listaUsuarios(){
		$tabla	=	DB_PREFIJO."op_chats_amigos t1";
		$data		=	$this->db->select(" TRIM(LEADING '0' FROM usuario_id) as usuario_id,
																	t2.usuario_id as perfil_id,
																	t1.token as ventana,
																	t1.is_grupo,
																	t2.token,
																	nombres,
																	apellidos,
																	login,
																	login ")
												->from($tabla)
												->join(DB_PREFIJO."usuarios t2","t1.peticion_usuario_id=t2.usuario_id OR t1.receptor_usuario_id=t2.usuario_id")
												->where("t1.estatus",1)
												->where("peticion_usuario_id",$this->user->usuario_id)
												->or_where("receptor_usuario_id",$this->user->usuario_id)
												->group_by("t2.usuario_id")
												->get()
												->result();
		// $data		=	$this->db->select(" TRIM(LEADING '0' FROM t3.usuario_id) as usuario_id,t3.usuario_id as perfil_id,	t1.token as ventana,t2.token, t3.nombres, t3.segundo_nombre, t3.primer_apellido,t3.segundo_apellido ,t3.login")
		// 										->from($tabla)
		// 										->join(DB_PREFIJO."usuarios t2","t1.peticion_usuario_id=t2.usuario_id OR t1.receptor_usuario_id=t2.usuario_id")
		// 										->join(DB_PREFIJO."usuarios t3","t1.peticion_usuario_id=t3.usuario_id OR t1.receptor_usuario_id=t3.usuario_id")
		// 										->where("t2.usuario_id",$this->user->usuario_id)
		// 										->get()
		// 										->result();
		$return =[];
		if (!empty($data)) {
			foreach ($data as $key => $value) {
				if ($this->user->usuario_id!=$value->usuario_id) {
					$obj	=		$value;
					if($obj->alias==='null'	||	$obj->alias===null 	||	$obj->alias===NULL	||  $obj->alias==='NULL'	||	$obj->alias==='' ){
						$obj->nombre_usuario	=		$obj->login;
					}else{
						$obj->nombre_usuario	=		$obj->alias;
					}
					$obj->avatar	=	$this->img_profile($obj->perfil_id);
					$return[]	=	$obj;
				}
			}
		}

		$data		=	$this->db->select(" TRIM(LEADING '0' FROM t1.usuario_id) as usuario_id,
																		t1.usuario_id as perfil_id,
																		t1.token as ventana,
																		t1.grupo as login,
																		'1' as is_grupo,
																		t3.alias,
																		t2.token")
												->from(DB_PREFIJO."op_chats_grupos t1")
												->join(DB_PREFIJO."usuarios t2","t1.usuario_id=t2.usuario_id")
												->join(DB_PREFIJO."op_chats_amigos t3","t1.token=t3.token")
												->where("t1.estatus",1)
												->group_by("t1.token")
												->get()
												->result();
		//p($data);
		if (!empty($data)) {
			foreach ($data as $key => $value) {
				$obj	=		$value;

				if($obj->alias==='null'	||	$obj->alias===null 	||	$obj->alias===NULL	||  $obj->alias==='NULL'	||	$obj->alias==='' ){
					$obj->nombre_usuario	=		$obj->login;
				}else{
					$obj->nombre_usuario	=		$obj->alias;
				}

				$obj->avatar	=	$this->img_profile($obj->perfil_id);
				$return[]	=	$obj;
			}
		}
		$this->result["data"]	=	$return;
	}

	private function img_profile($usuario_id){
		return $this->image("uploads/perfiles/".$usuario_id.'/profile.jpg');
	}


	private function image($image,$html=false,$imageTag=false,$attr=array()){
		$return_image=null;
		if(file_exists(PATH_IMG.$image)){
			$return_image = IMG.$image;
		}else{
			$return_image = IMG."No_image.png";
		}
		if(!$html){
			return $return_image;
		}else{
			$atributos	=	'';
			foreach($attr as $k	=> $v){
				$atributos	.=	 $k.'="'.$v.'"';
			}
			if(!$imageTag){
				return '<img src="'.$return_image.'" '.$atributos.' />';
			}else{
				return '<div class="image_rect image_default" style="background-image:url('.$return_image.');-webkit-background-size: cover; -moz-background-size: cover; -o-background-size: cover; background-size: cover;"></div>';
			}
		}
	}

	public function ___listaUsuarios(){
		$tabla	=	DB_PREFIJO."op_chats_mensajes t1";
		$data		=	$this->db->select(" TRIM(LEADING '0' FROM usuario_id) as usuario_id,	token, nombres, apellidos,login ")
												->from($tabla)
												->join(DB_PREFIJO."usuarios t2","t1.emisor_id=t2.usuario_id OR t1.receptor_id=t2.usuario_id")
												//->where("usuario_id",$this->user->usuario_id)
												->group_by("t2.usuario_id")
												->order_by("t1.mensaje")
												->get()
												->result();
		$this->result["data"]	=	$data;
	}

	public function Grupos(){
		$post	=	post();
		$yo		=	$this->user;
		$grupo_token=	"g_".token();
		$grupo_str	=	"Grupo ".rand(100,9000);

		$mensaje_bienvenida="message_".token();
		/*PRIMER LUGAR CREO UN GRUPO CON NOMBRE ALEATORIO*/
		foreach ($post["usuario"] as $key => $value) {
			$insert_grupo	=	[
												"usuario_id"=>usuarios_x_token($value)->usuario_id,
												"grupo"=>$grupo_str,
												"fecha"=>date("Y-m-d H:i:s"),
												"estatus"=>1,
												"token"=>$grupo_token
											];
			$this->db->insert(DB_PREFIJO."op_chats_grupos",$insert_grupo);
			$this->db->insert(DB_PREFIJO."op_chats_mensajes",[
																													"reply"=>0,
																													"fecha"=>date("Y-m-d H:i:s"),
																													"mensaje"=>"Bienvenidos al grupo",
																													"emisor_id"=>$yo->usuario_id,
																													"receptor_id"=>usuarios_x_token($value)->usuario_id,
																													"estatus"=>0,
																													"attachment"=>0,
																													"ogg"=>"",
																													"token"=>$grupo_token,
																													"mensaje_token"=>$mensaje_bienvenida,
																													]);
		}

		/*CREO UNA AMISTAD CON EL GRUPO*/
		$insert	=	[
									"fecha"=>date("Y-m-d H:i:s"),
									"peticion_usuario_id"=>$yo->usuario_id,
									"receptor_usuario_id"=>0,
									"is_grupo"=>1,
									"estatus"=>1,
									"token"=>$grupo_token,
								];
		$this->db->insert(DB_PREFIJO."op_chats_amigos",$insert);
		//p($insert_grupo);
		$this->result["data"]			=	[
																	"token"=>$yo->token,
																	"ventana"=>$grupo_token,
																	"avatar"=>$this->img_profile(100000),
																	"name"=>$grupo_str,
																];

		$this->result["message"]	=	"Grupo Creado";
	}

	function response(){
		return $this->result;
	}
}
?>
