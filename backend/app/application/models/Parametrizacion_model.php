<?php
/*
	DESARROLLO Y PROGRAMACIÓN
	PROGRAMANDOWEB.NET
	LCDO. JORGE MENDEZ
	info@programandoweb.net
*/
defined('BASEPATH') OR exit('No direct script access allowed');

class Parametrizacion_model extends CI_Model {

	var $campos,$result,$message;

	public function campos_listado(){

	}

	public function setEmpresas(){
		$tabla	=	DB_PREFIJO."empresas";
		$post=post();
		$tabla	=	DB_PREFIJO."empresas";
		$data		=	$this->db->select("	institucion_id ")
												->from($tabla)
												->where("token",$post["token"])
												->get()
												->row();

		if(empty($data)){
			$insert=[
				"empresa"=>$post["empresa"],
				"email"=>$post["email"],
				"telefono"=>$post["json"]["telefono"],
				"direccion"=>$post["json"]["direccion"],
				"token"=>token(),
				"estatus"=>1,
				"json"=>json_encode($post["json"]),
			];
			$this->db->insert($tabla,$insert);
			//pre($insert);
		}else{
			$insert=[
				"empresa"=>$post["empresa"],
				"email"=>$post["email"],
				"telefono"=>$post["json"]["telefono"],
				"direccion"=>$post["json"]["direccion"],
				"estatus"=>1,
				"token"=>token(),
				"json"=>json_encode($post["json"]),
			];

			$this->db->where("token",$post["token"]);
			$this->db->update($tabla,$insert);

		}
	}

	public function getEmpresas(){
		$tabla	=	DB_PREFIJO."empresas";
		$data		=	$this->db->select("	* ")
												->from($tabla)
												->where("token",get("id"))
												->get()
												->row();

		$this->result	=	campo_json_db($data);
	}

