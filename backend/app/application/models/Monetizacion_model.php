<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monetizacion_model extends CI_Model {

	var $fields,
			$result,
			$where,
			$total_rows,
			$pagination,
			$search,
			$response,
			$campos,
			$message;

	public function  campos_listado(){
		$this->campos	=	[	"usuario_id"=>"Usuario ID",
											"nombres"=>"Nombres y Apellidos"];
	}

	function SetProfileMonetizacion(){
		$this->db->where("usuario_id",post("usuario_id"));
		$this->db->update(DB_PREFIJO."usuarios",["parent_id"=>post("institucion_id")]);
	}

	function GetProfileMonetizacion(){
		$tabla	=	DB_PREFIJO."usuarios";
		$data		=	$this->db->select("	* ")
												->from($tabla)
												->where("parent_id",get("id"))
												->get()
												->row();
		$this->result	=	$data;
	}

	function downloadPdf(){

		require(PATH_BASE_APP."application/third_party/domPDF0.6.0beta3/dompdf_config.inc.php");
		$token=	get("token");
		$tabla	=	DB_PREFIJO."op_monetizacion t1";
		$data		=	$this->db->select("	t1.*,
																	DATE_FORMAT(t1.fecha_expedicion, '%d/%m/%Y') as fecha_expedicion_format,
																	t2.nombre_legal,
																	t2.numero_identificacion,
																	t2.direccion ")
												->from($tabla)
												->join(DB_PREFIJO."instituciones t2","t1.institucion_id=t2.institucion_id","left")
												->where("t1.token",$token)
												->get()
												->row();
		$data=campo_json_db($data);
		$my_html= set_template_PDF([	"view"=>"factura",
														"data"=>[
															"row"=>$data,
															"logo"=>IMG.'uploads/perfiles/1/logo.png',
															"logo2"=>IMG.'SELLO-CURVAS-01.png',
														]]);


		ob_start();
		echo utf8_decode($my_html);
		$salida = ob_get_clean();
		$dompdf = new DOMPDF();
		$dompdf->load_html($salida);
		$dompdf->render();
		$dompdf->stream("factura.pdf", array("Attachment" => false));
		exit;
	}

	function SetConfiguracion(){
		$tabla1	=	DB_PREFIJO."cf_monetizacion";
		/*recojo datos post*/
		$insert[$tabla1]	=	[];
		foreach (campos_tabla($tabla1) as $value) {
			if(!empty(post($value))){
				if($value=='json'){
					$insert[$tabla1][$value]	=	json_encode(post($value));
				}else{
					$insert[$tabla1][$value]	=	post($value);
				}
			}
		}
		/*fin recoleccion*/

		/*Verifico existencia de colegio y usuario: creo o edito*/
		$institucion_id	=	update_data("configuracion_id",$tabla1,["configuracion_id","configuracion"],post(),$insert[$tabla1]);
		$this->message=	"";
	}

	public function GetConfiguracion($configuracion_id){
		$tabla	=	DB_PREFIJO."cf_monetizacion";
		$data		=	$this->db->select("	* ")
												->from($tabla)
												->where("configuracion_id",$configuracion_id)
												->get()
												->row();

		$data=campo_json_db($data);
		$this->result	=	$data;
	}

	public function GetConfiguracions(){
		if(get("id")){
			$this->GetConfiguracion(get("id"));
			return;
		}
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$tabla	=	DB_PREFIJO."cf_monetizacion";
		$this->db->select("SQL_CALC_FOUND_ROWS  	configuracion_id", FALSE)
							->select('configuracion_id,
												configuracion_id as id,
												configuracion,
												"prueba" as edit')->from($tabla);
		if($search["value"]){
			$this->db->like("configuracion",$search["value"]);
		}
		if($order){
			$this->db->order_by("configuracion_id","DESC");
		}
		if($start && $length){
			$this->db->limit($length,$start);
		}
		$query	=	$this->db->get();

		$result_array=[];

		foreach ($query->result() as $key => $value) {
			foreach ($value as $key2 => $value2) {
				$result_array[$key][$key2] =	$value2;
			}
			if(isset($value->json) && !empty($value->json)){
				foreach (json_decode($value->json) as $key2 => $value2) {
					$result_array[$key][$key2]	=	$value2;
				}
			}
		}
		$total_rows=$this->db->query('SELECT FOUND_ROWS() count;')->row()->count;
		$this->result["data"]		= foreach_edit($result_array,$total_rows);
		$this->result["recordsTotal"]	=	$this->result["recordsFiltered"] =	$total_rows;
		$this->result["draw"]	=	get("draw");
		$this->message=	"";
	}

	function SetPagos(){
		$post	=	post();
		$tabla1	=	DB_PREFIJO."op_monetizacion";
		/*recojo datos post*/
		$insert[$tabla1]	=	[];
		foreach (campos_tabla($tabla1) as $value) {
			if(!empty(post($value))){
				if($value=='json'){
					$insert[$tabla1][$value]	=	json_encode(post($value));
				}else{
					$insert[$tabla1][$value]	=	post($value);
				}
			}
		}

		$insert[$tabla1]["usuario_creador"]	=	$this->user->usuario_id;
		$post["usuario_creador"]	=	$this->user->usuario_id;
		$post["monetizacion_id"]	=	post("monetizacion_id");
		$insert[$tabla1]["token"]	=	$post["token"]	=	token();
		/*fin recoleccion*/

		/*Verifico existencia de colegio y usuario: creo o edito*/
		$monetizacion_id	=	update_data("monetizacion_id",$tabla1,["monetizacion_id"],$post,$insert[$tabla1]);

		$institucion=institucion($post["institucion_id"]);


		if(!empty($institucion)){
			$email_html	= set_template_mail([	"view"=>"factura",
																				"data"=>[	"usuario"=>$institucion->nombre_legal,
																				"url"=>base_url("ApiRest/get?modulo=Monetizacion&m=downloadPdf&token=".$post["token"])
																				]]);
			send_mail([
				"recipient"=>"lic.jorgemendez@gmail.com",
				"subject"=>"Hola, tenemos una factura para usted",
				"body"=>$email_html,
			]);
		}
		$this->message=	"";
	}

	public function GetPago($monetizacion_id){
		$tabla	=	DB_PREFIJO."op_monetizacion";
		$data		=	$this->db->select("	* ")
												->from($tabla)
												->where("monetizacion_id",$monetizacion_id)
												->get()
												->row();

		$data=campo_json_db($data);
		$this->result	=	$data;
	}

	public function GetPagos(){
		$yo	=	usuarios($this->user->usuario_id);
		if(get("id")){
			$this->GetPago(get("id"));
			return;
		}
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$tabla	=	DB_PREFIJO."op_monetizacion t1";
		$this->db->select("SQL_CALC_FOUND_ROWS monetizacion_id", FALSE)
							->select('monetizacion_id,
												monetizacion_id as id,
												CONCAT(nombres," ",apellidos) AS creador,
												nombre_legal as empresa,
												monto,
												t1.token,
												"view" as view,
												t1.json,
												DATE_FORMAT(fecha_inicio, "%d-%m-%Y") as fecha_inicio,
												DATE_FORMAT(fecha_final, "%d-%m-%Y") as fecha_final,
												"pdf" as edit')->from($tabla)
												->join(DB_PREFIJO."usuarios t2","t1.usuario_creador=t2.usuario_id")
												->join(DB_PREFIJO."instituciones t3","t1.institucion_id=t3.institucion_id");


		if($this->user->tipo_usuario_id==4){
			$this->db->where("t1.institucion_id",$yo->parent_id);
		}
		if($search["value"]){
			$this->db->like("nombre_legal",$search["value"]);
		}
		if($order){
			$this->db->order_by("monetizacion_id","DESC");
		}
		if($start && $length){
			$this->db->limit($length,$start);
		}
		$query	=	$this->db->get();

		$result_array=[];

		foreach ($query->result() as $key => $value) {
			foreach ($value as $key2 => $value2) {
				$result_array[$key][$key2] =	$value2;
				if($key2=='monto'){
					$result_array[$key][$key2] =	format($value2);
				}
			}
			if(isset($value->json) && !empty($value->json)){
				foreach (json_decode($value->json) as $key2 => $value2) {
					$result_array[$key][$key2]	=	$value2;
				}
			}
		}
		$total_rows=$this->db->query('SELECT FOUND_ROWS() count;')->row()->count;
		$this->result["data"]		= foreach_edit($result_array,$total_rows);
		$this->result["recordsTotal"]	=	$this->result["recordsFiltered"] =	$total_rows;
		$this->result["draw"]	=	get("draw");
		$this->message=	"";
	}

	function SetClientes(){
		$tabla1	=	DB_PREFIJO."instituciones";
		/*recojo datos post*/
		$insert[$tabla1]	=	[];
		foreach (campos_tabla($tabla1) as $value) {
			if(!empty(post($value))){
				if($value=='json'){
					$insert[$tabla1][$value]	=	json_encode(post($value));
				}else if($value=='logo'){
					$insert[$tabla1][$value]	=	$this->input->post("logo",false);
				}else if($value=='contrato'){
					$insert[$tabla1][$value]	=	$this->input->post("contrato",false);
				}else{
					$insert[$tabla1][$value]	=	post($value);
				}
			}
		}
		/*fin recoleccion*/

		$insert[$tabla1]["token"]	=	token();

		/*Verifico existencia de colegio y usuario: creo o edito*/
		$institucion_id	=	update_data("institucion_id",$tabla1,["nombre_legal","nombre_comercial","institucion_id"],post(),$insert[$tabla1]);
		$files	=	$_FILES;

		if(isset($files["userfile"]["name"]["logo"])){

			$_FILES["userfile"]["name"]	=	$files["userfile"]["name"]["logo"];
			$_FILES["userfile"]["type"]	=	$files["userfile"]["type"]["logo"];
			$_FILES["userfile"]["tmp_name"]	=	$files["userfile"]["tmp_name"]["logo"];
			$_FILES["userfile"]["error"]	=	$files["userfile"]["error"]["logo"];
			$_FILES["userfile"]["size"]	=	$files["userfile"]["size"]["logo"];

			$upload	=	upload('userfile',$path='images/uploads/clientes/'.$institucion_id.'/',
													array(	"renombrar"=>"logo",
																	"allowed_types"=>'gif|jpg|jpeg|png',
																	"max_size"=>3000,
																	"max_width"=>4000,
																	"max_height"=>4000)
															);
			if(!isset($upload["error"])){
				$this->db->where("institucion_id",$institucion_id);
				$this->db->update($tabla1,["logo"=>json_encode($upload)]);
			}
		}

		if(isset($files["userfile"]["name"]["contrato"])){

			$_FILES["userfile"]["name"]	=	$files["userfile"]["name"]["contrato"];
			$_FILES["userfile"]["type"]	=	$files["userfile"]["type"]["contrato"];
			$_FILES["userfile"]["tmp_name"]	=	$files["userfile"]["tmp_name"]["contrato"];
			$_FILES["userfile"]["error"]	=	$files["userfile"]["error"]["contrato"];
			$_FILES["userfile"]["size"]	=	$files["userfile"]["size"]["contrato"];

			$upload2	=	upload('userfile',$path='images/uploads/clientes/'.$institucion_id.'/',
												array(	"renombrar"=>"contrato",
																"allowed_types"=>'doc|pdf|docx',
																"max_size"=>10000));
			if(!isset($upload2["error"])){
				$this->db->where("institucion_id",$institucion_id);
				$this->db->update($tabla1,["contrato"=>json_encode($upload2)]);
			}
		}
		$this->message=	"";
	}

	public function GetCliente($institucion_id){
		$tabla	=	DB_PREFIJO."instituciones";
		$data		=	$this->db->select("	* ")
												->from($tabla)
												->where("institucion_id",$institucion_id)
												->get()
												->row();

		$data=campo_json_db($data);
		if(!empty($data->logo)){
			$json=json_decode($data->logo);
			$data->logo	=	(isset($json->upload_data->imagen_nueva))?$json->upload_data->imagen_nueva:'';
		}
		if(!empty($data->contrato)){
			$json=json_decode($data->contrato);
			$data->contrato	=	(isset($json->upload_data->imagen_nueva))?$json->upload_data->imagen_nueva:'';
		}
		return $this->result	=	$data;
	}

	public function GetClientes(){
		if(get("id")){
			$this->GetCliente(get("id"));
			return;
		}
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$tabla	=	DB_PREFIJO."instituciones";
		$this->db->select("SQL_CALC_FOUND_ROWS institucion_id", FALSE)
							->select('institucion_id,
												institucion_id as id,
												nombre_legal,
												direccion,
												telefono,
												json,
												estatus as switch,
												"profile" as profile,
												"instituciones" as tabla,
												"prueba" as edit')->from($tabla);
		if($search["value"]){
			$this->db->like("nombre_legal",$search["value"]);
		}
		if($order){
			$this->db->order_by("nombre_legal");
		}
		if($start && $length){
			$this->db->limit($length,$start);
		}
		$query	=	$this->db->get();

		$result_array=[];

		foreach ($query->result() as $key => $value) {
			foreach ($value as $key2 => $value2) {
				$result_array[$key][$key2] =	$value2;
			}
			foreach (json_decode($value->json) as $key2 => $value2) {
				$result_array[$key][$key2]	=	$value2;
			}


		}
		$total_rows=$this->db->query('SELECT FOUND_ROWS() count;')->row()->count;
		$this->result["data"]		= foreach_edit($result_array,$total_rows);
		$this->result["recordsTotal"]	=	$this->result["recordsFiltered"] =	$total_rows;
		$this->result["draw"]	=	get("draw");
		$this->message=	"";
	}

	function toggle_Monetizacion(){
		get_estatus(get("t"),get("id"));
	}

	function response(){
		return $this->result;
	}
}
?>
