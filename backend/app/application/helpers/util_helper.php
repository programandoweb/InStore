<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
	DESARROLLADO POR JORGE MENDEZ
	programandoweb.net
	info@programandoweb.net
	Colombia - Venezuela - Chile
*/

/*	$primary_key la clave primaria
		datos tabla a insertar o editar,
		array where para verificar,
		$variables a consultar (posible post),
		$data a insertar o edidar
*/

/*
	ESTO HACE QUE SI UN USUARIO NO TIENE PRIVILEGIOS
	NECESARIOS, LO ENVÍE A UN FORMULARIO PARA SETEAR SU PERFIL
	A DETERMINADO USUARIO, EJEMPLO ROOT: QUIERE VER LA LISTA
	DE USUARIOS, DEBE HACERLO BAJO EL PERFIL DE DIRECTOR
*/

/*
	DEBERÍA PASAR ARRAY CON LOS filtros:
	instituciones
*/

function Lista_Profesores_X_Grados($grado_escolar_token){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."op_grados_x_instituciones t1";
	return $ci->db->select("	t4.usuario_id as profesor_id,
														t4.nombres,
														t4.telefono,
														t4.token as profesor_token,
														t4.email,CONCAT(grado_escolar,' ', GROUP_CONCAT(materia ORDER BY materia ASC SEPARATOR ','))  AS materias,
														IF(t4.genero='masculino', 'design/profe.png', 'design/profesora.png')  as avatar")
								->from($tabla)
								->join(DB_PREFIJO."op_materias_x_grados t2","t1.grado_escolar_id=t2.grado_escolar_id  AND t1.institucion_id=t2.institucion_id AND t1.nivel_educativo_id=t2.nivel_educativo_id")
								->join(DB_PREFIJO."rel_profesores_materias t3","t1.institucion_id=t3.institucion_id AND t1.grado_escolar_id=t3.grado_escolar_id AND t2.token=t3.materia_token AND t1.letra=t3.seccion")
								->join(DB_PREFIJO."usuarios t4","t3.docente_id=t4.usuario_id")
								->join(DB_PREFIJO."ma_grados_escolares t5","t1.grado_escolar_id=t5.grado_escolar_id")
								->where("t1.token",$grado_escolar_token)
								->group_by("t3.docente_id")
								->get()
								->result();
}

function avatar($genero){
	if ($genero=="masculino") {
		return 	'design/nino.png';
	}
	else {
		return	'design/nina.png';
	}
}

function get_recipientes_alumnos($alumno_id){
	$ci 	=& 	get_instance();
	$tabla1	=		DB_PREFIJO."rel_alumnos_instituciones t1";
	$tabla2	=		DB_PREFIJO."usuarios t2";
	$tabla3	=		DB_PREFIJO."usuarios t3";
	$rows		= 	$ci->db->select('t2.push_token as alumno,t3.push_token as acudiente,t2.usuario_id as aluid,t3.usuario_id as acuid')
											->from($tabla1)
											->join($tabla2,"t1.usuario_id		=	t2.usuario_id","left")
											->join($tabla3,"t1.acudiente_id	=	t3.usuario_id","left")
											->where("t1.usuario_id",$alumno_id)
											->get()
											->row();
	if (!empty($rows)) {
		$return='--->'.$rows->aluid.'<---->'.$rows->acuid.'<----';
		$return='';
		if ($rows->alumno!=$rows->acudiente) {
			if ($rows->alumno!='') {
				$return	.=	$rows->alumno.',';
			}
			if ($rows->acudiente!='') {
				$return	.=	$rows->acudiente.',';
			}
		}else {
			if ($rows->alumno!='') {
				$return	.=	$rows->alumno.',';
			}
		}
		return $return;
	}else {
		return "";
	}
}

function curl_socketJavacript($array='',$stop=false){
		$parametros			=		json_encode($array);
		$url 						= 	SOCKET_SERVER.':'.SOCKET_PORT."/emit";

		$fields_string='';
		foreach($array as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		$fields_string=rtrim($fields_string, '&');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
    $status	=	curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);


		if ($stop) {
			echo $url.'<br/>';
			echo $status.'<br/>';
			echo $result.'<br/>';
			exit;
		}

}

function curl_push($title='Prueba de título',$message='Prueba de cuerpo del mensaje',$recipients=''){
		$url 						= 	PUSH_URL."/message";
 		$ch 						= 	curl_init($url);
		$parametros 		= 	'key='.PUSH_KEY.'&title='.urlencode($title).'&message='.urlencode($message).'&recipients='.$recipients;

		//Se indica que es el método POST
		curl_setopt ($ch, CURLOPT_POST, 1);

		//Se añaden los parámetros
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $parametros);

		//Máximo de tiempo esperando una respuesta del servidor
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);

	 //Que nos devuelva las cabeceras de la petición
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		//Para saber si se redirige
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		//Obtiene la dirección url
		curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

		// ejecutamos la petición
		curl_exec($ch);

		// cerramos
		curl_close($ch);

}

function tipo_de_observacion($tipo){
	switch ($tipo) {
		case 3:
			$tipo = "Observación Neutra";
		break;
		case 2:
			$tipo = "Observación Mala";
		break;
		case 1:
		default:
			$tipo = "Observación Buena";
		break;
	}
	return $tipo;
}

function asistencia_por_alumno($alumno_id,$materia_token,$fecha){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."op_alumnos_asistencia";
	return 	$ci->db->select('*')
										->from($tabla1)
										->where("alumno_id",$alumno_id)
										->where("materia_token",$materia_token)
										->where("fecha",$fecha)
										->get()
										->row();
}

function libro($token){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."op_biblioteca t1";
	$tabla2=	DB_PREFIJO."usuarios t2";
	return 	$ci->db->select('t1.*,CONCAT(t2.nombres," (", t2.identificacion ,") ") as autor')
										->from($tabla1)->join($tabla2,"t1.usuario_id=t2.usuario_id")
										->where("t1.token",$token)
										->get()
										->row();
}

function libros($term){
	$ci 		=& 	get_instance();
	$tabla1	=		DB_PREFIJO."op_biblioteca t1";
	$tabla2	=		DB_PREFIJO."usuarios t2";
	$rows		=		$ci->db->select('t1.*,CONCAT(t2.nombres," (", t2.identificacion ,") ") as autor')
												->from($tabla1)->join($tabla2,"t1.usuario_id=t2.usuario_id")
												->like("t1.texto",$term)
												->or_like("t1.descripcion",$term)
												->get()
												->result();
	$return				=	[];
	if (!empty($rows)) {
		foreach ($rows as $key => $value) {
			$value		=	campo_json_db($value);
			$data			=	new stdClass;
			$data->id			=	$value->biblioteca_id;
			$data->label	=	$value->texto;
			$data->value	=	$value->fullurl;
			$return[]			=	$data;
		}
	}
	return $return;
}

function busqueda_dinamica($obj){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO.$obj->tabla;
	return 	$ci->db->select('*')
										->from($tabla1)
										->where($obj->key,$obj->value)
										->get()
										->row();
}

function parse_to_select($data,$var){
	if (isset($var[2])) {
		$return =	[""	=>	$var[2]];
	}else {
		$return =	[""=>"Seleccione"];
	}
	$index	=	$var[0];
	$str		=	$var[1];
	foreach ($data as $key => $value) {
		$return[$value->$index] =	$value->$str;
	}
	return $return;
}

function filtros($institucion=false,$genero=false,$edad=false,$notas=false,$grados=false){
	$ci 				=& 	get_instance();
	$html_ini		=	form_open( base_url("Apanel/Secretaria?m=exc_Gestion_instituciones&view=Panel&back=history&tab=false&sv=docentes"),
																array(  "method"=>"get",
															 					"class"=>"filtros_busqueda"),
																get()
													);
	$html_ini		.=	'<div class="row mt-3 mb-3"><div class="col"><div class="card"><div class="card-header bg-primary text-white">Filtros</div><div class="card-body">';

	if ($institucion) {
		$html_ini		.=	MakeSelect( "institucion_id",get("institucion_id"),["class"=>"mb-2 browser-default custom-select dependiente",],instituciones());
	}
	if ($genero) {
		$html_ini		.=	MakeSelect( "genero",get("genero"),["class"=>"mb-2 browser-default custom-select dependiente",],[""=>"Género","masculino"=>"Masculino","femenino"=>"Femenino"]);
	}
	if ($grados) {
		$todos_los_grados	=	parse_to_select(ma_todos_los_grados(),["grado_escolar_id","grado_escolar"]);
		$html_ini		.=	MakeSelect( "grado_escolar_id",get("grado_escolar_id"),["class"=>"mb-2 browser-default custom-select dependiente",],$todos_los_grados);
	}
	if ($edad) {
		$edad	=	[""=>"Edad desde",];
		for ($i=4; $i <25 ; $i++) {
			$edad[$i]	=	$i;
		}
		$html_ini		.=	'<div class="row">';
			$html_ini		.=	'<div class="col">';
				$html_ini		.=	MakeSelect( "edad_desde",get("edad_desde"),["class"=>"mb-2 browser-default custom-select dependiente",],$edad);
			$html_ini		.=	'</div>';
		$edad	=	[""=>"Edad hasta",];
			for ($i=4; $i <25 ; $i++) {
				$edad[$i]	=	$i;
			}
			$html_ini		.=	'<div class="col">';
				$html_ini		.=	MakeSelect( "edad_hasta",get("edad_hasta"),["class"=>"mb-2 browser-default custom-select dependiente",],$edad);
			$html_ini		.=	'</div>';
		$html_ini		.=	'</div>';
	}

	if ($notas) {
		$nota	=	[""=>"Notas desde",];
		for ($i=1; $i <6 ; $i++) {
			$nota[$i]	=	$i;
		}
		$html_ini		.=	'<div class="row">';
			$html_ini		.=	'<div class="col">';
				$html_ini		.=	MakeSelect( "notas_desde",get("notas_desde"),["class"=>"mb-2 browser-default custom-select dependiente",],$nota);
			$html_ini		.=	'</div>';
		$edad	=	[""=>"Notas hasta",];
		for ($i=1; $i <6 ; $i++) {
			$nota[$i]	=	$i;
		}
			$html_ini		.=	'<div class="col">';
				$html_ini		.=	MakeSelect( "notas_hasta",get("notas_hasta"),["class"=>"mb-2 browser-default custom-select dependiente",],$nota);
			$html_ini		.=	'</div>';
		$html_ini		.=	'</div>';
	}
	$html_fin		=		'<div class="btn btn-primary btn-sm buscar"><i class="fas fa-search"></i> Filtrar</div></div></div></div></div>'.form_close();
	echo $html_ini.$html_fin;
}

function asistencia($alumno_id,$materia,$seccion){
	$ci 	=& 	get_instance();
	$return = new stdClass;
	$return->asistencias=0;
	$return->inasistencias=0;
	$return->totalclases=0;
	$tabla1=	DB_PREFIJO."op_alumnos_asistencia t1";
	$row	=		$ci->db->select('IF(sum(estatus), sum(estatus), 0) AS total')
										->from($tabla1)
										->where("t1.materia_token",$materia)
										->where("t1.seccion",$seccion)
										->where("t1.alumno_id",$alumno_id)
										->where("t1.estatus",0)
										->get()
										->row();
	if (!empty($row)) {
		$return->inasistencias=(!empty($row->total))?(int)$row->total:0;
	}
	$row	=		$ci->db->select('IF(sum(estatus), sum(estatus), 0) AS total')
										->from($tabla1)
										->where("t1.materia_token",$materia)
										->where("t1.seccion",$seccion)
										->where("t1.alumno_id",$alumno_id)
										->where("t1.estatus",1)
										->get()
										->row();
	if (!empty($row)) {
		$return->asistencias=(!empty($row->total))?(int)$row->total:0;
	}

	$rows	=		$ci->db->select('*')
										->from($tabla1)
										->where("t1.materia_token",$materia)
										->where("t1.seccion",$seccion)
										->where("t1.alumno_id",$alumno_id)
										->get()
										->result();
	$return->todas_las_clases	=	$rows;
	$return->totalclases=$return->asistencias+$return->inasistencias;
	return $return;
}