	public function ListaEmpresas(){
		if(get("id")){
			return $this->getMaproducto(get("id"));
		}
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;

		$tabla	=	DB_PREFIJO."empresas";
		$this->db->select("SQL_CALC_FOUND_ROWS  institucion_id", FALSE)
							->select('	institucion_id,
													token as id,
													empresa,
													telefono,
													direccion,
													"edit" as edit')->from($tabla);

		if($search["value"]){
			$this->db->like("empresa",$search["value"]);
		}
		if($order){
			$this->db->order_by("institucion_id");
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

	function getDependencia(){
		$tabla	=	 	DB_PREFIJO."ma_productos";
		$ci 	=& 	get_instance();
		$campo=get("t");
		if(get("t")!='' && get("t")=='parent_id'){
			$rows	=$ci->db->select("*")
							->from($tabla)
							->where($campo,get("id"))
							->where("subparent_id",0)
							->order_by("tipo")
							->get()
							->result();
		}else if(get("t")!='' && get("t")=='subparent_id'){
			$rows	=$ci->db->select("*")
							->from($tabla)
							->where($campo,get("id"))
							->order_by("tipo")
							->get()
							->result();
		}else{
			$rows	=$ci->db->select("*")
							->from($tabla)
							->where("parent_id",get("id"))
							->where("subparent_id",0)
							->order_by("tipo")
							->get()
							->result();
		}

		$return_ = array(	"data"=>foreach_edit($rows,1000));
		$this->result	=  $return_;
	}

	function setMaestro_Productos(){
		$tabla	=	DB_PREFIJO."ma_productos";
		$post=post();
		$producto=producto($post["tipo_id"]);
		if(isset($post["subparent_id"])){
			$insert=[
				"parent_id"=>$post["parent_id"],
				"subparent_id"=>$post["subparent_id"],
				"tipo"=>$post["tipo"],
				"codigo"=>$post["codigo"],
			];
		}else{
			$insert=[
				"parent_id"=>$post["parent_id"],
				"subparent_id"=>0,
				"tipo"=>$post["tipo"],
				"codigo"=>$post["codigo"],
			];
		}
		if(empty($producto)){
			$this->db->insert($tabla,$insert);
		}else{
			$this->db->where("tipo_id",$producto->tipo_id);
			$this->db->update($tabla,$insert);
		}
	}

	function getMaAdrian(){
		if(get("id")){
			return $this->getMaproducto(get("id"));
		}
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;

		$tabla	=	DB_PREFIJO."usuarios";
		$this->db->select("SQL_CALC_FOUND_ROWS  usuario_id", FALSE)
							->select('	usuario_id,
													usuario_id as id,
													nombres,
													"edit" as edit')->from($tabla);

		$this->db->where("email!=","NULL");

		if($search["value"]){
			$this->db->like("email",$search["value"]);
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

	function getMaproducto($id){
		$tabla	=	DB_PREFIJO."ma_productos";
		$data		=	$this->db->select("	* ")
												->from($tabla)
												->where("tipo_id",$id)
												->get()
												->row();
		$this->result	=	$data;
	}

	function getMaproductos(){
		if(get("id")){
			return $this->getMaproducto(get("id"));
		}
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;

		$tabla	=	DB_PREFIJO."ma_productos";
		$this->db->select("SQL_CALC_FOUND_ROWS  tipo_id", FALSE)
							->select('	tipo_id,
													tipo_id as id,
													tipo,
													"edit" as edit')->from($tabla);

		if($search["value"]){
			$this->db->like("tipo",$search["value"]);
		}
		if($order){
			$this->db->order_by("tipo_id");
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


	function set_notificacion_envio(){
		$tabla	=	DB_PREFIJO."op_notificacion";
		$data		=	$this->db->select("	notificacion_id ")
												->from(DB_PREFIJO."op_notificacion")
												->where("centro_de_costos_id",$this->gasto->centro_de_costos_id)
												->where("fecha",date("Y-m-d"))
												->where("notificacion",$this->notificacion)
												->get()
												->row();
		if(empty($data)){
			$this->db->insert($tabla,[	"centro_de_costos_id"=>$this->gasto->centro_de_costos_id,
																	"notificacion"=>$this->notificacion,
																	"fecha"=>date("Y-m-d"),
																	"token"=>token(),
																	"estatus"=>1,
																	"json"=>json_encode(["body"=>$this->body])
																]);
		}else{
			$this->db->where("notificacion_id",$data->notificacion_id);
			$this->db->update($tabla,[	"json"=>json_encode(["body"=>$this->body])]);
		}
	}

	function setGastos_Fijos(){
		$tabla	=	DB_PREFIJO."op_gastos_fijos";
		$post		=	post();
		unset($post["redirect"]);
		unset($post["celular"]);
		$post["token"]=token();
		if(post("token") && post("token")!='0'){
			$this->db->where("token",post("token"));
			$this->db->update($tabla,$post);
		}else{
			$this->db->insert($tabla,$post);
		}
	}

	function logo(){
		$empresa=empresa();
		$imagen	=	upload(	'userfile',
											'images/uploads/perfiles/'.$empresa->institucion_id.'/',
											array(	"allowed_types"=>'gif|jpg|png',
															"renombrar"=>"logo",
															"max_size"=>12000,
															"max_width"=>12000,
															"max_height"=>12000));
		//pre($imagen);
	}

	function toggle_Parametrizacion(){
		get_estatus(get("t"),get("id"));
	}

	function get(){
		if(get("id")){
			//return $this->centro_de_costo(get("id"));
		}
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;

		$tabla	=	DB_PREFIJO."ma_plataformas";
		$this->db->select("SQL_CALC_FOUND_ROWS plataforma_id", FALSE)
							->select('	plataforma_id,
													token as id,
													plataforma,
													nombre_legal,
													"ma_plataformas" as tabla,
													estatus as switch')->from($tabla);

		if(empty(get("estatus"))){
				$this->db->where("estatus<",9);
		}
		if($search["value"]){
			$this->db->like("nombre_legal",$search["value"]);
		}
		if($order){
			$this->db->order_by("plataforma_id");
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


	function escala_de_Pagos(){
		if(get("id")){
			//return $this->centro_de_costo(get("id"));
		}
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;

		$tabla	=	DB_PREFIJO."ma_escala_pagos";
		$this->db->select("SQL_CALC_FOUND_ROWS escala_id", FALSE)
							->select('	escala_id,
													token as id,
													escala,
													"edit" as edit')->from($tabla);

		if(empty(get("estatus"))){
				$this->db->where("estatus<",9);
		}
		if($search["value"]){
			$this->db->like("escala",$search["value"]);
		}
		if($order){
			$this->db->order_by("escala_id");
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


	// function escala_de_Pagos(){
	// 	if(get("id")){
	// 		//return $this->centro_de_costo(get("id"));
	// 	}
	// 	$order	=	get("order");
	// 	$search	=	get("search");
	// 	$start	=	get("start");
	// 	$length	=	get("length");
	// 	$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;
	//
	// 	$tabla	=	DB_PREFIJO."ma_escala_pagos";
	// 	$this->db->select("SQL_CALC_FOUND_ROWS escala_id", FALSE)
	// 						->select('	escala_id,
	// 												token as id,
	// 												escala,
	// 												"ma_escala_pagos" as tabla,
	// 												estatus as switch')->from($tabla);
	//
	// 	if(empty(get("estatus"))){
	// 			$this->db->where("estatus<",9);
	// 	}
	// 	if($search["value"]){
	// 		$this->db->like("escala",$search["value"]);
	// 	}
	// 	if($order){
	// 		$this->db->order_by("escala_id");
	// 	}
	// 	if($start && $length){
	// 		$this->db->limit($length,$start);
	// 	}
	// 	$query	=	$this->db->get();
	// 	$rows		=	$query->result_array();
	// 	if(!empty($rows)){
	// 		$count=$this->db->query('SELECT FOUND_ROWS() count;')->row()->count;
	//  		$return_ = array(	"data"=>foreach_edit($rows,$count),
	//  											"recordsTotal"=>$count,
	// 											"recordsFiltered"=>$count,
	//  											"limit"=>$limit);
	// 		$this->result	=	$return_;
	// 		return $return_;
	//  	}
	// }

	function exc_Gastos_Fijos(){

	}

	function Gasto_Fijo($token){
		$tabla	=	DB_PREFIJO."op_gastos_fijos";

		$this->result["data"]		= $this->db->select('*')->from($tabla)->where("token",$token)->get()->row();
	}

	function Gastos_Fijos(){
		//date_default_timezone_set('America/Bogota');
		if(get("id")){
			return $this->Gasto_Fijo(get("id"));
		}
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;

		$tabla	=	DB_PREFIJO."op_gastos_fijos";
		$this->db->select("SQL_CALC_FOUND_ROWS gasto_fijo_id", FALSE)
							->select('	gasto_fijo_id,
													token as id,
													gasto,
													IF(recurrencia=1, "Recurrente", "No Recurrente") as recurrencia,
													CONCAT(DAY(fecha_vencimiento),"-",MONTH(fecha_vencimiento)) AS fecha_vencimiento,
													CONCAT(DAY(fecha_recordatorio),"-",MONTH(fecha_recordatorio)) AS fecha_recordatorio,
													"prueba" as edit_email')->from($tabla);

		if(empty(get("estatus"))){
				$this->db->where("estatus<",10);
		}
		if($search["value"]){
			$this->db->like("gasto",$search["value"]);
		}
		if($order){
			$this->db->order_by("gasto_fijo_id");
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