function mensajes($usuario_id,$estatus="ALL"){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."op_notificacion t1";
	$tabla2=	DB_PREFIJO."usuarios t2";
	$ci->db->select("	t1.notificacion,
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
										->join($tabla2,"t1.usuario_id=t2.usuario_id OR t1.emisor_id=t2.usuario_id","left")
										->join(DB_PREFIJO."usuarios t3","t1.emisor_id=t3.usuario_id","left")
										->join(DB_PREFIJO."usuarios t4","t1.usuario_id=t4.usuario_id","left")
										->where("t1.usuario_id",$usuario_id);
		if ($estatus=="NUEVOS") {
			$ci->db->where("t1.estatus",0);
		}
		$rows	=					$ci->db->or_where("t1.emisor_id",$usuario_id)
										->group_by("notificacion_id")
										->order_by("fecha,notificacion_id","desc")
										->get()
										->result();
	return $rows;
}

function alumnos_seccion($usuario_id){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."rel_alumnos_instituciones t1";
	$tabla2=	DB_PREFIJO."op_grados_x_instituciones t2";
	$rows	=		$ci->db->select("*")
										->from($tabla1)
										->join($tabla2,"t1.grado_escolar_token=t2.token","left")
										->where("t1.usuario_id",$usuario_id)
										->get()
										->row();
	return (isset($rows->letra))?$rows->letra:"NO DEFINIDA";
}

function evaluacion_alumno($alumno_id,$token){
	$ci 	=& 	get_instance();
	$ci->db->select("*")->from(DB_PREFIJO."op_alumnos_notas");
	$ci->db->where("alumno_id",$alumno_id);
	$ci->db->where("token",$token);
	return $ci->db->get()->row();
}

function alumnos_x_manteria($materia_token,$seccion){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."op_profesores_evaluaciones t1";
	$ci->db->select("*, t5.nombres as alumno, t6.nombres as acudiente , t5.usuario_id as alumno_id")
						->from($tabla)
						->join(DB_PREFIJO."op_materias_x_grados t2","t1.materia_token=t2.token")
						->join(DB_PREFIJO."op_grados_x_instituciones t3","t2.grado_escolar_id=t3.grado_escolar_id")
						->join(DB_PREFIJO."rel_alumnos_instituciones t4","t3.token=t4.grado_escolar_token")
						->join(DB_PREFIJO."usuarios t5","t4.usuario_id=t5.usuario_id")
						->join(DB_PREFIJO."usuarios t6","t4.acudiente_id=t6.usuario_id")
						->where("t1.materia_token",$materia_token)
						->where("letra",$seccion)->group_by("t4.usuario_id");
	return $rows		= 	$ci->db->get()->result();
}

function evaluacions_x_materias_x_alumnos($materia_token,$seccion){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."op_profesores_evaluaciones t1";
	$ci->db->select("t1.*")->from($tabla)
													->where("t1.materia_token",$materia_token)
													->where("t1.seccion",$seccion)
													//->where("t1.fecha<=",date("Y-m-d"))
													;
	$rows		= 	$ci->db->get()->result();
	$return=[];
	if (!empty($rows)) {
		foreach ($rows as $key => $value) {
			$return[$value->periodo][] =	$value;
		}
	}
	return $return;
}

function materias_x_alumnos($usuario_id,$docente_id=false){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."rel_alumnos_instituciones t1";
	$ci->db->select("t3.*")->from($tabla)
											->join(DB_PREFIJO."op_grados_x_instituciones t2","t1.grado_escolar_token=t2.token")
											->join(DB_PREFIJO."op_materias_x_grados t3","t2.grado_escolar_id=t3.grado_escolar_id")
											->join(DB_PREFIJO."rel_profesores_materias t4","t3.token=t4.materia_token")
											->where("t1.usuario_id",$usuario_id);
	if ($docente_id) {
		$ci->db->where("t4.docente_id",$docente_id);
	}
	$rows		= 	$ci->db->get()->result();
	$return=[];
	if (!empty($rows)) {
		foreach ($rows as $key => $value) {
			$return[$value->token]	=	$value->materia;
		}
	}
	return $return;
}

function asistencias_hoy($docente_id,$materia_token,$grado_escolar_id,$seccion,$fecha=false){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."op_alumnos_asistencia t1";
	$ci->db->select("*")
											->from($tabla)
											->where("t1.docente_id",$docente_id)
											->where("t1.materia_token",$materia_token)
											->where("t1.grado_escolar_id",$grado_escolar_id);
	if (!$fecha) {
		$fecha	= date("Y-m-d");
	}
	$rows		= 	$ci->db->where("t1.fecha",$fecha);
	$rows		= 	$ci->db->get()->result();
	$return=[];
	if (!empty($rows)) {
		foreach ($rows as $key => $value) {
			$return[$value->alumno_id]	=	$value;
		}
	}
	return $return;
}

function asistencias($alumnos_asistencia_id){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."op_alumnos_asistencia t1";
	$rows	= 	$ci->db->select("*")
											->from($tabla)
											->where("t1.alumnos_asistencia_id",$alumnos_asistencia_id)
											->get()
											->result();
	$return=[];
	if (!empty($rows)) {
		foreach ($rows as $key => $value) {
			$return[$value->alumno_id]	=	$value;
		}
	}
	return $return;
}

function Lista_Alumnos($token_materia,$grado_escolar_id,$seccion){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."op_materias_x_grados t1";
	return $ci->db->select("*,t1.token as materia_token")
								->from($tabla)
								->join(DB_PREFIJO."op_grados_x_instituciones t2","t1.grado_escolar_id=t2.grado_escolar_id")
								->join(DB_PREFIJO."rel_alumnos_instituciones t3","t2.token=t3.grado_escolar_token")
								->join(DB_PREFIJO."usuarios t4","t3.usuario_id=t4.usuario_id")
								->join(DB_PREFIJO."ma_grados_escolares t5","t1.grado_escolar_id=t5.grado_escolar_id")
								->where("t1.token",$token_materia)
								->where("t2.letra",$seccion)
								->order_by("t4.nombres","ASC")
								->get()
								->result();
}

function upload_nativo($file='userfile',$path="documentos"){
	if (empty($_FILES[$file]['name'])) {
		return false;
	}
	$ci 	=& 	get_instance();
	$ruta					=		'uploads/'.$path;
	$upload_path  = 	PATH_IMG.$ruta;
	$upload_url  	= 	IMG.$ruta;
	$return				=		new stdClass();
	if(!is_dir($upload_path)){
		if(!mkdir($upload_path, 0755,true)){
			return false;
		}else{
			$fp		=	fopen($upload_path.'/index.html',"w");
			fwrite($fp,'<a href="http://programandoweb.net">PROGRAMANDOWEB</a>');
			fclose($fp);
		}
	}

	$nombre_archivo			=		$_FILES[$file]['name'];
	$nombre_archivo_md5	=		"file_".$ci->user->usuario_id."_".url_title($ci->user->nombres)."_".md5($nombre_archivo);
	$extension_archivo	=		obtenerExtensionFichero($nombre_archivo);
	$tamano 						= 	$_FILES[$file]['size'];
	$tmp 								= 	$_FILES[$file]['tmp_name'];

	if ($tamano<10097152) {
		if(move_uploaded_file($tmp,$upload_path.'/'.$nombre_archivo_md5.".".$extension_archivo)){
			$return->error	=		false;
			$return->path		=		$upload_path.'/'.$nombre_archivo_md5.".".$extension_archivo;
			$return->fullurl=		$upload_url.'/'.$nombre_archivo_md5.".".$extension_archivo;
			$return->url		=		$ruta.'/'.$nombre_archivo_md5.".".$extension_archivo;
			$return->file		=		$nombre_archivo_md5.".".$extension_archivo;
			$return->ext		=		$extension_archivo;
		}else{
			$return->error	=		"Error cargando archivo";
		}
	}else {
		$return->error	=		"Archivo sobre pasa el tamaño permitido";
	}
	return $return;
}

function obtenerExtensionFichero($str){
  return @end(explode(".", $str));
}

function materias_importadas(){
	$ci 	=& 	get_instance();
	$ci->db->select("*")->from(DB_PREFIJO."materias");
	$rows	= $ci->db->order_by("materia","ASC")->get()->result();
	$return=[""=>"Seleccione la materia"];
	foreach ($rows as $key => $value) {
		$return[$value->materia]	=	$value->cod.' '.$value->materia;
	}
	return $return;
}

function usuarios_x_code($codigo,$institucion_id=false){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."usuarios";
	$ci->db->select("*")->from($tabla)
											->where("identificacion",$codigo);
	if ($institucion_id) {
		$ci->db->where("institucion_id",$institucion_id);
	}
	return $ci->db->get()->row();
}

function logs($resultado=""){
	$ci 	=& 	get_instance();
	$page_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$ci->db->insert(DB_PREFIJO."logs",[	"fecha"=>date("Y-m-d- H:i:s"),
																				"url"=>$page_url,
																				"modulo"=>get("modulo"),
																				"metodo"=>get("m"),
																				"resultado"=>json_encode($resultado),
																				"usuario_id"=>(isset($ci->user->usuario_id))?$ci->user->usuario_id:0]);
}

function tmpl($view,$data=[]){
	$ci 	=& 	get_instance();
	$view_path	=	PATH_VIEW.'/Template/tmpl/'.$view.'.php';
	if(file_exists($view_path)){
		return $ci->load->view('Template/tmpl/'.$view,["data"=>$data],TRUE);
	}else{
		return false;
	}
}

function get_observaciones($quien_hace_la_observacion_id,$quien_recibe_va_la_observacion_id){
	$ci 	=& 	get_instance();
	$ci->db->select("*")->from(DB_PREFIJO."op_observaciones_generales");
	$ci->db->where("quien_hace_la_observacion_id",$quien_hace_la_observacion_id);
	$ci->db->where("quien_recibe_va_la_observacion_id",$quien_recibe_va_la_observacion_id);
	$ci->db->order_by("fecha","DESC");
	$rows	= $ci->db->get()->result();
	return $rows;
}

function lista_de_alumnos_x_profesor(){
	$ci 	=& 	get_instance();

	$order	=	get("order");
	$search	=	get("buscar");
	$start	=	get("start");
	$length	=	get("length");
	$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;


	$tabla	=	DB_PREFIJO."usuarios t1";
	$ci->db->select("SQL_CALC_FOUND_ROWS  t1.usuario_id", FALSE)
						->select('	t1.usuario_id,
												t1.identificacion,
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


	$ci->db->like("t1.tipo_usuario_id",2);
	if($ci->user->tipo_usuario_id>0){
		$ci->db->where("docente_id",$ci->user->usuario_id);
	}
	if($search){
		$ci->db->where("identificacion",$search);
		$ci->db->or_like("nombres",$search);
	}

	$ci->db->order_by("usuario_id");
	$ci->db->group_by("usuario_id");

	if($start && $length){
		$ci->db->limit($length,$start);
	}
	$query	=	$ci->db->get();
	return	$query->result();
}

function evaluaciones_x_alumnos($alumno_id,$periodo){
	$ci 	=& 	get_instance();
	$ci->db->select("*")
					->from(DB_PREFIJO."op_alumnos_notas t1")
					->join(DB_PREFIJO."op_profesores_evaluaciones t2","t1.evaluacion_id=t2.evaluacion_id","left");
	$ci->db->where("t1.alumno_id",$alumno_id);
	$ci->db->where("t1.periodo",$periodo);
	return $ci->db->get()->result();
}

function evaluacion_profesor_alumnos($token){
	$ci 	=& 	get_instance();
	$ci->db->select("*")->from(DB_PREFIJO."op_alumnos_notas");
	$ci->db->where("token",$token);
	$rows	= $ci->db->get()->result();
	$return=[];
	foreach ($rows as $key => $value) {
		$return[$value->alumno_id]	=	$value;
	}
	return $return;
}

function evaluacion_alumnos($post){
	$ci 	=& 	get_instance();
	$ci->db->select("*")->from(DB_PREFIJO."op_alumnos_notas");
	$ci->db->where("docente_id",$post["docente_id"]);
	$ci->db->where("alumno_id",$post["alumno_id"]);
	$ci->db->where("evaluacion_id",$post["evaluacion_id"]);
	$ci->db->where("periodo",$post["periodo"]);
	$ci->db->where("token",$post["token"]);
	return $ci->db->get()->row();
}

function get_niveleducativo_gradoescolar($institucion_id,$nivel_educativo_id,$grado_escolar_id){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."op_grados_x_instituciones";
	return		$ci->db->select("*")
										->from($tabla1)
										->where("institucion_id",$institucion_id)
										->where("nivel_educativo_id",$nivel_educativo_id)
										->where("grado_escolar_id",$grado_escolar_id)
										->get()
										->result();
}

function Horario_x_profesores($institucion_id,$docente_id,$dia){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."rel_profesores_materias t1";
	return $ci->db->select("*")
								->from($tabla)
								->join(DB_PREFIJO."op_materias_x_horarios t2","t1.materia_token=t2.token_materia")
								->join(DB_PREFIJO."ma_grados_escolares t3","t2.grado_escolar_id=t3.grado_escolar_id")
								->join(DB_PREFIJO."op_materias_x_grados t4","t2.token_materia=t4.token")
								->where("t2.dia",$dia)
								->where("t1.docente_id",$docente_id)
								->where("t1.institucion_id",$institucion_id)
								->where("t1.anoescolar",date("Y"))
								->order_by("hora_desde","ASC")
								->get()
								->result();
}

function Horario_x_profesor($institucion_id,$docente_id){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."rel_profesores_materias t1";
	return $ci->db->select("*")
								->from($tabla)
								->join(DB_PREFIJO."op_materias_x_grados t2","t1.materia_token=t2.token")
								->join(DB_PREFIJO."ma_grados_escolares t3","t1.grado_escolar_id=t3.grado_escolar_id")
								->where("t1.docente_id",$docente_id)
								->where("t1.institucion_id",$institucion_id)
								->where("t1.anoescolar",date("Y"))
								->get()
								->result();
}

function evaluacion_x_id($evaluacion_id,$docente_id=false){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."op_profesores_evaluaciones";
	$ci->db->select("*")->from($tabla);
	$ci->db->where("evaluacion_id",$evaluacion_id);
	if ($docente_id) {
		$ci->db->where("docente_id",$docente_id);
	}
	return $ci->db->get()->row();
}

function evaluacion($token,$docente_id=false){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."op_profesores_evaluaciones";
	$ci->db->select("*")->from($tabla);
	$ci->db->where("token",$token);
	if ($docente_id) {
		$ci->db->where("docente_id",$docente_id);
	}
	return $ci->db->get()->row();
}

function get_acudiente_x_alumno($alumno_id){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."rel_alumnos_instituciones t1";
	$rows=	$ci->db->select(" * ")
										->from($tabla1)
										->join(DB_PREFIJO."usuarios t2","t1.acudiente_id=t2.usuario_id")
										->where("t1.usuario_id",$alumno_id)
										->get()
										->row();
	return $rows;
}

function alumno_acudiente($acudiente_id){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."rel_alumnos_instituciones t1";
	//;
	$rows=	$ci->db->select(" t1.usuario_id,
														t1.acudiente_id,
														t1.institucion_id,
														t1.grado_escolar_token,
														t2.nombres,
														t2.push_token,
														t4.grado_escolar_id,
														t3.letra,
														t4.grado_escolar,
														IF(genero='masculino', 'design/nino.png', 'design/nina.png')  as avatar")
										->from($tabla1)
										->join(DB_PREFIJO."usuarios t2","t1.usuario_id=t2.usuario_id")
										->join(DB_PREFIJO."op_grados_x_instituciones t3","t1.grado_escolar_token=t3.token")
										->join(DB_PREFIJO."ma_grados_escolares t4","t3.grado_escolar_id=t4.grado_escolar_id")
										->where("acudiente_id",$acudiente_id)
										->get()
										->result();
	return $rows;
}

function alumno_acudiente_evaluaciones($token,$usuario_id=false){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."op_grados_x_instituciones t1";
	$ci->db->select(" t1.*,
										IF(t6.nota!='null', t6.nota, 'Aún no evaluado') as evaluacion_nota,
										IF(t6.fecha!='null', t6.fecha, 'Aún no evaluado') as evaluacion_fecha,
										IF(t6.observacion!='null', t6.observacion, 'Sin Observación') as evaluacion_observacion,
										t5.nombres , t4.materia,t3.json as json_evaluacion,
										t3.evaluacion,
										t3.descripcion,
										t3.biblioteca,
										t3.fecha,
										t3.fecha as fecha_evaluacion,
										t3.token
										")
							->from($tabla1)
							->join(DB_PREFIJO."rel_profesores_materias t2","t1.letra=t2.seccion AND t1.grado_escolar_id=t2.grado_escolar_id","LEFT")
							->join(DB_PREFIJO."op_profesores_evaluaciones t3"," t2.materia_token=t3.materia_token AND t1.letra=t3.seccion","LEFT")
							->join(DB_PREFIJO."op_materias_x_grados t4","t3.materia_token=t4.token","LEFT")
							->join(DB_PREFIJO."usuarios t5","t3.docente_id=t5.usuario_id","LEFT");
	$ci->db->join(DB_PREFIJO."op_alumnos_notas t6","t3.evaluacion_id = t6.evaluacion_id AND t6.alumno_id=".$usuario_id,"LEFT");
	$rows	=	$ci->db->where("t1.token",$token)
									->where("t3.evaluacion_id !=",null)
									->order_by("t3.fecha","desc")
									->get()
									->result();
	return $rows;
}



// function alumno_acudiente($acudiente_id){
// 	$ci 	=& 	get_instance();
// 	$tabla1=	DB_PREFIJO."rel_alumnos_instituciones t1";
// 	$rows=	$ci->db->select(" t6.nombres as docente,
// 														t5.materia,
// 														t1.usuario_id,
// 														t7.evaluacion,
// 														t7.descripcion,
// 														t2.nombres as alumno ")
// 										->from($tabla1)
// 										->join(DB_PREFIJO."usuarios t2","t1.usuario_id=t2.usuario_id")
// 										->join(DB_PREFIJO."op_grados_x_instituciones t3","t1.grado_escolar_token=t3.token","LEFT")
// 										->join(DB_PREFIJO."rel_profesores_materias t4","t3.letra=t4.seccion AND t3.grado_escolar_id=t4.grado_escolar_id","LEFT")
// 										->join(DB_PREFIJO."op_materias_x_grados t5","t4.materia_token=t5.token","LEFT")
// 										->join(DB_PREFIJO."usuarios t6","t4.docente_id=t6.usuario_id","LEFT")
// 										->join(DB_PREFIJO."op_profesores_evaluaciones t7","t4.docente_id=t7.docente_id","LEFT")
// 										->where("acudiente_id",$acudiente_id)
// 										->get()
// 										->result();
// 	$return=[];
// 	if (!empty($rows)) {
// 		foreach ($rows as $key => $value) {
// 			$return[$value->usuario_id][]	=	$value;
// 		}
// 	}
// 	return ["subitems"=>$return,"items"=>$rows];
// }

function acudiente($alumno_id){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."rel_alumnos_instituciones";
	return	$ci->db->select("*")
										->from($tabla1)
										->where("usuario_id",$alumno_id)
										->get()
										->row();
}

function Docentes_x_Materias_evaluacion($docente_id){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."rel_profesores_materias t1";
	$rows	= $ci->db->select("	CONCAT(materia,' (',grado_escolar,' ',seccion,') ') AS materia ,
														materia_token as token,
														seccion")
															->from($tabla)
															->join(DB_PREFIJO."op_materias_x_grados t2","t1.materia_token=t2.token")
															->join(DB_PREFIJO."ma_grados_escolares t3","t2.grado_escolar_id=t3.grado_escolar_id")
															->where("t1.docente_id",$docente_id)
															->order_by("materia")
															->get()
															->result();
	$return=[""=>"Seleccione la materia"];
	foreach ($rows as $key => $value) {
		$return[$value->token.'::'.$value->seccion]	=	$value->materia;
	}
	return $return;
}

function Docentes_x_Materias($institucion_id){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."rel_profesores_materias t1";
	$rows	= $ci->db->select("	t1.institucion_id,
														t1.docente_id,
														t1.materia_token,
														t1.seccion,
														t1.grado_escolar_id,
														t1.anoescolar
															")->from($tabla)->join(DB_PREFIJO."usuarios t2","t1.docente_id=t2.usuario_id")->where("t1.institucion_id",$institucion_id)->get()->result();
	$return=[];
	foreach ($rows as $key => $value) {
		$return[$value->institucion_id][$value->materia_token][$value->grado_escolar_id][$value->anoescolar][$value->seccion]	=	$value->docente_id;
	}
	return $return;
}

function TodosLosUsuariosPorTipo($tipo_usuario_id,$institucion_id){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."usuarios";
	$rows	= $ci->db->select("usuario_id,nombres,apellidos")->from($tabla)->where("tipo_usuario_id",$tipo_usuario_id)->where("institucion_id",$institucion_id)->get()->result();
	$return=[""=>"Seleccione"];
	foreach ($rows as $key => $value) {
		$return[$value->usuario_id]	=	$value->nombres.' '.$value->apellidos;
	}
	return $return;
}

function materias_x_grados_x_docentes($docente_id){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."op_materias_x_grados";
	$rows	=		$ci->db->select("*")
										->from($tabla1)
										->where("institucion_id",$institucion_id)
										->get()
										->result();
	$return=[];
	if (!empty($rows)) {
		foreach ($rows as $key => $value) {
			$return[$value->nivel_educativo_id][$value->grado_escolar_id][]	=	$value;
		}
	}
	return $return;
}

function materias_x_horarios($institucion_id,$nivel_educativo_id,$grado_escolar_id,$dia,$token_materia,$letra){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."op_materias_x_horarios";
	$rows	=		$ci->db->select("*")
										->from($tabla1)
										->where("institucion_id",$institucion_id)
										->where("nivel_educativo_id",$nivel_educativo_id)
										->where("grado_escolar_id",$grado_escolar_id)
										->where("token_materia",$token_materia)
										->where("dia",$dia)
										->where("letra",$letra)
										->get()
										->result();
	return $rows;
}

function materias_x_grados_x_tokens($token){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."op_grados_x_instituciones t1";
	$tabla2=	DB_PREFIJO."op_materias_x_grados t2";
	$rows	=		$ci->db->select("t1.letra,t2.*")
										->from($tabla1)
										->join($tabla2,"t1.institucion_id=t2.institucion_id AND	t1.grado_escolar_id=t2.grado_escolar_id","left")
										->where("t1.token",$token)
										->get()
										->result();
	return $rows;
}


function materias_x_grados($institucion_id){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."op_materias_x_grados";
	$rows	=		$ci->db->select("*")
										->from($tabla1)
										->where("institucion_id",$institucion_id)
										->get()
										->result();
	$return=[];
	if (!empty($rows)) {
		foreach ($rows as $key => $value) {
			$return[$value->nivel_educativo_id][$value->grado_escolar_id][]	=	$value;
		}
	}
	return $return;
}

function ma_todos_los_grados(){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."ma_grados_escolares t1";
	$ci->db->select("*")
										->from($tabla1);
	$rows	=		$ci->db->get()->result();
	return $rows;
}

function todos_los_grados_x_institucion($institucion_id=false){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."op_grados_x_instituciones t1";
	$tabla2=	DB_PREFIJO."ma_grados_escolares t2";
	$ci->db->select("t1.*,t2.grado_escolar")
										->from($tabla1)
										->join($tabla2,"t1.grado_escolar_id=t2.grado_escolar_id","left");
	if ($institucion_id) {
		$ci->db->where("institucion_id",$institucion_id);
	}								/*->group_by("nivel_educativo_id, grado_escolar_id")*/
	$rows	=		$ci->db->order_by("grado_escolar_id,letra")
										->get()
										->result();
	return $rows;
}

function grados_relacionados($institucion_id){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."op_grados_x_instituciones t1";
	$rows	=		$ci->db->select("grado_escolar")
										->from($tabla1)
										->join(DB_PREFIJO."ma_grados_escolares t2","t1.grado_escolar_id=t2.grado_escolar_id","left")
										->where("institucion_id",$institucion_id)
										->get()
										->result();
	$return=$rows;

	return $return;
}

function grados_x_institucion_x_nivelEducativo($id,$institucion_id){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."op_grados_x_instituciones t1";
	$tabla2=	DB_PREFIJO."ma_grados_escolares t2";
	return 		$ci->db->select("t1.token,CONCAT(grado_escolar,' Sección ',letra) AS grado")
										->from($tabla1)
										->join($tabla2,"t1.grado_escolar_id=t2.grado_escolar_id","left")
										->where("t2.nivel_educativo_id",$id)
										->where("t1.institucion_id",$institucion_id)
										->get()
										->result();
}

function grados_x_institucion($institucion_id){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."op_grados_x_instituciones";
	$rows	=		$ci->db->select("count(grados_x_instituciones_id) as total,nivel_educativo_id,grado_escolar_id")
										->from($tabla1)
										->where("institucion_id",$institucion_id)
										->group_by("nivel_educativo_id, grado_escolar_id")
										->get()
										->result();
	$return=[];
	if (!empty($rows)) {
		foreach ($rows as $key => $value) {
			$return[$value->nivel_educativo_id][$value->grado_escolar_id]	=	$value->total;
		}
	}
	return $return;
}

function consulta_grados($institucion_id,$letra,$nivel_educativo_id,$grado_escolar_id){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."op_grados_x_instituciones";
	$row	=		$ci->db->select("*")
										->from($tabla1)
										->where("institucion_id",$institucion_id)
										->where("letra",$letra)
										->where("nivel_educativo_id",$nivel_educativo_id)
										->where("grado_escolar_id",$grado_escolar_id)
										->get()
										->row();
	return (empty($row))?false:$row;
}

function letras(){
	return [
					1=>"A",
					2=>"B",
					3=>"C",
					4=>"D",
					5=>"E",
					6=>"F",
					7=>"G",
					8=>"H",
					9=>"I",
					10=>"J",
				];
}

function serializar_para_select($row,$campos){
	if(isset($campos["s"])){
		$return	=	[""=>$campos["s"]];
	}else{
		$return	=	[""=>"seleccione"];
	}
	if(isset($row)&&!empty($row)){
		foreach ($row as $key => $value) {
			$k=$campos["k"];
			$v=$campos["v"];
			$return[$value->$k]	=	$value->$v;
		}
	}
	return $return;
}

function nivel_educativo(){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."ma_nivel_educativo";
	$row	=		$ci->db->select("*")
										->from($tabla1)
										/*CONDICIONAL TEMPORAL*/
										->where("nivel_educativo_id<",4)
										->get()
										->result();
	return (empty($row))?false:$row;
}

function nivel_grados_educativo(){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."ma_grados_escolares";
	$row	=		$ci->db->select("*")
										->from($tabla1)
										->get()
										->result();
	$return =	[];
	foreach ($row as $key => $value) {
		$return[$value->nivel_educativo_id][$value->grado_escolar_id]	=	$value->grado_escolar;
	}
	return (empty($row))?false:$return;
}

function historial_solicitudes_de_usuarios($cliente_id){
	$ci 	=& 	get_instance();
	$tabla=		DB_PREFIJO."op_servicios";
	$row	=		$ci->db->select("*")
										->from($tabla)
										->where("estatus",0)
										->where("cliente_id",$cliente_id)
										->get()
										->result();
	if(!empty($row)){
		return $row;
	}else{
    return [];
  }
}

function solicitudes_de_usuarios($cliente_id,$all=false){
	$ci 	=& 	get_instance();
	$tabla=		DB_PREFIJO."op_servicios";

	$row	=		$ci->db->select("*,COUNT(servicio_id) AS cantidad")
										->from($tabla)
										->where("estatus",0)
										->where("cliente_id",$cliente_id)
										->get()
										->row();
	if(!empty($row)){
		if ($all) {
			return $row;
		}else {
			return $row->cantidad;
		}

	}else{
    return 0;
  }
}

function saldo_cliente($cliente_id){
	$ci 	=& 	get_instance();
	$tabla=		DB_PREFIJO."op_servicios";

	$row	=		$ci->db->select("SUM(monto_deuda_del_cliente) AS cantidad")
										->from($tabla)
										->where("monto_deuda_del_cliente>",0)
										->where("cliente_id",$cliente_id)
										->get()
										->row();
	if(!empty($row)){
		return ($row->cantidad=='')?  format(0.00) : $row->cantidad;
	}else{
    return 0;
  }
}

function objectToArray($r){
  if (is_object($r)) {
    if (method_exists($r, 'toArray')) {
      return $r->toArray(); // returns result directly
    } else {
      $r = get_object_vars($r);
    }
  }
  if (is_array($r)) {
    $r = array_map(__FUNCTION__, $r); // recursive function call
  }
	return $r;
}

function bool_recurso($recurso_id){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."op_servicios";
	$row	=		$ci->db->select("*")
										->from($tabla1)
										->where("recurso_id",$recurso_id)
										->where("domiciliaro_entrega_id>",0)
										->where("domiciliaro_receptor_id",0)
										->get()
										->row();
	return (empty($row))?false:true;
}

function recursos($institucion_id){
	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."op_inventario";
	$row	=		$ci->db->select("*")
										->from($tabla1)
										->where("institucion_id",$institucion_id)
										->get()
										->result();
	$return=[""=>"Seleccione Lavadora"];
	if(!empty($row)){
		foreach ($row as $key => $value) {
			$value	=	campo_json_db($value);
			$return[$value->inventario_id]	=	"COD:[".$value->Codigo."], Tipo ".$value->TipoLavadora.", Capacidad ".$value->Capacidad_Peso;
		}
	}
	return $return;
}

function recursos_disponibles($institucion_id){
	$return 	=	new stdClass;
	$inventario	=	0;
	$servicios_activos	=	0;

	$ci 	=& 	get_instance();
	$tabla1=	DB_PREFIJO."op_inventario";
	$tabla2=	DB_PREFIJO."op_servicios";
	$row	=		$ci->db->select("COUNT(inventario_id) AS cantidad")
										->from($tabla1)
										->where("institucion_id",$institucion_id)
										->get()
										->row();
	if(!empty($row)){
		$inventario=$row->cantidad;
	}

	$row	=		$ci->db->select("COUNT(servicio_id) AS cantidad")
										->from($tabla2)
										->where("domiciliaro_entrega_id>",0)
										->where("domiciliaro_receptor_id",0)
										->where("institucion_id",$institucion_id)
										->get()
										->row();

	if(!empty($row)){
		$servicios_activos=$row->cantidad;
	}

	$return->inventario=$inventario;
	$return->servicios_activos=$servicios_activos;
	$return->disponibles=$inventario-$servicios_activos;
	return $return;
}

function solicitudes_de_servicios($tipo_de_solicitud,$institucion_id=false){
	$ci 	=& 	get_instance();
	$tabla=		DB_PREFIJO."op_servicios";
	if($tipo_de_solicitud=="Pendientes"){
		$ci->db->select("COUNT(servicio_id) AS cantidad")
											->from($tabla)
											->where("domiciliaro_entrega_id",0)
											->where("domiciliaro_receptor_id",0);
	  if ($institucion_id) {
	  	$ci->db->where("institucion_id",$institucion_id);
	  }
		$row	=	$ci->db->get()->row();
		if(!empty($row)){
			return $row->cantidad;
		}
	}else if($tipo_de_solicitud=="Impagas"){
		$row	=		$ci->db->select("COUNT(servicio_id) AS cantidad")
											->from($tabla)
											->where("domiciliaro_entrega_id>",0)
											->where("domiciliaro_receptor_id>",0)
											->where("monto_alquiler>monto_pagado_por_cliente")
											->get()
											->row();
		if(!empty($row)){
			return $row->cantidad;
		}
	}
}

function Horario($institucion_id){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."op_horarios";
	return $ci->db->select("*")->from($tabla)->where("institucion_id",$institucion_id)->get()->row();
}

function array_dias(){
	return ["Lun","Mar","Mie","Jue","Vie","Sab","Dom"];
}

function monetizacion_x_cliente($institucion_id,$estatus="ANY"){
	$ci 	=& 	get_instance();
	$tabla=		DB_PREFIJO."op_monetizacion";
	$ci->db->select("*,MONTH(fecha_expedicion) AS mes")
									 ->from($tabla);
	if ($institucion_id) {
		$ci->db->where("institucion_id",$institucion_id);
	}
	if($estatus!='ANY'){
		$ci->db->where("estatus",$estatus);
	}
	$rows		=		$ci->db->order_by("fecha_expedicion","ASC")->get()->result();
	$result	=		new stdClass;
	if ($institucion_id) {
		foreach ($rows as $key => $value) {
			$result->all[$key]	=	$value;
			$json=campo_json_db($value);
			$result->ventar_x_meses[$value->mes]	=	0;
			$result->ventar_x_plataforma_x_mes=$result->ventar_x_plataforma=[];
			foreach ($json->monto_bruto as $key2 => $value2) {
				$result->ventar_x_meses[$value->mes]	+=	$value2;
				$result->ventar_x_plataforma_x_mes[$value->mes][$json->plataforma[$key2]]	=	$value2;
				@$result->ventar_x_plataforma[$json->plataforma[$key2]]	+=	$value2;
			}
		}
	}else{
		$result->ventar_x_meses=[];
		$result->ventar_x_plataforma_x_mes=$result->ventar_x_plataforma=[];
		foreach ($rows as $key => $value) {
			$result->all[$key]	=	$value;
			$json=campo_json_db($value);
			foreach ($json->monto_bruto as $key2 => $value2) {
				@$result->ventar_x_meses[$value->mes]	+=	$value2;
				$result->ventar_x_plataforma_x_mes[$value->mes][$json->plataforma[$key2]]	=	$value2;
				@$result->ventar_x_plataforma[$json->plataforma[$key2]]	+=	$value2;
			}
		}
	}
	return $result;
}

function reloj($modelo_id,$tipo_id="ANY"){
	$ci 	=& 	get_instance();
	$tabla=		DB_PREFIJO."op_seguimiento_modelos_reloj";
	$ci->db->select("*")
									 ->from($tabla)
									 ->where("modelo_id",$modelo_id);
	if($tipo_id!='ANY'){
		$ci->db->where("tipo",$tipo_id);
	}
	$row	=		 $ci->db->where("fecha",date("Y-m-d"))
									 ->order_by("hora","DESC")
									 ->get()
									 ->row();
	return $row;
}

function json_adjuntos($data){
	if(!empty($data->logo)){
		$json=json_decode($data->logo);
		$data->logo	=	$json->upload_data->imagen_nueva;
	}
	if(!empty($data->contrato)){
		$json=json_decode($data->contrato);
		$data->contrato	=	$json->upload_data->imagen_nueva;
	}
	return $data;
}

function relacionar_escala_de_pagos($dias,$variable){
	foreach ($variable as $key => $value) {
		if($dias>=$value->dias_desde && $dias<=$value->dias_hasta ){
			return $value;
		}
	}
}

function escala_pagos(){
	$ci 	=& 	get_instance();
	$tabla		=	DB_PREFIJO."ma_escala_pagos";
	$rows	= $ci->db->select("*")->from($tabla)->where("estatus",1)->get()->result();
	$return=[];
	$hasta=[];
	foreach ($rows as $key => $value) {
		$return[$value->escala]	=	new stdClass;
		$return[$value->escala]->dias_desde	=	$value->dias_desde;
		$return[$value->escala]->dias_hasta	=	$value->dias_hasta;
		$return[$value->escala]->meta	=	$value->meta;
		$return[$value->escala]->tipo	=	$value->escala;
		$return[$value->escala]->bonificacion	=	$value->bonificacion;
	}
	return $return;
}

function icons($return=false){
	$icons	=	[
								"Profesores"=>'<i class="fas fa-desktop mr-2"></i>',
								"Evaluaciones"=>'<i class="fas fa-address-book mr-2"></i>',
								"Alumnos"=>'<i class="fas fa-child mr-2"></i>',
								"Biblioteca"=>'<i class="fas fa-address-book"></i>',
								"Subir_Libro_o_Texto"=>'<i class="fas fa-cloud-upload-alt mr-2"></i>',
								"Usuarios"=>'<i class="fas fa-users mr-2"></i>',
								"Principal"=>'<i class="fas fa-home mr-2"></i>',
								"Home"=>'<i class="fas fa-home mr-2"></i>',
								"Mensajes"=>'<i class="fas fa-comments mr-2"></i>',
								"Inicio"=>'<i class="fas fa-home mr-2"></i>',
								"Asistencia"=>'<i class="fas fa-user-check mr-2"></i>',
								"Calificaciones"=>'<i class="fas fa-user-check mr-2"></i>',
								"Parametrizacion"=>'<i class="fas fa-cogs"></i>',
								"Evaluaciones"=>'<i class="fas fa-thumbtack mr-2"></i>',
								"Evaluaciones"=>'<i class="fas fa-book-open mr-2"></i>',
								"Plan_de_tareas"=>'<i class="fas fa-book-open mr-2"></i>',
								"Calificaciones"=>'<i class="fas fa-user-friends mr-2"></i>',
								"Acudientes"=>'<i class="fas fa-user-alt"></i>',
								"Gestion_usuarios"=>'<i class="fas fa-user-friends mr-2"></i>',
								"Roles_usuarios"=>'<i class="fas fa-user-tag mr-2"></i>',
								"Horarios"=>'<i class="fas fa-calendar-alt mr-2"></i>',
								"Configuración_Metas"=>'<i class="fas fa-weight mr-2"></i>',
								"Centros_de_costos"=>'<i class="fas fa-store-alt mr-2"></i>',
								"Empresas"=>'<i class="fas fa-store-alt mr-2"></i>',
								"Gestion_instituciones"=>'<i class="fas fa-store-alt mr-2"></i>',
								"Logo_de_Empresa"=>'<i class="fab fa-artstation mr-2"></i>',
								"Escala_de_Pagos"=>'<i class="fas fa-money-bill-wave mr-2"></i>',
								"Gastos_Fijos"=>'<i class="fas fa-cart-arrow-down mr-2"></i>',
								"Impuestos_y_Gastos_transaccionales"=>'<i class="fas fa-file-invoice-dollar mr-2"></i>',
								"Clientes"=>'<i class="fas fa-user-alt mr-2"></i>',
								"Pagos"=>'<i class="far fa-credit-card mr-2"></i>',
								"Configuracion"=>'<i class="fas fa-cogs mr-2"></i>',
								"Importar_data"=>'<i class="fas fa-cloud-upload-alt mr-2"></i>',
								"Inventario"=>'<i class="fas fa-dolly-flatbed mr-2"></i>',
								"Password"=>'<i class="fas fa-key mr-2"></i>',
								"SessionClose"=>'<i class="fas fa-lock mr-2"></i>',
								"Add"=>'<i class="fas fa-plus-square mr-2"></i>',
								"Lista_de_modelos"=>'<i class="fas fa-female mr-2"></i>',
								"Iniciar_jornada"=>'<i class="fas fa-play mr-2"></i>',
								"Inasistencia_jornada"=>'<i class="fas fa-sad-tear mr-2"></i>',
							];
	if(!$return){
		return $icons;
	}else{
		return (isset($icons[$return]))?$icons[$return]:"";
	}
}



function TodosLosUsuarios($tipo_usuario_id){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."usuarios";
	$rows=$ci->db->select("usuario_id,nombres")->from($tabla)->where("tipo_usuario_id",$tipo_usuario_id)->get()->result();
	$return=[""=>"Seleccione usuario"];
	foreach ($rows as $key => $value) {
		$return[$value->usuario_id]	=	$value->nombres;
	}
	return $return ;
}

function buscar_usuario_x_nombre($nombres,$tipo_usuario_id){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."usuarios";
	return $ci->db->select("*")->from($tabla)
									->where("nombres",$nombres)
									->where("tipo_usuario_id",$tipo_usuario_id)
									->get()
									->row();
}

function factura($monetizacion_id){
	$tabla	=	 	DB_PREFIJO."cf_monetizacion";
	$ci 	=& 	get_instance();
	return $ci->db->select("*")
					->from($tabla)
					->where("monetizacion_id",$monetizacion_id)
					->get()
					->row();
}

function set_template_PDF($var){
	$ci 	=& 	get_instance();
	$view	=	PATH_VIEW.'/Template/PDF/'.$var['view'].'.php';
	if(file_exists($view)){
		return $ci->load->view('Template/PDF/'.$var['view'],$var['data'],TRUE);
	}else{
		return false;
	}
}

function fecha($fecha,$formato="d/m/Y"){
	/**/
	$date	=	date_create($fecha);
	return date_format($date,$formato);
}

function config($id=1){
		$tabla	=	 	DB_PREFIJO."cf_monetizacion";
		$ci 	=& 	get_instance();
		$rows	=$ci->db->select("*")
						->from($tabla)
						->where("configuracion_id",$id)
						->get()
						->row();
		if(!empty($rows) && isset($rows->json)){
			$json_decode=json_decode($rows->json);
			if($id==1){
				return $json_decode->comision;
			}else{
				return $json_decode;
			}
		}else{
			return 0;
		}
}

function institucion($institucion_id){
		$tabla	=	 	DB_PREFIJO."instituciones";
		$ci 	=& 	get_instance();
		$rows	=$ci->db->select("*")
						->from($tabla)
						->where("institucion_id",$institucion_id)
						->get()
						->row();
		return $rows;
}

function institucion_x_token($token){
		$tabla	=	 	DB_PREFIJO."instituciones";
		$ci 	=& 	get_instance();
		$rows	=$ci->db->select("*")
						->from($tabla)
						->where("token",$token)
						->get()
						->row();
		return $rows;
}

function instituciones(){
		$tabla	=	 	DB_PREFIJO."instituciones";
		$ci 	=& 	get_instance();
		$rows	=$ci->db->select("*")
						->from($tabla)
						->where("estatus",1)
						->get()
						->result();
		$return=[""=>"Seleccione la Institución"];
		foreach ($rows as $key => $value) {
			$return[$value->institucion_id]	=	$value->nombre_legal;
		}
		return $return;
}

function plataformas(){
		$tabla	=	 	DB_PREFIJO."ma_plataformas";
		$ci 	=& 	get_instance();
		$rows	=$ci->db->select("*")
						->from($tabla)
						->where("estatus",1)
						->get()
						->result();
		$return=[""=>"Seleccione la Plataforma"];
		foreach ($rows as $key => $value) {
			$return[$value->plataforma]	=	$value->plataforma;
		}
		return $return;
}

function campo_json_db($row){
	if(isset($row->json)){
		$json=json_decode($row->json);
		foreach ($json as $key => $value) {
			$row->$key=$value;
		}
	}
	return $row;
}

function producto($id=0){
		$tabla	=	 	DB_PREFIJO."ma_productos";
		$ci 	=& 	get_instance();
		$row	=$ci->db->select("*")
						->from($tabla)
						->where("tipo_id",$id)
						->get()
						->row();
		return $row;
}

function productos($parent_id=0){
		$tabla	=	 	DB_PREFIJO."ma_productos";
		$ci 	=& 	get_instance();
		$ci->db->select("*")->from($tabla);
		if($parent_id!='ANY'){
			$ci->db->where("parent_id",$parent_id);
		}else if($parent_id==='0'){
			$ci->db->where("parent_id",0);
		}else{
			$ci->db->where("parent_id",$parent_id);
		}
		$rows	= $ci->db->order_by("tipo")->get()->result();
		$return=[0=>"Seleccione"];
		foreach ($rows as $key => $value) {
			$return[$value->tipo_id]	=	$value->tipo;
		}
		return $return;
}

function inventarios_items(){
	$tabla1	=	 	DB_PREFIJO."op_inventario t1";
	$tabla2	=	 	DB_PREFIJO."ma_inventario_tipo t2";
	$ci 	=& 	get_instance();
	$sql		=		'SELECT COUNT(t1.inventario_id) as cantidad,
									t1.inventario_id,
									t1.factura,
									t2.tipo,
									t2.codigo,
									(SELECT COUNT(t2.inventario_id)
										FROM '. DB_PREFIJO .'op_inventario t2
											WHERE t2.factura=t1.factura AND t2.room>0) AS asignadas,
									COUNT(t1.inventario_id) - (SELECT COUNT(t2.inventario_id) FROM '. DB_PREFIJO .'op_inventario t2 WHERE t2.factura=t1.factura AND t2.room>0) AS restantes
									FROM '.$tabla1.'
										LEFT JOIN '.$tabla2.' ON t1.tipo_id = t2.tipo_id
											GROUP BY factura
												ORDER BY room ASC';
	$return=[];
	foreach ($ci->db->query($sql)->result() as $key => $value) {
		if($value->restantes>0){
			$return[$value->inventario_id]	=	$value->tipo.' (Restante:'.$value->restantes.') ';
		}
	};
	return $return;
}

function ma_inventario_tipo($tipo){
	$ci 	=& 	get_instance();
	$tabla=		DB_PREFIJO."ma_productos";
	$row	=		$ci->db->select("*")->from($tabla)->where("parent_id",$tipo)->order_by("tipo")->get()->result();
	$return=[];
	foreach ($row as $key => $value) {
		$return[$value->tipo_id]	=	$value->tipo;
	}
	return $return;
}

function token(){
	return md5(rand(2000,933333).date("Y-m-d H:i:s"));
}

function get_estatus($tabla,$token){
	$ci 	=& 	get_instance();
	$tabla=		DB_PREFIJO.$tabla;
	if($ci->db->select("estatus")->from($tabla)->where("token",$token)->or_where("institucion_id",$token)->get()->row()->estatus==1){
		$ci->db->where("token",$token);
		$ci->db->or_where("institucion_id",$token);
		$ci->db->update($tabla,["estatus"=>0]);
	}else{
		$ci->db->where("token",$token);
		$ci->db->or_where("institucion_id",$token);
		$ci->db->update($tabla,["estatus"=>1]);
	}
}

function gasto_fijo_token($token){
	$ci 		=& 	get_instance();
	$tabla	=		DB_PREFIJO."op_gastos_fijos";
	$row		=		$ci->db->select("*")->from($tabla)->where("token",$token)->get()->row();
	return $row;
}

function centros_de_costos($institucion_id=false){
	$ci 	=& 	get_instance();
	$tabla	=	DB_PREFIJO."centros_de_costo";
	$ci->db->select("*")->from($tabla);
	if($institucion_id){
		$ci->db->where("institucion_id",$institucion_id);
	}
	$rows		=	$ci->db->get()->result();
	return $rows;
}

function centro_de_costo($token){
	$ci 	=& 	get_instance();
	$tabla						=		DB_PREFIJO."centros_de_costo";
	$row=$ci->db->select("institucion_id,nombre,email,telefono,token,estatus,direccion,n_rooms,json")->from($tabla)->where("token",$token)->get()->row();
	$return	=	$row;
	$json		=	json_decode($row->json);
	if(!empty($json)){
		foreach ($json as $key => $value) {
			$return->$key=$value;
		}
	}
	return $return;
}

function numero_rooms_transmision($centro_de_costos){
	$ci 	=& 	get_instance();
	$tabla						=		DB_PREFIJO."centros_de_costo";
	return $ci->db->select("n_rooms")->from($tabla)->where("user_id",$centro_de_costos)->get()->row()->n_rooms;
}

function perfil_seleccionado($id){
	$ci 	=& 	get_instance();
	return $ci->db->select("*")
									->from(DB_PREFIJO."usuarios_rel_instituciones t1")
									->join(DB_PREFIJO."instituciones t2","t1.institucion_id=t2.institucion_id","left")
									->join(DB_PREFIJO."tipo_usuarios t3","t1.tipo_usuario_id=t3.tipo_usuario_id","left")
									->where("id",$id)->get()->row();
}

function perfiles_disponibles_x_usuario($usuario_id){
	$ci 	=& 	get_instance();
	return $ci->db->select("nombre_comercial,tipo,id")
									->from(DB_PREFIJO."usuarios_rel_instituciones t1")
									->join(DB_PREFIJO."instituciones t2","t1.institucion_id=t2.institucion_id","left")
									->join(DB_PREFIJO."tipo_usuarios t3","t1.tipo_usuario_id=t3.tipo_usuario_id","left")
									->where("usuario_id",$usuario_id)->get()->result();
}

function ma_grados($pais_id=114){
	$ci 	=& 	get_instance();
	$rows	=$ci->db->select("*")->from(DB_PREFIJO."v_grados_escolares")->where("pais_id",$pais_id)->get()->result();
	$return		=		new stdClass();
	$return->niveles	=	$return->grados	=	[];

	foreach ($rows as $key => $value) {
		$return->niveles[$value->nivel_educativo_id]	=		$value->nivel_educativo;
		$return->grados[$value->nivel_educativo_id][]	=		$value;
	}
	return $return;
}

function tipos_de_usuarios(){
	$ci 		=&get_instance();
	$rows		= $ci->db->select("*")->from(DB_PREFIJO."tipo_usuarios")->get()->result();
	$return	=	[""=>"Seleccione"];
	if (!empty($rows)) {
		foreach ($rows as $key => $value) {
			$return[$value->tipo_usuario_id]=$value->tipo;
		}
	}
	return $return;
}

function tipo_usuario($id){
	$ci 	=& 	get_instance();
	return $ci->db->select("*")->from(DB_PREFIJO."tipo_usuarios")->where("tipo_usuario_id",$id)->get()->row();
}

function data_null($array){
	$return	=	new stdClass();
	foreach ($array as $value) {
		$return->$value	=	"";
	}

	return $return;
}
function update_data($primary_key,$tabla,$campos,$variables,$data){
	$ci 	=& 	get_instance();
	$ci->db->select($primary_key)->from($tabla);
	if(!empty($campos)){
		$inc=0;
		foreach ($campos as $value) {
			if($inc==0){
				$ci->db->where($value,$variables[$value]);
			}else {
				$ci->db->or_where($value,$variables[$value]);
			}
			$inc++;
		}
	}
	$query	=	$ci->db->get();
	$row	= $query->row();
	//pre($ci->db->last_query());	pre($row);	return ;
	if(empty($row)){
		$ci->db->insert($tabla,$data);
		return $ci->db->insert_id();
	}else{
		$ci->db->where($primary_key,$row->$primary_key);
		$ci->db->update($tabla,$data);
		return $row->$primary_key;
	}
}

function campos_tabla($tabla){
	$ci 	=& 	get_instance();
	return $ci->db->list_fields($tabla);
}

function corrector_diccionario($str){
	return str_replace(array("cion","_","add","exc"),array("ción"," ","Agregar"," "),$str);
}

function get_image($image,$html=true,$alt="",$class="img-fluid"){
	/*BUSCO SI EXISTE LA IMAGEN*/
	$path_img = PATH_IMG.$image;
	if(file_exists($path_img)){
		if($html){
			echo '<img src="'.IMG.$image.'" class="'.$class.'"  alt="'.$alt.'"/>';
		}else{
			return IMG.$image;
		}
	}else{
		if($html){
			echo '<img src="'.IMG.'default.png" class="'.$class.'"  alt="'.$alt.'"/>';
		}else{
			return IMG.'default.png';
		}
	}
}

function genera_token($usuario_id=0){
	$ci 	=& 	get_instance();
	$ci->load->helper("security");
	$semilla	=		(post("token"))?post("token"):date("Y-m-d H:i:s");
	$token		= 	do_hash($semilla);
	$rows			=		$ci->db->select("token")
												->from(DB_PREFIJO."sesiones")
												->where("usuario_id",$usuario_id)
												->get()
												->row();

	if(empty($rows)){
		$ci->db->insert(DB_PREFIJO."sesiones",array("fecha"=>date("Y-m-d H:i:s"),"token"=>$token,"usuario_id"=>$usuario_id));
		$session_id	=	$token;
	}else {
		$ci->db->where("usuario_id",$usuario_id);
		//$ci->db->update(DB_PREFIJO."sesiones",array("fecha"=>date("Y-m-d H:i:s"),"token"=>$token));
		$ci->db->update(DB_PREFIJO."sesiones",array("fecha"=>date("Y-m-d H:i:s")));
		$session_id	=	$rows->token;
	}
	return	$session_id;
}

function quien_hace_la_peticion($token){
	$table=DB_PREFIJO."sesiones t1";
	$ci 	=& 	get_instance();
	$row	=	$ci->db->select("*")
									->from($table)
									->join(DB_PREFIJO."usuarios t2"," t1.usuario_id=t2.usuario_id","left")
									->where("t1.token",$token)
									->get()
									->row();
	return $row;
}

function traer_metodo($_this){
	$ci 	=& 	get_instance();
	$metodo	=	$ci->uri->segment(3);
	if(method_exists($_this,$metodo)){
		return $ci->uri->segment(3);
	}else{
		false;
	}
}

function get_ci(){
	$ci 	=& 	get_instance();
	return $ci->session->userdata("country");
}

function cambiar_amps(){
	$ci 	=& 	get_instance();
	$rows						=		$ci->db->select("*")
																->from(DB_PREFIJO."disciplinas")
																->get()
																->result();

	foreach ($rows as $key => $value) {
		$nueva_carrera	=	str_replace("&amp;","&",$value->carrera);
		$ci->db->where("id",$value->id);
		$ci->db->update(DB_PREFIJO."disciplinas",array("carrera"=>$nueva_carrera));
	}
}

function _parse_url($url,$param=""){
$query_str = parse_url($url, PHP_URL_QUERY);
	parse_str($query_str, $query_params);
	if($param!=''){
		return $query_params[$param];
	}else{
		return $query_params;
	}
}

function set_image_remote($source,$dest,$name){
	/*PREPARAR DIRECTORIO*/
	if(!is_dir($dest)){
		mkdir($dest, 0755);
	}
	/*quito parametros de redimensión remoto*/
	$source = 	preg_replace('#\?(.*)#',"", $source);
	$source	=	explode("?",$source);
	$source	=	$source[0];
	/*extraer imagen y renombro*/
	$filename 	= 	pathinfo($source, PATHINFO_FILENAME);
	$ext 		= 	pathinfo($source, PATHINFO_EXTENSION);
	$dest2 		= 	$dest.'/'.$name.'.'.$ext;
	if(strpos ($source,"http://")===FALSE && strpos ($source,"https://")===FALSE){
		$source	=	'http:'.$source;
	}
	if($source!='http:' && $file_get_contents=@file_get_contents($source)){
		return @file_put_contents($dest2, $file_get_contents);
	}else{
		return false;
	}
}

function model_save($table,$primary_keyName,$data){
	$ci 	=& 	get_instance();
	$ci->db->select()->from(DB_PREFIJO.$table);
	if(is_array($primary_keyName)){
		foreach($primary_keyName as $v){
			$ci->db->where($v,$data[$v]);
		}
	}
	$query	=	$ci->db->get();
	$row	=	$query->row();

	if(empty($row)){
		$ci->db->insert(DB_PREFIJO.$table,$data);
	}else{
		if(is_array($primary_keyName)){
			foreach($primary_keyName as $v){
				$ci->db->where($v,$data[$v]);
			}
		}
		$ci->db->update(DB_PREFIJO.$table,$data);
	}
}

function get_pais($pais,$pais_id=false){
	$ci=& 	get_instance();
	$ci->db->reset_query();
	$tabla	=	DB_PREFIJO."paises";
	$ci->db->select('*')->from($tabla);
	if($pais_id){
		$ci->db->like("pais_id",$pais_id);
	}else{
		$ci->db->like("pais",$pais);
	}
	$query	=	$ci->db->get();
	$row		=	$query->row();
	if(!empty($row) && !$pais_id){
		return $row->pais_id;
	}else if(!empty($row) && $pais_id){
		return $row->pais;
	}else{
		return	"NULL";
	}
}

function get_ciudad($ciudad,$pais_id){
	$ci 	=& 	get_instance();
	$tabla	=	DB_PREFIJO."ciudades";
	$ci->db->select('id')->from($tabla)->like("ciudad",$ciudad)->where("pais_id",$pais_id);
	$query	=	$ci->db->get();
	$row		=	$query->row();
	if(!empty($row)){
		return $row->id;
	}else{
		return	"NULL";
	}
}

function template_header($replace=''){
	$html 	=	file_get_contents(PATH_BASE_APP.'../'.TEMPLATE.'/header.php');
	return 	str_replace(array("{htm}","{metatags}","{template}"), array(DOMINIO,$replace,TEMPLATE),$html);
}

function template_footer(){
	$html 	=	file_get_contents(PATH_BASE_APP.'../'.TEMPLATE.'/footer.php');
	return 	str_replace(array("{htm}","{template}"), array(DOMINIO,TEMPLATE),$html);
}

function session_flash($simulacion=false){
	$ci 	=& 	get_instance();
	if($error = $ci->session->flashdata('error')){
		echo '<div class="alert alert-danger">';
		echo $error;
		echo '<i class="glyphicon glyphicon-alert"></i></div>';
	}elseif($info = $ci->session->flashdata('info')){
		echo '<div class="alert alert-info">';
		echo $info;
		echo '<i class="glyphicon  glyphicon-ok"></i></div>';
	}else if($success = $ci->session->flashdata('success')){
		echo '<div class="alert alert-success">';
		echo $success;
		echo '<i class="glyphicon  glyphicon-ok"></i></div>';
	}else if($success = $ci->session->flashdata('danger')){
		echo '<div class="alert alert-danger">';
		echo $success;
		echo '<i class="glyphicon  glyphicon-ok"></i></div>';
	}else{
		if($simulacion){
			echo '<div class="alert alert-success">';
			echo "Prueba de Texto simulado";
			echo '<i class="glyphicon  glyphicon-ok"></i></div>';
		}
	}
}

function View($view,$data=array(),$Apanel=""){
	$ci =& 	get_instance();
	$ci->file_exists = file_exists(PATH_VIEW.'/Template/'.$view.'.php');
	$ci->load->view('Template/Header'.$Apanel);
	if($Apanel=='_Apanel'){
		$ci->load->view('Template/Apanel/Menu');
	}
	$ci->load->view('Template/Flash');
	if(isset($ci->Breadcrumb_bool) && $ci->Breadcrumb_bool){
		$ci->load->view('Template/Breadcrumb');
	}
	if($ci->file_exists){
		$ci->load->view('Template/'.$view,$data);
	}else{
		$ci->load->view('Template/Error_NoView',array("View"=>$view));
	}
	$ci->load->view('Template/Footer'.$Apanel);
}

function post($var=""){
	$ci 	=& 	get_instance();
	if($var==''){
		return $ci->input->post();
	}else{
		return $ci->input->post($var, TRUE);
	}
}

function get($var=""){
	$ci 	=& 	get_instance();
	if($var==''){
		return $ci->input->get();
	}else{
		return $ci->input->get($var, TRUE);
	}
}

function decraped_logs($user,$tipo_transaccion,$tabla_afectada,$registro_afectado_id=NULL,$modulo_donde_produjo_cambio=NULL,$accion=1,$json=array()){
	$ci 	=& 	get_instance();
	$ci->db->insert(DB_PREFIJO."tipo_usuario_idlogs",array(
									"fecha"=>date("Y-m-d H:i:s"),
									"usuario_id"=>$user->usuario_id,
									"tipo_transaccion"=>$tipo_transaccion,
									"tabla_afectada"=>$tabla_afectada,
									"registro_afectado_id"=>$registro_afectado_id,
									"modulo_donde_produjo_cambio"=>$modulo_donde_produjo_cambio,
									"accion"=>$accion,
									"json"=>json_encode($json)));

}

function ini_session($user){
	$ci 	=& 	get_instance();
	$session_id		=	md5(date("Y-m-d H:i:s"));
	if(is_object($user)){
		$user->session_id		=	$session_id;
		$insert					=	$ci->db->insert(DB_PREFIJO."sesiones",array(	"fecha"=>date("Y-m-d H:i:s"),
																					"usuario_id"=>$user->usuario_id,
																					"session_id"=>$user->session_id));
	}else if(is_array($user)){
		$user['session_id']		=	$session_id;
		$insert					=	$ci->db->insert(DB_PREFIJO."sesiones",array(	"fecha"=>date("Y-m-d H:i:s"),
																					"usuario_id"=>$user["usuario_id"],
																					"session_id"=>$user["session_id"]));
	}
	if($insert){
		return $user;
	}else{
		return false;
	}
}

function chequea_session($user){
	$ci 					=& 	get_instance();
	$session							=		$ci->db->select('*')->from(DB_PREFIJO."sesiones")->where('token',$user->session_id)->get()->row();
	$fechaGuardada 				= 	@$session->fecha;
	$ahora 								= 	date("Y-m-d H:i:s");
	$tiempo_transcurrido 	= 	(strtotime($ahora)-strtotime($fechaGuardada));

	if($tiempo_transcurrido>=SESSION_TIME){
		redirect(base_url("autenticacion/salir"));
	}else{
		$ci->db->where('session_id', $user->session_id);
		$ci->db->update(DB_PREFIJO."sesiones",array("fecha"=>$ahora));
	}
}

function tiempo_session($user){
	$ci 					=& 	get_instance();
	$session				=	$ci->db->select('*')->from(DB_PREFIJO."sesiones")->where('usuario_id',$user->usuario_id)->get()->row();
	$fechaGuardada 			= 	$session->fecha;
	$ahora 					= 	date("Y-m-d H:i:s");
	$tiempo_transcurrido 	= 	(strtotime($ahora)-strtotime($fechaGuardada));
	$user->session_id		=	$session->session_id;
	if($tiempo_transcurrido>=SESSION_TIME){
		destruye_session($user);
		return ini_session($user);
	}else{
		return false;
	}
}

function destruye_session($user){
	$ci 					=& 	get_instance();
	if(is_object($user)){
		$ci->db->where('session_id', $user->session_id);
		$ci->db->delete(DB_PREFIJO."sesiones");
		return true;
	}else{
		$ci->db->where('session_id', $user["session_id"]);
		$ci->db->delete(DB_PREFIJO."sesiones");
		return true;
	}
}

function Destroy($session_id){
	$ci 					=& 	get_instance();
	$ci->db->where('session_id', $session_id);
	$ci->db->delete(DB_PREFIJO."sesiones");
}

function answers_json($array){
	return json_encode($array);
}

function menu($tipo_id=NULL,$modulos,$no_listas=array()){
	$menu=null;
	if(!is_null($tipo_id) && $tipo_id>0){
		$ci 	=& 	get_instance();
		$tabla						=	DB_PREFIJO."tipo_usuarios";
		$ci->db->select("*")->from($tabla);
		if(!empty($tipo_id)){
			$ci->db->where("tipo_usuario_id",$tipo_id);
		}
		$roles						=	$ci->roles					=	$ci->db->get()->row();
		$menu_						=	json_decode($roles->privilegios);
		foreach($menu_ as $k =>$v){
			foreach($v as $key => $value){
				$menu[$k][]		=	$key;
			}
		}
	}else{
		foreach($modulos as $k =>$v){
			foreach($v as $k2 => $v2){
				if(!in_array($v2,$no_listas)){
					$menu[$k]	=	$v;
				}
			}

		}

	}
	return $menu;
}

function set_input($name,$row,$placeholder='',$require=false,$class='',$extra=NULL){
	$data = array(
		'type' 			=> 	(isset($extra["type"]))?$extra["type"]:'text',
		'name'  		=> 	$name,
		'id'    		=> 	@$extra["id"],
		'placeholder' 	=> 	$placeholder,
		'class' 		=> 	'form-control '.$class
	);
	if(is_array($extra)){
		foreach($extra as $k => $v){
			$data[$k]	=	$v;
		}
	}
	if($require){
		$data['require']=	$require;
		$data['required']=	'required';
	}
	if(is_object($row)){
		if(isset($row->$name)){
			$data['value']	=	set_value($name, $row->$name);
		}
	}else if(!empty($row)){
		$data['value']	=	 $row ;
	}
	echo form_input($data);
}

function btn_add($add=true,$print=true,$excel=true,$back=false){
	$ci=&get_instance();
	if($ci->input->is_ajax_request() || $ci->uri->segment(5)=='Iframe'){return;}
	$return 	=	'<div class="container">';
		$return 	.=	'<div class="section_">';
			$return 	.=	'<div class="row">';
				$return 	.=	'<div class="col-md-6">';
					$return 	.=	'<h3>';
						$return 	.=	$ci->Breadcrumb;
					$return 	.=	'</h3>';
				$return 	.=	'</div>';
				$return 	.=	'<div class="col-md-6">';
					$return 	.=	'<div class="btn-group float-right" role="group" aria-label="Basic example">';
						if($add){
							if($add=="NL"){
								$return 	.=	'<a href="'.current_url().'/Add/" title="Agregar Registro" class="btn btn-primary "><i class="fas fa-plus"></i></a>';
							}else if($add=="LG"){
								$return 	.=	'<a href="'.current_url().'/Add/0/Iframe" title="Agregar Registro" class="btn btn-primary lightbox" data-type="iframe" data-form="true" data-size="modal-lg" data-btnsuccess="true" ><i class="fas fa-plus"></i></a>';
							}else{
								$return 	.=	'<a href="'.current_url().'/Add/0/Iframe" title="Agregar Registro" class="btn btn-primary lightbox" data-type="iframe" data-form="true" data-size="modal-md" data-btnsuccess="true" ><i class="fas fa-plus"></i></a>';
							}
						}
						if($print){
							$return 	.=	'<a href="'.current_url().'/Print" title="Procesar Impresión" class="btn btn-primary"><i class="fas fa-print"></i></a>';
						}
						if($excel){
							$return 	.=	'<a href="'.current_url().'/Excel" title="Exportar a Excel" class="btn btn-primary"><i class="fas fa-file-excel"></i></a>';
						}
						if($back){
							//pre($back);
							if($back==true){
								if (isset($_SERVER['HTTP_REFERER'])){
									$url = $_SERVER['HTTP_REFERER'];
								}else{
									$url = base_url("Apanel");
								}
								$return 	.=	'<a href="'.$url.'" title="Regresar" class="btn btn-primary "><i class="fas fa-chevron-left"></i></a>';
								//$return 	.=	'<a  title="Volver atrás" class="btn btn-primary historyback" ><i class="fas fa-chevron-left"></i></a>';
							}else{
								//$return 	.=	'<a href="'.$back.'" title="Agregar Registro" class="btn btn-primary "><i class="fas fa-chevron-left"></i></a>';
							}
						}
					$return 	.=	'</div>';
				$return 	.=	'</div>';
			$return 	.=	'</div>';
		$return 	.=	'</div>';
	$return 	.=	'</div>';
	return $return;
}

function columnas($campo){
	$return		=	'';
	$lastkey 	= 	count($campo) - 1;
	$count		=	0;
	foreach($campo as $k => $v){
		if($count==$lastkey || $k=='estatus' || $k=='id'){
			$return		.=	'<th data-columna="'.$k.'" class="text-left">';
		}else{
			$return		.=	'<th data-columna="'.$k.'" class="text-left">';
		}
			$return		.=	$v;
		$return		.=	'</th>';
		$count++;
	}
	return $return;
}

function foreach_edit($data){
	$ci=&get_instance();
	$return	=	array();
	foreach($data as $k => $v){
		$token="";
		if(is_array($v) && isset($v["token"])){
			$token=$v["token"];
		}else	if(isset($v->token)){
			$token=$v->token;
		}
		// if(isset($v["token"])){
		// 	$token=$v["token"];
		// }
		$id	=	'';
		foreach($v as $k2 => $v2){
			if($k2=='id'){
				$id	=	$v2;
			}
			if($k2=='nombre'){
				$nombre	=	$v2;
			}
			$explode	=	explode("::",$k2);
			$pdf			=	'';
			if($v2=="pdf"){
				$pdf		=	'<a target="_blank" class="ml-2" href="'.base_url("ApiRest/get?modulo=".get("controller")."&m=downloadPdf&token=".$token).'"><i class="far fa-file-pdf"></i></a>';
			}
			if($k2=="edit"){
				if(get("me")){
					$return[$k][$k2]	=		'<a class="edit" title="Editar" href="'.base_url("Apanel/".get("controller")).'?m='.get("me").'&view='.get("view").'&id='.$id.'"><i class="fas fa-edit"></i></a>'.$pdf;
				}else{
					$return[$k][$k2]	=		'<a class="edit" title="Editar" href="'.base_url("Apanel/".get("controller")).'?m='.get("modulo").'&view=add_'.get("modulo").'&id='.$id.'"><i class="fas fa-edit"></i></a>';
				}
			}else if($k2=="login_link"){
				if ($v2!='') {
					$return[$k][$k2]	=		'<a class="login_link" title="Buscar" href="'.base_url("Apanel/Instituciones?m=exc_Gestion_instituciones&view=Panel&back=history&tab=false&sv=AlumnosGrados&buscar=".$v2).'">'.$v2.'</a>';
				}else {
					$return[$k][$k2]	=		$v["login"];
				}
			}else if($k2=="login_link_representante"){
				$return[$k][$k2]	=		'<a class="login_link" title="Buscar" href="'.base_url("Apanel/Instituciones?m=exc_Gestion_instituciones&view=Panel&back=history&tab=false&sv=AlumnosGrados&buscar=".$v["login_link_representante"]).'">'.$v2.'</a>';
			}else if($k2=="representante"){
				$return[$k][$k2]	=		'<a class="login_link" title="Buscar" href="'.base_url("Apanel/Instituciones?m=exc_Gestion_instituciones&view=Panel&back=history&tab=false&sv=AlumnosGrados&buscar=".$v["login_link"]).'">'.$v2.'</a>';
			}else if($k2=="representado"){
				if ($v["login_link"]!='') {
					$return[$k][$k2]	=		'<a class="login_link" title="Buscar" href="'.base_url("Apanel/Instituciones?m=exc_Gestion_instituciones&view=Panel&back=history&tab=false&sv=AlumnosGrados&buscar=".$v["login_link"]).'">'.$v2.'</a>';
				}else {
					$return[$k][$k2]	=		$v2;
				}
			}else if($k2=="view"){
				$return[$k][$k2]	=		'	<a class="edit" title="Ver" data-toggle="modal" data-target="#lateral" href="'.base_url("ApiRest/post?modulo=".get("controller")."&m=".get("me")."&formato=none&id=".$id).'"><i class="fas fa-edit"></i></a>';
			}else if($k2=="edit_evaluacion"){
				$return[$k][$k2]	=		'<a class="edit mr-2" title="Editar" href="'.base_url("Apanel/".get("controller")).'?m='.get("me").'&view='.get("view").'&id='.$id.'"><i class="fas fa-edit"></i></a>';
				$return[$k][$k2]	.=	'<a class="evaluar" title="Evaluar" href="'.base_url("Apanel/".get("controller")).'?m='.get("me").'&view='.get("sv").'&id='.$id.'"><i class="fas fa-chalkboard-teacher"></i></a>';
			}else if($k2=="edit_email"){
				$return[$k][$k2]	=		'	<a class="edit" title="Editar" href="'.base_url("Apanel/".get("controller")).'?m='.get("modulo").'&view=add_'.get("modulo").'&id='.$id.'"><i class="fas fa-edit"></i></a>
																<a class="edit" title="Email" href="'.base_url("Apanel/".get("controller")).'?m=exc_sendmail&action=Gastos_Fijos&id='.$id.'"><i class="fas fa-envelope"></i></a>
																';
			}else if($k2=="edit_delete"){
				$return[$k][$k2]	=		'	<a class="edit" title="Editar" href="'.base_url("Apanel/".get("controller")).'?m='.get("me").'&view='.get("view").'&id='.$id.'"><i class="fas fa-edit"></i></a>
																<a class="Eliminar" title="Eliminar Solicitud" href="'.base_url("ApiRest/post?modulo=Operatividad&m=delServicios&formato=redirect").'&id='.$id.'"><i class="fas fa-trash"></i></a>'.$pdf;
			}else if($k2=="edit_setuser"){
				if($v["institucion_id"]	==	$ci->user->institucion_id ){
					$return[$k][$k2]	=		'<div class="text-center">
																		<a class="edit" title="Editar" href="'.base_url("Apanel/".get("controller")).'?m='.get("me").'&view='.get("view").'&id='.$id.'"><i class="fas fa-edit"></i></a>
																		<a class="" data-type="iframe" title="Iniciar Sesión como " xhref="'.base_url(get("controller")).'?m=setSesionUsuario&view='.get("view").'&id='.$id.'"><i class="fas fa-toggle-on"></i></a>
																</div>';
				}else{
					$return[$k][$k2]	=		'<div class="text-center">
																		<a class="edit" title="Editar" href="'.base_url("Apanel/".get("controller")).'?m='.get("me").'&view='.get("view").'&id='.$id.'"><i class="fas fa-edit"></i></a>
																		<a class="" data-type="iframe" title="Iniciar Sesión como " href="'.base_url(get("controller")).'?m=exc_setSesionUsuario&view='.get("view").'&id='.$id.'"><i class="fas fa-toggle-off"></i></a>
																</div>';
				}
			}else if($k2=="monto"){
				$return[$k][$k2]	=		'<div class="text-right" title="Tokens">'.$v2.'</a>';
			}else if($k2=="view"){
				$return[$k][$k2]	=	'<div class="text-center"><a target="_blank" class="ml-2" href="'.base_url("ApiRest/get?modulo=".get("controller")."&m=downloadPdf&token=".$token).'"><i class="far fa-file-pdf"></i></a></div>';
			}else if($k2=="estatus"){
				$return[$k][$k2]	=		($v2==1)?'Activo':'Inactivo';
			}else if($k2=="switch"){
				$return[$k][$k2]	=		($v2==1)?'<div class="text-center">
																					<a href="'.base_url("ApiRest/push").'?modulo='.get("modulo").'&m=toggle_'.get("modulo").'&t='.$v["tabla"].'&id='.$id.'&formato=redirect&history=true">
																						<i class="fas fa-toggle-on"></i>
																					</a>
																				</div>':'
																				<div class="text-center">
																					<a href="'.base_url("ApiRest/push").'?modulo='.get("modulo").'&m=toggle_'.get("modulo").'&t='.$v["tabla"].'&id='.$id.'&formato=redirect&history=true">
																						<i class="fas fa-toggle-off"></i>
																					</a>
																				</div>';
			}else if($k2=="profile"){
				$return[$k][$k2]	=		'<div class="text-center">
																	<a class="lightbox" href="'.base_url("Apanel/".get("controller")).'?m='.get("me").'&view=add_profile'.get("modulo").'&id='.$id.'">
																		<i class="fas fa-users"></i>
																	</a>
																</div>';
			}else if($explode[0]=="json" && isset($explode[1])){
				$json_decode				=	json_decode($v->json);
				$label							=	$explode[1];
				$return[$k][$label]	=	@$json_decode->$label;
			}else if($k2=="nombre_frontOffice"){
				$return[$k][$k2]	=		'<a target="_blank" title="Ver" href="'.base_url($v2.$id).'-BackOffice">'.$nombre.' <i class="fas fa-search"></i></a>';
			}else if($k2=="json"){
				$return[$k][$k2]			=		$v2;
				$return[$k]["nombres"]		=		@json_decode($v2)->nombres .' '.@json_decode($v2)->apellidos;
				$return[$k]["ciudad"]		=		@json_decode($v2)->ciudad;
				$return[$k]["departamento"]	=		@json_decode($v2)->departamento;
				$return[$k]["title"]					=	@json_decode($v2)->name;
			}else{
				$return[$k][$k2]	=		$v2;
			}
			if($k2=="monto"){
				$return[$k][$k2]	=		'<div class="text-right mr-4">'.$v2.'</div>';
			}
			if(@$return[$k]["title"]==''){
				@$return[$k]["title"]=$v->title;
			}
		}
	}
	return $return;
}

function MakeEstado($name,$estado=null,$extra=array()){
	$options = array(
		'1'         => 'Activo',
		'0'       => 'Inactivo'
	);
	return form_dropdown($name, $options, $estado,$extra);
}

function menu_encode($var){
	$items 	= 	array();
	foreach($var["ver"] as $v){
		$json		=	json_decode($v);
		foreach($json as $key => $value){
			$items[$key][$value]	=	1;
		}
	}
	return json_encode($items);
}

function View2($view,$data=array(),$Apanel="_Apanel"){
	$ci =& 	get_instance();
	$ci->file_exists = file_exists(PATH_VIEW.'/Template/'.$view.'.php');
	$ci->load->view('Template/Header'.$Apanel);
	if($Apanel=='_Apanel'){
		$ci->load->view('Template/Apanel/Menu');
	}
	$ci->load->view('Template/Flash');
	if(isset($ci->Breadcrumb_bool) && $ci->Breadcrumb_bool){
		$ci->load->view('Template/Breadcrumb');
	}
	if($ci->file_exists){
		$ci->load->view('Template/'.$view,$data);
	}else{
		$ci->load->view('Template/Error_NoView',array("View"=>$view));
	}
	$ci->load->view('Template/Footer'.$Apanel);
}

function UploadAjaxImage($selector="#demo1"){
	$js	=	'
				<form id="Upload" method="post" action="" enctype="multipart/form-data">
					<label>Fotos a Subir: <input class="btn btn-info" type="file" name="userfile" id="demo1" /></label>
					<div id="uploads">
					</div>
				</form>
				<script type="text/javascript">
						$(document).ready(function() {
							$("'.$selector.'").AjaxFileUpload({
								selector_action:"#Upload",
								onComplete: function(filename, response) {
									$("#uploads").append(
										$("<img />").attr("src", response.name).attr("width", 200)
									);
								}
							});
						});
				</script>';
		echo $js;
}

function pre($var,$stop=true){
	echo '<pre>';
		print_r($var);
	echo '</pre>';
	if($stop){
		exit;
	}
}

function upload($file='userfile',$path='images/uploads/',$config=array("allowed_types"=>'gif|jpg|png',"max_size"=>100,"max_width"=>1024,"max_height"=>768)){
	$config['upload_path']        = 	PATH_BASE.'/'.$path;
	if(!is_dir($config['upload_path'])){
		if(!mkdir($config['upload_path'], 0755,true)){
			return false;
		}else{
			$fp		=	fopen($config['upload_path'].'/index.html',"w");
			fwrite($fp,'<a href="http://programandoweb.net">PROGRAMANDOWEB</a>');
			fclose($fp);
		}
	}
	$config['encrypt_name']       = 	TRUE;

	if(isset($config['renombrar'])){
		$nuevo_nombre	=	$config['renombrar'];
		unset($config['renombrar']);
	}

	$ci 	=& 	get_instance();
	$ci->load->library('upload', $config);
	$ci->upload->initialize($config);

	if(isset($_FILES[$file])){

		if ( ! $ci->upload->do_upload($file)){
			return array('error' => $ci->upload->display_errors());
		}
		else{
			$upload_data	=	$ci->upload->data();
			$upload_data['imagen_nueva']	=	DOMINIO.'/'.$path.$nuevo_nombre.$upload_data['file_ext'];
			rename($upload_data['full_path'],$upload_data['file_path'].$nuevo_nombre.$upload_data['file_ext']);
			$upload_data['nuevo_nombre']	=	$nuevo_nombre.$upload_data['file_ext'];
			return array('upload_data' => $upload_data);
		}
	}
}

function format($num,$decimal=true){
	$ci 	=& 	get_instance();
	$trm_											=		$ci->session->userdata('trm');
	if($num==0 || $num=='') $num=0;
	if($decimal){
		return number_format($num, 2, ',', '.');
	}else{
		return number_format($num,0, '', '.');
	}
}

function json_response($response=null,$message = null, $code = 200,$callback='',$redirect=false){
	header_remove();
	http_response_code($code);
	header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
	header('Content-Type: application/json');
	$status = array(
		200 => '200 OK',
		203 => '203 Error',
		400 => '400 Bad Request',
		422 => 'Unprocessable Entity',
		500 => '500 Internal Server Error'
	);
	header('Status: '.$status[$code]);
	if(is_array($response) && isset($response["skip"])){
		return json_encode($response["skip"]);
	}else if(is_object($response) && isset($response->skip)){
		return json_encode($response->skip);
	}
	$json=array(
		'status' => $code < 300,
		'message' => $message,
		'response' => $response,
		'code' => $code,
		'callback' => $callback
	);

	if(is_array($response) && get("u")==''){
		foreach ($response as $key => $value) {
			$json[$key]	=	$value;
		}
		//unset($json['response']);
	}
	if(empty($json["callback"])){
		unset($json['callback']);
	}
	if(empty($json["message"])){
		unset($json['message']);
	}
	if($redirect){
		$json["redirect"]	=	$redirect;
	}
	return json_encode($json);
}

function get_contador($campos=array()){

}

function _segment($var,$delimitador='-'){
	return explode($delimitador, $var);
}

function usuarios_x_login($post){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."usuarios";
	return $ci->db->select("*")->from($tabla)->where("email",$post["email"])->or_where("login",$post["email"])->get()->row();
}

function usuarios_x_token($token){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."usuarios";
	return $ci->db->select("*")->from($tabla)->where("token",$token)->get()->row();
}

function usuarios_like($identificacion,$institucion_id=false){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."usuarios";
	$ci->db->select("*")->from($tabla)
											->where("identificacion",$identificacion)
											->or_like("nombres",$identificacion);
	if ($institucion_id) {
		$ci->db->where("institucion_id",$institucion_id);
	}
	return $ci->db->get()->result();
}

function usuarios_x_indentificacion($identificacion,$institucion_id=false){
	$ci 	=& 	get_instance();
	$tabla						=	DB_PREFIJO."usuarios";
	$ci->db->select("*")->from($tabla)
											->where("identificacion",$identificacion)
											->or_like("nombres",$identificacion);
	if ($institucion_id) {
		$ci->db->where("institucion_id",$institucion_id);
	}
	return $ci->db->get()->row();
}

function usuarios($usuario_id){
	$ci 	=& 	get_instance();
	$tabla	=	DB_PREFIJO."usuarios";
	return $ci->db->select("*")->from($tabla)->where("usuario_id",$usuario_id)->or_where("token",$usuario_id)->get()->row();
}

function set_template_mail($var=array()){
	$ci 	=& 	get_instance();
	$view	=	PATH_VIEW.'/Template/Emails/'.$var['view'].'.php';
	if(file_exists($view)){
		return $ci->load->view('Template/Emails/'.$var['view'],$var['data'],TRUE);
	}else{
		return false;
	}
}

function enviar_notificacion($emisor,$receptor,$object){
	$ci 	=& 	get_instance();
	$receptor_	=	usuarios($receptor);
	$token			=	$object->token;
	$mensaje		=	$object->mensaje;
	$nombre			=	$object->nombre;
	$subject		=	$object->asunto;

	$url								=	base_url("ApiRest/post?modulo=Chat&m=notificaciones&formato=json&token=".$token);
	$vars["body"]				= set_template_mail(["view"=>"noticificacion","data"=>[ "url"=>$url,"mensaje"=>$mensaje,"nombre"=>$nombre ]]);
	//$vars["body"]				= 'The email send using codeigniter library';
	$vars["recipient"]	=	$receptor_->email;
	$vars["subject"]		=	$subject;
	$ci->db->insert(DB_PREFIJO."op_notificacion",[	"fecha"=>date("Y-m-d- H:i:s"),
																				"emisor_id"=>$emisor,
																				"usuario_id"=>$receptor,
																				"notificacion"=>$mensaje	,
																				"token"=>"Notificacion_".token(),
																				"estatus"=>0,
																				"json"=>json_encode($object)]);
	if ($receptor_->email!='no_email@gmail.com' && $receptor_->email!='' && $receptor_->email!='NULL') {
		$send_mail					=	send_mail($vars);
		if ($send_mail["error"]!='') {
			logs(["Mensaje"=>"No se pudo enviar correo","error"=>$send_mail["error"]]);
		}
	}
}

function send_mail($vars,$return=false){
	if($return){
		echo $vars["body"];
		return;
	}
	$ci 	=& 	get_instance();
	$config = array(
		'protocol' 		=> 	PROTOCOL,
		'smtp_host' 	=> 	SMTP_HOST,
		'smtp_port' 	=> 	SMTP_PORT,
		'smtp_user' 	=> 	SMTP_USER,
		'smtp_pass' 	=> 	SMTP_PASS,
		'smtp_timeout'=> 	SMTP_TIMEOUT,
		'mailtype'		=> 	MAILTYPE,
		'charset' 		=> 	CHARSET
	);
	//pre($config);
	$ci->load->library('email', $config);
	$ci->email->set_newline("\r\n");
	$ci->email->initialize($config);
	$ci->email->from(FROM_EMAIL, FROM_NAME);
	$ci->email->to($vars["recipient"]);
	$ci->email->subject($vars["subject"]);

	$ci->email->message($vars["body"]);
	if($ci->email->send()){
		return array("error"	=>	false);
	}else{
		return array("error"	=>	true, "debugger"=>$ci->email->print_debugger() );
	}
}

function dias($fecha,$dias=2){
	$nuevafecha = strtotime ( '+'.$dias.' day' , strtotime ( $fecha ) ) ;
	$nuevafecha = date ( 'Y-m-j' , $nuevafecha );
	return  $nuevafecha;
}

function contar_dias($fecha_ayer,$format='%R%a días'){
	$datetime1 	= 	new DateTime(date("Y-m-d"));
	$datetime2 	= 	new DateTime($fecha_ayer);
	$interval 	= 	$datetime1->diff($datetime2);
	return $interval->format($format);
}

function CookieUrl($var){
	$buscar	=	array("[","]",'"');
	return str_replace($buscar,"",$var);
}

function encriptar($var){
	$ci = get_instance();
	$ci->load->library("encryption");
	return $ci->encryption->encrypt($var);
}

function desencriptar($var){
	$ci = get_instance();
	$ci->load->library("encryption");
	return $ci->encryption->decrypt($var);
}
function me_img_profile(){
	$ci   =&  get_instance();
	return image("uploads/perfiles/".$ci->user->usuario_id.'/profile.jpg');
}
function img_logo($institucion_id){
	return image("uploads/perfiles/".$institucion_id.'/logo.png');
}
function image($image,$html=false,$imageTag=false,$attr=array()){
	$return_image=null;
	if(file_exists(PATH_IMG.$image)){
		$return_image = IMG.$image;
	}else{
		$return_image = IMG."1.jpg";
	}
	if(!$html){
		return $return_image;
	}else{
		$atributos  = '';
		foreach($attr as $k => $v){
			$atributos  .=   $k.'="'.$v.'"';
		}
		if(!$imageTag){
			return '<img src="'.$return_image.'" '.$atributos.' />';
		}else{
			return '<div class="image_rect image_default" style="background-image:url('.$return_image.');-webkit-background-size: cover; -moz-background-size: cover; -o-background-size: cover; background-size: cover;"></div>';
		}
	}
}

function MakeSelect($name,$estado,$extra = array("class"=>"form-control"),$data,$key = false){
	$options = array();
	if(!empty($data)){
		if(is_array($data)){
			foreach ($data as $k => $v){
				if($key){
					$options[$v] = $v;
				}else{
					$options[$k] = $v;
				}
			}
		}
	}
	return form_dropdown($name, $options, $estado,$extra);
}

function import($type,$file){
	switch($type){
		case "js":
			echo '<script async src="'.JS.$file.'.js"></script>';
		break;
		case "css":
			echo '<link rel="stylesheet" href="'.CSS.$file.'.css">';
		break;
		case "js3":
			echo '<script src="'.THIRDPARTY.$file.'.js"></script>';
		break;
		case "css3":
			echo '<link rel="stylesheet" href="'.THIRDPARTY.$file.'.css">';
		break;
	}
}

function MakeSiNo($name,$estado=null,$extra=array()){
	$options = array(
		""     => "Seleccione",
		 1       => 'Si',
		 0       => 'No',

	);
	return form_dropdown($name, $options, $estado,$extra);
}

function MakeTipoIdentidad($name,$estado=null,$extra=array(),$ids= null){
 $ci   =&  get_instance();
 $tabla            = DB_PREFIJO."ma_tipo_identidad";
 $ci->db->select("*")->from($tabla);
 $options    = $ci->db->get()->result();
 $option     =   array(""=>"Seleccione");
 foreach($options as $v){
	 $option[$v->tipo_identidad_id]   =   $v->tipo_identidad;
 }
 //pre($option); return;
 return form_dropdown($name, $option, $estado,$extra);
}

function ciudades($name,$row,$placeholder='',$require=false){
	if(empty($row)){
		$row=new stdClass();
		$row->$name='';
	}
	if(!empty($row)){
		$ci          =&  get_instance();
		$tabla       = "sys_municipios";
		if(is_numeric($row)){
			$row_municipio  = $ci->db->select("*")
																 ->from($tabla)
																 ->where('id',$row)->get()->row();
		}else{
			$row_municipio=new stdClass();
			$row_municipio->union_ = "";
			$row='';
		}
	}
	$html = '<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">';
	$html .=  '<input value="'.$row_municipio->union_.'" type="text" class="form-control" id="'.$name.'" placeholder="'.$placeholder.'" maxlength="150" ';
	$html .=  ($require)? 'require="require"':'';
	$html .=  '/>';
	$html	.=	'<input type="hidden" name="'.$name.'" id="content'.$name.'" require="require"  value="'.@$row.'" />';
	$html .=  '<script>
							 $(function(){
								 $( "#'.$name.'" ).autocomplete({
									 source: "'.base_url("ApiRest/get?modulo=Maestros&m=municipios&formato=json").'",
									 minLength: 2,
									 change: function (event, ui){
										 if (ui.item===null) {
											 this.value = "";
											 $("#text-alert").text("Por favor seleccione una ciudad válida del listado")
											 $("#myModal").modal("show");
										 }
									 },
									 focus: function( event, ui ) {
										 console.log(ui)
										 $("#content'.$name.'" ).val( ui.item.value );
										 $( "#'.$name.'" ).val( ui.item.label );
										 return false;
									 },
									 select: function( event, ui ) {
										 $("#content'.$name.'" ).val( ui.item.value );
										 $( "#'.$name.'" ).val( ui.item.label );
										 return false;
									 }
								 });
							 })';
 $html .=   '</script>';
	return $html;
}

function num_to_letras($numero, $moneda = 'PESO', $subfijo = 'M.N.')
{
    $xarray = array(
        0 => 'Cero'
        , 1 => 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'
        , 'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'
        , 'VEINTI', 30 => 'TREINTA', 40 => 'CUARENTA', 50 => 'CINCUENTA'
        , 60 => 'SESENTA', 70 => 'SETENTA', 80 => 'OCHENTA', 90 => 'NOVENTA'
        , 100 => 'CIENTO', 200 => 'DOSCIENTOS', 300 => 'TRESCIENTOS', 400 => 'CUATROCIENTOS', 500 => 'QUINIENTOS'
        , 600 => 'SEISCIENTOS', 700 => 'SETECIENTOS', 800 => 'OCHOCIENTOS', 900 => 'NOVECIENTOS'
    );

    $numero = trim($numero);
    $xpos_punto = strpos($numero, ',');
    $xaux_int = $numero;
    $xdecimales = '00';
    if (!($xpos_punto === false)) {
        if ($xpos_punto == 0) {
            $numero = '0' . $numero;
            $xpos_punto = strpos($numero, ',');
        }
        $xaux_int = substr($numero, 0, $xpos_punto); // obtengo el entero de la cifra a covertir
        $xdecimales = substr($numero . '00', $xpos_punto + 1, 2); // obtengo los valores decimales
    }

    $XAUX = str_pad($xaux_int, 18, ' ', STR_PAD_LEFT); // ajusto la longitud de la cifra, para que sea divisible por centenas de miles (grupos de 6)
    $xcadena = '';
    for ($xz = 0; $xz < 3; $xz++) {
        $xaux = substr($XAUX, $xz * 6, 6);
        $xi = 0;
        $xlimite = 6; // inicializo el contador de centenas xi y establezco el límite a 6 dígitos en la parte entera
        $xexit = true; // bandera para controlar el ciclo del While
        while ($xexit) {
            if ($xi == $xlimite) { // si ya llegó al límite máximo de enteros
                break; // termina el ciclo
            }

            $x3digitos = ($xlimite - $xi) * -1; // comienzo con los tres primeros digitos de la cifra, comenzando por la izquierda
            $xaux = substr($xaux, $x3digitos, abs($x3digitos)); // obtengo la centena (los tres dígitos)
            for ($xy = 1; $xy < 4; $xy++) { // ciclo para revisar centenas, decenas y unidades, en ese orden
                switch ($xy) {
                    case 1: // checa las centenas
                        $key = (int) substr($xaux, 0, 3);
                        if (100 > $key) { // si el grupo de tres dígitos es menor a una centena ( < 99) no hace nada y pasa a revisar las decenas
                            /* do nothing */
                        } else {
                            if (TRUE === array_key_exists($key, $xarray)) {  // busco si la centena es número redondo (100, 200, 300, 400, etc..)
                                $xseek = $xarray[$key];
                                $xsub = subfijo($xaux); // devuelve el subfijo correspondiente (Millón, Millones, Mil o nada)
                                if (100 == $key) {
                                    $xcadena = ' ' . $xcadena . ' CIEN ' . $xsub;
                                } else {
                                    $xcadena = ' ' . $xcadena . ' ' . $xseek . ' ' . $xsub;
                                }
                                $xy = 3; // la centena fue redonda, entonces termino el ciclo del for y ya no reviso decenas ni unidades
                            } else { // entra aquí si la centena no fue numero redondo (101, 253, 120, 980, etc.)
                                $key = (int) substr($xaux, 0, 1) * 100;
                                $xseek = $xarray[$key]; // toma el primer caracter de la centena y lo multiplica por cien y lo busca en el arreglo (para que busque 100,200,300, etc)
                                $xcadena = ' ' . $xcadena . ' ' . $xseek;
                            } // ENDIF ($xseek)
                        } // ENDIF (substr($xaux, 0, 3) < 100)
                        break;
                    case 2: // checa las decenas (con la misma lógica que las centenas)
                        $key = (int) substr($xaux, 1, 2);
                        if (10 > $key) {
                            /* do nothing */
                        } else {
                            if (TRUE === array_key_exists($key, $xarray)) {
                                $xseek = $xarray[$key];
                                $xsub = subfijo($xaux);
                                if (20 == $key) {
                                    $xcadena = ' ' . $xcadena . ' VEINTE ' . $xsub;
                                } else {
                                    $xcadena = ' ' . $xcadena . ' ' . $xseek . ' ' . $xsub;
                                }
                                $xy = 3;
                            } else {
                                $key = (int) substr($xaux, 1, 1) * 10;
                                $xseek = $xarray[$key];
                                if (20 == $key)
                                    $xcadena = ' ' . $xcadena . ' ' . $xseek;
                                else
                                    $xcadena = ' ' . $xcadena . ' ' . $xseek . ' Y ';
                            } // ENDIF ($xseek)
                        } // ENDIF (substr($xaux, 1, 2) < 10)
                        break;
                    case 3: // checa las unidades
                        $key = (int) substr($xaux, 2, 1);
                        if (1 > $key) { // si la unidad es cero, ya no hace nada
                            /* do nothing */
                        } else {
                            $xseek = $xarray[$key]; // obtengo directamente el valor de la unidad (del uno al nueve)
                            $xsub = subfijo($xaux);
                            $xcadena = ' ' . $xcadena . ' ' . $xseek . ' ' . $xsub;
                        } // ENDIF (substr($xaux, 2, 1) < 1)
                        break;
                } // END SWITCH
            } // END FOR
            $xi = $xi + 3;
        } // ENDDO
        # si la cadena obtenida termina en MILLON o BILLON, entonces le agrega al final la conjuncion DE
        if ('ILLON' == substr(trim($xcadena), -5, 5)) {
            $xcadena.= ' DE';
        }

        # si la cadena obtenida en MILLONES o BILLONES, entonces le agrega al final la conjuncion DE
        if ('ILLONES' == substr(trim($xcadena), -7, 7)) {
            $xcadena.= ' DE';
        }

        # depurar leyendas finales
        if ('' != trim($xaux)) {
            switch ($xz) {
                case 0:
                    if ('1' == trim(substr($XAUX, $xz * 6, 6))) {
                        $xcadena.= 'UN BILLON ';
                    } else {
                        $xcadena.= ' BILLONES ';
                    }
                    break;
                case 1:
                    if ('1' == trim(substr($XAUX, $xz * 6, 6))) {
                        $xcadena.= 'UN MILLON ';
                    } else {
                        $xcadena.= ' MILLONES ';
                    }
                    break;
                case 2:
                    if (1 > $numero) {
                        $xcadena = "CERO {$moneda} {$xdecimales}/100 {$subfijo}";
                    }
                    if ($numero >= 1 && $numero < 2) {
                        $xcadena = "UN {$moneda} {$xdecimales}/100 {$subfijo}";
                    }
                    if ($numero >= 2) {
                        $xcadena.= " {$moneda} {$xdecimales}/100 {$subfijo}"; //
                    }
                    break;
            } // endswitch ($xz)
        } // ENDIF (trim($xaux) != "")

        $xcadena = str_replace('VEINTI ', 'VEINTI', $xcadena); // quito el espacio para el VEINTI, para que quede: VEINTICUATRO, VEINTIUN, VEINTIDOS, etc
        $xcadena = str_replace('  ', ' ', $xcadena); // quito espacios dobles
        $xcadena = str_replace('UN UN', 'UN', $xcadena); // quito la duplicidad
        $xcadena = str_replace('  ', ' ', $xcadena); // quito espacios dobles
        $xcadena = str_replace('BILLON DE MILLONES', 'BILLON DE', $xcadena); // corrigo la leyenda
        $xcadena = str_replace('BILLONES DE MILLONES', 'BILLONES DE', $xcadena); // corrigo la leyenda
        $xcadena = str_replace('DE UN', 'UN', $xcadena); // corrigo la leyenda
    } // ENDFOR ($xz)
    return trim($xcadena);
}

/**
 * Esta función regresa un subfijo para la cifra
 *
 * @author Ultiminio Ramos Galán <contacto@ultiminioramos.com>
 * @param string $cifras La cifra a medir su longitud
 */
function subfijo($cifras)
{
    $cifras = trim($cifras);
    $strlen = strlen($cifras);
    $_sub = '';
    if (4 <= $strlen && 6 >= $strlen) {
        $_sub = 'MIL';
    }

    return $_sub;
}


?>
