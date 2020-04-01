<?php
/*
	DESARROLLO Y PROGRAMACIÓN
	PROGRAMANDOWEB.NET
	LCDO. JORGE MENDEZ
	info@programandoweb.net
*/
defined('BASEPATH') OR exit('No direct script access allowed');

class Operatividad_model extends CI_Model {

	var $campos,$result,$message;

	public function campos_listado(){

	}

	public function delServicios(){
		$tabla	=	DB_PREFIJO."op_servicios";
		$this->db->where("servicio_id",get("id"));
		$this->db->delete($tabla);
	}

	public function Solicitudlavadora(){
		$solicitudes	=	solicitudes_de_usuarios($this->user->usuario_id);
		if($solicitudes==0){
			$post		=	post();
			$tabla	=	DB_PREFIJO."op_servicios";
			$post["institucion_id"]		=	post("institucion_id");
			$post["fecha_solicitud"]=date("Y-m-d H:i:s");
			$post["domiciliaro_entrega_id"]=$post["domiciliaro_receptor_id"]=0;
			$post["token"]=token();
			$post["cliente_id"]=$this->user->usuario_id;
			unset($post["redirect"]);
			$this->db->insert($tabla,$post);
			redirect(post("redirect"));
		}
	}

	public function InventarioQR($token){
		$tabla	=	DB_PREFIJO."op_inventario t1";
		return	$this->db->select("	t1.*,t2.empresa ")
												->from($tabla)
												->join(DB_PREFIJO."empresas t2","t1.institucion_id=t2.institucion_id","LEFT")
												->where("t1.token",$token)
												->get()
												->row();
	}

	public function QR(){
		/*GET(T) TIPO -> E = EQUIPO*/
		switch (get("t")) {
			case 'E':
			default:
				redirect(base_url("Main/QR/Equipo/".get("token")));
				break;
		}

	}

	public function getUsuariosDeudores(){
		if(get("id")){
			return $this->getServicio(get("id"));
		}
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;

		$tabla1	=	DB_PREFIJO."op_servicios t1";
		$tabla2	=	DB_PREFIJO."usuarios cl";
		$tabla3	=	DB_PREFIJO."usuarios de";
		$tabla4	=	DB_PREFIJO."usuarios dr";
		$this->db->select("SQL_CALC_FOUND_ROWS  t1.servicio_id", FALSE)
							->select('	t1.servicio_id,
													t1.servicio_id as id,
													CONCAT(cl.nombres) as cliente,
													CONCAT(de.nombres) as domiciliaro_entrega,
													CONCAT(dr.nombres) as domiciliaro_receptor,
													fecha_entrega_al_cliente,
													fecha_solicitud,
													fecha_entrega_al_domiciliario,
													monto_deuda_del_cliente,
													"edit" as edit')
							->from($tabla1)
							->join($tabla2,"t1.cliente_id=cl.usuario_id","left")
							->join($tabla3,"t1.domiciliaro_entrega_id=de.usuario_id","left")
							->join($tabla4,"t1.domiciliaro_receptor_id=dr.usuario_id","left");

		$this->db->where("domiciliaro_entrega_id>",0);
		$this->db->where("domiciliaro_receptor_id>",0);

		if($search["value"]){
			$this->db->like("cl.nombres",$search["value"]);
			$this->db->or_like("de.nombres",$search["value"]);
			$this->db->or_like("dr.nombres",$search["value"]);
		}
		if($order){
			$this->db->order_by("servicio_id");
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
			$this->result	= array(	"data"=>[],
															"recordsTotal"=>0,
															"recordsFiltered"=>0,
															"limit"=>$limit);
		}
	}

	public function SetOrdenesServicios(){
		$post		=	post();
		//pre($post);
		$tabla	=	DB_PREFIJO."op_servicios";
		unset($post["redirect"]);

		/*SE VERIFICA SI VIENEN YA LOS DOMICILIARIOS
			PARA SETEAR LA FECHA DE ENTREGA Y RECEPCIÓN*/

		if($post["domiciliaro_entrega_id"]>0){
			$post["fecha_entrega_al_cliente"]=date("Y-m-d H:i:s");
		}
		if($post["domiciliaro_receptor_id"]>0){
			$post["fecha_entrega_al_domiciliario"]=date("Y-m-d H:i:s");
			$post["estatus"]=1;
		}

		$post["institucion_id"]		=	$this->user->institucion_id;

		if($post["servicio_id"]==0){
			$post["fecha_solicitud"]=date("Y-m-d H:i:s");
			$post["token"]=token();
			$this->db->insert($tabla,$post);
		}else{
			$this->db->where("servicio_id",$post["servicio_id"]);
			$this->db->update($tabla,$post);
		}
	}

	public function getServicio($servicio_id){
		$tabla	=	DB_PREFIJO."op_servicios";
		$data		=	$this->db->select("	* ")
												->from($tabla)
												->where("servicio_id",$servicio_id)
												->get()
												->row();
		$this->result	=	$data;
	}

	public function getServicios(){
		if(get("id")){
			return $this->getServicio(get("id"));
		}
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;

		$tabla1	=	DB_PREFIJO."op_servicios t1";
		$tabla2	=	DB_PREFIJO."usuarios cl";
		$tabla3	=	DB_PREFIJO."usuarios de";
		$tabla4	=	DB_PREFIJO."usuarios dr";
		$this->db->select("SQL_CALC_FOUND_ROWS  t1.servicio_id", FALSE)
							->select('	t1.servicio_id,
													t1.servicio_id as id,
													CONCAT(cl.nombres) as cliente,
													cl.json,
													CONCAT(de.nombres) as domiciliaro_entrega,
													CONCAT(dr.nombres) as domiciliaro_receptor,
													fecha_entrega_al_cliente,
													fecha_agendamiento,
													fecha_entrega_al_domiciliario,
													"edit" as edit_delete')
							->from($tabla1)
							->join($tabla2,"t1.cliente_id=cl.usuario_id","left")
							->join($tabla3,"t1.domiciliaro_entrega_id=de.usuario_id","left")
							->join($tabla4,"t1.domiciliaro_receptor_id=dr.usuario_id","left");


		if($search["value"]){
			$this->db->like("cl.nombres",$search["value"]);
			$this->db->or_like("de.nombres",$search["value"]);
			$this->db->or_like("dr.nombres",$search["value"]);
		}
		$this->db->where("t1.institucion_id",$this->user->institucion_id);
		if($order){
			$this->db->order_by("servicio_id");
		}
		if($start && $length){
			$this->db->limit($length,$start);
		}
		$query	=	$this->db->get();
		$rows		=	$query->result_array();

		$_rows	=	[];
		foreach ($rows as $key => $value) {
			$json	=	json_decode($value["json"]);
			$_rows[$key]	=	$value;
			if(isset($json->direccion)){
				$_rows[$key]["direccion"]	=	$json->direccion.' - '.$json->ciudad.' - '.$json->departamento;
			}else{
				$_rows[$key]["direccion"]	=	"No especificada";
			}

		}

		if(!empty($rows)){
			$count=$this->db->query('SELECT FOUND_ROWS() count;')->row()->count;
	 		$return_ = array(	"data"=>foreach_edit($_rows,$count),
	 											"recordsTotal"=>$count,
												"recordsFiltered"=>$count,
	 											"limit"=>$limit);
			$this->result	=	$return_;
			return $return_;
	 	}else{
			$this->result	= array(	"data"=>[],
															"recordsTotal"=>0,
															"recordsFiltered"=>0,
															"limit"=>$limit);
		}
	}

	public function SetHorarios(){
		$post=post();
		$tabla	=	DB_PREFIJO."op_horarios";
		$Horario=Horario($this->user->institucion_id);

		foreach (array_dias() as $key => $value) {
			$post[$value]	=		(isset($post["json"]["horario"][$value]))?1:0;
		}
		$insert=$post;
		$insert["institucion_id"]=$this->user->institucion_id;
		$insert["institucion_id"]=$this->user->institucion_id;
		$insert["json"]=json_encode(post("json"));
		unset($insert["redirect"]);
		if(empty($Horario)){
			$this->db->insert($tabla,$insert);
		}else{
			$this->db->where("horario_id",$Horario->horario_id);
			$this->db->update($tabla,$insert);
		}
	}

	public function GetInventarioXID(){
		$tabla	=	DB_PREFIJO."op_inventario";
		$data		=	$this->db->select("	* ")
												->from($tabla)
												->where("inventario_id",get("id"))
												->get()
												->row();

		if(!empty($data->json) && $data->json!=NULL){
			$data=campo_json_db($data);
		}
		$this->result	=	$data;
	}

	public function getEntrevista(){
		$tabla1	=	DB_PREFIJO."op_entrevistas t1";
		$return_=	$this->db->select("	t1.*")
												->from($tabla1)
												->where("t1.entrevista_id",get("id"))
												->get()
												->row();
		$this->result	=	campo_json_db($return_);
		return $return_;
	}

	public function getEntrevistas(){
		if(get("id")){
			return $this->getEntrevista(get("id"));
		}
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;

		$tabla	=	DB_PREFIJO."op_entrevistas";
		$this->db->select("SQL_CALC_FOUND_ROWS  entrevista_id", FALSE)
							->select('	entrevista_id,
													entrevista_id as id,
													nombres as modelo,
													"edit" as edit')->from($tabla);


		if($search["value"]){
			$this->db->like("nombres",$search["value"]);
		}
		if($order){
			$this->db->order_by("entrevista_id");
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

	public function SetEntrevistas(){
		$insert	=	[
								"tipo_usuario_id"=>post("tipo_usuario_id"),
								"nombres"=>post("nombres"),
								"telefono"=>post("telefono"),
								"json"=>json_encode(post("json")),
								];
		if (post("entrevista_id")==0) {
			$this->db->insert(DB_PREFIJO."op_entrevistas",$insert);
		}else{
			$this->db->where("entrevista_id",post("entrevista_id"));
			$this->db->update(DB_PREFIJO."op_entrevistas",$insert);
		}
	}

	public function AprobarData(){
		$this->db->where("centro_de_costos_id",get("cc"));
		$this->db->where("estatus",0);
		$this->db->update(DB_PREFIJO."op_importar_excel",["estatus"=>get("estatus")]);
		redirect("Apanel/Operatividad?m=Importar_data");
	}

	public function GetDetalleImportacion($formato="array"){
		$tabla1	=	DB_PREFIJO."op_importar_excel t1";
		$tabla2	=	DB_PREFIJO."usuarios t2";
		$tabla3	=	DB_PREFIJO."centros_de_costo t3";
		if($formato=="array"){
			$rows		=	$this->db->select("	t1.*,t2.nombres,t3.nombre ")
													->from($tabla1)
													->join($tabla2,"t1.modelo_id = t2.usuario_id","left")
													->join($tabla3,"t1.centro_de_costos_id = t3.centro_de_costos_id","left")
													->where("t1.token",get("id"))
													->get()
													->result_array();
		}else{
			$rows2		=	$this->db->select("	t1.*,t2.nombres,t3.nombre,t2.parent_id,t4.nombres as monitor,t2.fecha_ingreso ")
													->from($tabla1)
													->join($tabla2,"t1.modelo_id = t2.usuario_id","left")
													->join($tabla3,"t1.centro_de_costos_id = t3.centro_de_costos_id","left")
													->join(DB_PREFIJO."usuarios t4","t2.parent_id = t4.usuario_id","left")
													->where("t1.token",get("id"))
													->get()
													->result();
			$rows=[];
			foreach ($rows2 as $key => $value) {
				$rows[$key]	=	$value;
				//$monitores[$value->parent_id]=$value;
				@$monitores[$value->parent_id]->tokens	+=	@$value->tokens;
				$monitores[$value->parent_id]->fecha_ingreso	=	$value->fecha_ingreso;
				$monitores[$value->parent_id]->fecha_inicio	=	$value->fecha_inicio;
				$monitores[$value->parent_id]->fecha_final	=	$value->fecha_final;
				$monitores[$value->parent_id]->monitor=	(!empty($value->monitor))?$value->monitor:"Satélite";
			}
			$return	=	new stdClass();
			$return->porModelos	=	$rows;
			$return->porMonitores	=	$monitores;
			//pre($monitores);
			return $return;
		}
		return $this->result["data"]	=	$rows;
	}

	public function DatosImportadosExcel(){
		$tabla1	=	DB_PREFIJO."op_importar_excel t1";
		$tabla2	=	DB_PREFIJO."usuarios t2";
		$tabla3	=	DB_PREFIJO."centros_de_costo t3";
		return $data		=	$this->db->select("	t1.*,t2.nombres,t3.nombre,sum(t1.tokens) as tokens ")
																->from($tabla1)
																->join($tabla2,"t1.modelo_id = t2.usuario_id","left")
																->join($tabla3,"t1.centro_de_costos_id = t3.centro_de_costos_id","left")
																//->where("t1.estatus",1)
																->group_by(["centro_de_costos_id","fecha_inicio","fecha_final"])
																->get()
																->result();
	}

	public function Get_data(){
		$tabla1	=	DB_PREFIJO."op_importar_excel t1";
		$tabla2	=	DB_PREFIJO."usuarios t2";
		return $data		=	$this->db->select("	t1.*,t2.nombres ")
												->from($tabla1)
												->join($tabla2,"t1.modelo_id = t2.usuario_id","left")
												->where("t1.estatus",0)
												->get()
												->result();
	}

	public function Importar_data(){
		$upload=upload('userfile','images/uploads/excel',array("renombrar"=>"reporte","allowed_types"=>'xls|xlsx',"max_size"=>10000,));
		$archivo =	$upload["upload_data"]["file_path"].$upload["upload_data"]["nuevo_nombre"];
		if(isset($archivo)){
			require_once PATH_THIRDPARTY.'/PHPExcel-1.8/Classes/PHPExcel.php';
			$inputFileType 	= 	PHPExcel_IOFactory::identify($archivo);
			$objReader 			= 	PHPExcel_IOFactory::createReader($inputFileType);
			$objPHPExcel 		= 	$objReader->load($archivo);
			$sheet 					= 	$objPHPExcel->getSheet(0);
			$highestRow 		= 	$sheet->getHighestRow();
			$highestColumn 	= 	$sheet->getHighestColumn();

			$monitores=$modelos=[];
			$token=token();
			for ($row = 2; $row <= $highestRow; $row++){
				if(!empty($sheet->getCell("B".$row)->getValue())){
					//$monitores[$sheet->getCell("B".$row)->getValue()]	=	$sheet->getCell("B".$row)->getValue();
					$monitor	=	buscar_usuario_x_nombre($sheet->getCell("B".$row)->getValue(),2);
					$insert	=	[	"nombres"=>$sheet->getCell("B".$row)->getValue(),
																										"centro_de_costos_id"=>post("centro_de_costos_id"),
																									];
					if(empty($monitor)){
						$this->db->insert(DB_PREFIJO."usuarios",$insert);
						$monitor_id 	= 	$this->db->insert_id();
					}else{
						$this->db->where("usuario_id",$monitor->usuario_id);
						$this->db->update(DB_PREFIJO."usuarios",$insert);
						$monitor_id		=		$monitor->usuario_id;
					}
					$monitores[$monitor_id]	=	$sheet->getCell("B".$row)->getValue();

					$modelo				=	buscar_usuario_x_nombre($sheet->getCell("A".$row)->getValue(),1);
					$obj_monitor	=	buscar_usuario_x_nombre($sheet->getCell("B".$row)->getValue(),2);
					if($sheet->getCell("B".$row)->getValue()=='No Asignado'){
						$insert2			=	[	"nombres"=>$sheet->getCell("A".$row)->getValue(),
															"centro_de_costos_id"=>post("centro_de_costos_id"),
															"parent_id"=>0,
															"tipo_usuario_id"=>1,
														];
					}else{
						$insert2			=	[	"nombres"=>$sheet->getCell("A".$row)->getValue(),
															"centro_de_costos_id"=>post("centro_de_costos_id"),
															"parent_id"=>$obj_monitor->usuario_id,
															"tipo_usuario_id"=>1,
														];
					}

					if(empty($modelo)){
						$this->db->insert(DB_PREFIJO."usuarios",$insert2);
					}else{
						$this->db->where("usuario_id",$modelo->usuario_id);
						$this->db->update(DB_PREFIJO."usuarios",$insert2);
					}

					$obj_modelo		=	buscar_usuario_x_nombre($sheet->getCell("A".$row)->getValue(),1);
					$obj_monitor	=	buscar_usuario_x_nombre($sheet->getCell("B".$row)->getValue(),2);

					$insert_importe_excel	=	[
																		"modelo_id"=>$obj_modelo->usuario_id,
																		"centro_de_costos_id"=>post("centro_de_costos_id"),
																		"usuario_creador"=>$this->user->usuario_id,
																		"fecha_inicio"=>post("fecha_inicio"),
																		"fecha_final"=>post("fecha_final"),
																		"fecha_creacion"=>date("Y-m-d H:i:s"),
																		"token"=>$token,
																		"tokens"=>$sheet->getCell("E".$row)->getValue(),
																	];
					if ($sheet->getCell("E".$row)->getValue()>0) {
						$data		=	$this->db->select("	importe_id ")
						->from(DB_PREFIJO."op_importar_excel")
						->where("modelo_id",$obj_modelo->usuario_id)
						->where("fecha_inicio",post("fecha_inicio"))
						->where("fecha_final",post("fecha_final"))
						->where("centro_de_costos_id",post("centro_de_costos_id"))
						->where("estatus",0)
						->get()
						->row();

						if(empty($data)){
							$this->db->insert(DB_PREFIJO."op_importar_excel",$insert_importe_excel);
						}else{
							$this->db->where("importe_id",$data->importe_id);
							$this->db->update(DB_PREFIJO."op_importar_excel",$insert_importe_excel);
						}
					}
				}
			}
		}
		//redirect(post("redirect"))
	}

	public function getCentrosDeCostos(){
			$rows		=	centros_de_costos();
			$result	=	'<option value="">Seleccione</option>';
			foreach ($rows as $key => $value) {
				if($value->centro_de_costos_id==post("id")){
					for($i=1;$i<=$value->n_rooms;$i++){
						$result	.=	'<option value="'.$i.'"> Room '.$i.'</option>';
					}
				}
			}
			$result	.=	'<option value="10000000">Otro</option>';
			echo $result;
	}

	public function asignar_recurso(){

		$tabla1	=	 	DB_PREFIJO."op_inventario";
		$this->db->where("inventario_id",post("inventario_id"));
		$this->db->update($tabla1,["centro_de_costos_id"=>post("centro_de_costos_id"),"room"=>post("room")]);

	}

	public function getRecursosActivos(){
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;

		$tabla	=	DB_PREFIJO."op_servicios t1";
		$tabla2	=	DB_PREFIJO."usuarios cl";
		$tabla3	=	DB_PREFIJO."usuarios de";
		$tabla4	=	DB_PREFIJO."op_inventario inv";
		$this->db->select("SQL_CALC_FOUND_ROWS  servicio_id", FALSE)
							->select('	t1.servicio_id,
													t1.servicio_id as id,
													t1.recurso_id,
													inv.json,
													CONCAT(cl.nombres) as cliente,
													CONCAT(de.nombres) as domiciliaro_entrega')->from($tabla)
													->join($tabla2,"t1.cliente_id=cl.usuario_id","left")
													->join($tabla3,"t1.domiciliaro_entrega_id=de.usuario_id","left")
													->join($tabla4,"t1.recurso_id=inv.inventario_id","left");

		if($this->user->tipo_usuario_id>0 || $this->user->institucion_id>0){
			$this->db->where("t1.institucion_id",$this->user->institucion_id);
		}

		if($search["value"]){
			$this->db->like("json",$search["value"]);
		}
		if($order){
			$this->db->order_by("servicio_id");
		}
		if($start && $length){
			$this->db->limit($length,$start);
		}
		$query	=	$this->db->get();
		$rows		=	$query->result_array();
		if(!empty($rows)){
			$_rows=[];
			$contador=0;
			foreach ($rows as $key => $value) {
				if(bool_recurso($value["recurso_id"])){
					$_rows[$contador]=$value;
					$json	=	json_decode($value["json"]);
					$_rows[$contador]["Codigo"]	=	$json->Codigo;
					$_rows[$contador]["TipoLavadora"]	=	$json->TipoLavadora;
					$_rows[$contador]["Capacidad_Peso"]	=	$json->Capacidad_Peso;
					$_rows[$contador]["Precio_Hora"]	=	$json->Precio_Hora;
					$contador++;
				}
			}
			$count=$this->db->query('SELECT FOUND_ROWS() count;')->row()->count;
	 		$return_ = array(	"data"=>foreach_edit($_rows,$count),
	 											"recordsTotal"=>$count,
												"recordsFiltered"=>$count,
	 											"limit"=>$limit);
			$this->result	=	$return_;
			return $return_;
	 	}else{
			$return_ = array(	"data"=>[],
	 											"recordsTotal"=>0,
												"recordsFiltered"=>0,
	 											"limit"=>10);
			$this->result	=	$return_;
			return $return_;
		}

	}


	public function getRecursosDisponibles(){
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;

		$tabla	=	DB_PREFIJO."op_inventario t1";
		$tabla2	=	DB_PREFIJO."centros_de_costo t2";
		$this->db->select("SQL_CALC_FOUND_ROWS  inventario_id", FALSE)
							->select('	t1.inventario_id,
													t1.inventario_id as id,
													t1.json')->from($tabla);

		if($this->user->tipo_usuario_id>0 || $this->user->institucion_id>0){
			$this->db->where("institucion_id",$this->user->institucion_id);
		}

		if($search["value"]){
			$this->db->like("json",$search["value"]);
		}
		if($order){
			$this->db->order_by("inventario_id");
		}
		if($start && $length){
			$this->db->limit($length,$start);
		}
		$query	=	$this->db->get();
		$rows		=	$query->result_array();
		if(!empty($rows)){
			$_rows=[];
			$contador=0;
			foreach ($rows as $key => $value) {
				if(!bool_recurso($value["inventario_id"])){
					$_rows[$contador]=$value;
					$json	=	json_decode($value["json"]);
					foreach ($json as $key2 => $value2) {
						$_rows[$contador][$key2]	=	$value2;
					}
					$contador++;
				}
			}
			$count=$this->db->query('SELECT FOUND_ROWS() count;')->row()->count;
	 		$return_ = array(	"data"=>foreach_edit($_rows,$count),
	 											"recordsTotal"=>$count,
												"recordsFiltered"=>$count,
	 											"limit"=>$limit);
			$this->result	=	$return_;
			return $return_;
	 	}else{
			$return_ = array(	"data"=>[],
	 											"recordsTotal"=>0,
												"recordsFiltered"=>0,
	 											"limit"=>10);
			$this->result	=	$return_;
			return $return_;
		}

	}

	public function getInventario(){
		$order	=	get("order");
		$search	=	get("search");
		$start	=	get("start");
		$length	=	get("length");
		$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;

		$tabla	=	DB_PREFIJO."op_inventario t1";
		$tabla2	=	DB_PREFIJO."centros_de_costo t2";
		$this->db->select("SQL_CALC_FOUND_ROWS  inventario_id", FALSE)
							->select('	t1.inventario_id,
													t1.inventario_id as id,
													t1.json,
													t1.token,
													"edit" as edit')->from($tabla);

		if($this->user->tipo_usuario_id>0 || $this->user->institucion_id>0){
			$this->db->where("institucion_id",$this->user->institucion_id);
		}

		if($search["value"]){
			$this->db->like("json",$search["value"]);
		}
		if($order){
			$this->db->order_by("inventario_id");
		}
		if($start && $length){
			$this->db->limit($length,$start);
		}
		$query	=	$this->db->get();
		$rows		=	$query->result_array();
		if(!empty($rows)){
			$_rows=[];
			foreach ($rows as $key => $value) {
				$_rows[$key]=$value;
				$json	=	json_decode($value["json"]);
				foreach ($json as $key2 => $value2) {
					$_rows[$key][$key2]	=	$value2;
				}
				$url	=	DOMINIO.'/ApiRest/post?modulo=Operatividad&m=QR&t=E&formato=redirect&token='.$value["token"];
				$_rows[$key]["qr"]	=	'<a href="'.$url.'" target="_blank" ><img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl='.$url.'&choe=UTF-8" alt="PGRW" /></a>';
			}
			$count=$this->db->query('SELECT FOUND_ROWS() count;')->row()->count;
	 		$return_ = array(	"data"=>foreach_edit($_rows,$count),
	 											"recordsTotal"=>$count,
												"recordsFiltered"=>$count,
	 											"limit"=>$limit);
			$this->result	=	$return_;
			return $return_;
	 	}else{
			$return_ = array(	"data"=>[],
	 											"recordsTotal"=>0,
												"recordsFiltered"=>0,
	 											"limit"=>10);
			$this->result	=	$return_;
			return $return_;
		}

	}

	// public function getInventario(){
	// 	$tabla1	=	 	DB_PREFIJO."op_inventario t1";
	// 	$tabla2	=	 	DB_PREFIJO."ma_productos t2";
	// 	$order	=	get("order");
	// 	$search	=	get("search");
	// 	$start	=	get("start");
	// 	$length	=	get("length");
	// 	$limit	= ($length)?$length:ELEMENTOS_X_PAGINA;
	// 	$sql		=		'SELECT	SQL_CALC_FOUND_ROWS inventario_id,
	// 									t2.tipo as sub_tipo,
	// 									inventario_id as id,
	// 									json,
	// 									"edit" as edit
	// 									FROM '.$tabla1.'
	// 										LEFT JOIN '.$tabla2.' ON t1.tipo_id = t2.tipo_id
	// 											GROUP BY factura';
	//
	// 	$rows			=		$this->db->query($sql)->result();
	// 	$count		=		$this->db->query('SELECT FOUND_ROWS() count;')->row()->count;
	// 	$_rows		=		[];
	// 	foreach ($rows as $key => $value) {
	// 		$_rows[$key]	=	$value;
	// 		$_rows[$key]	=	campo_json_db($value);
	// 	}
	// 	$return_ 	= 	array(	"data"=>	foreach_edit($rows,$count),
	// 												"recordsTotal"=>$count,
	// 												"recordsFiltered"=>$count,
	// 												"limit"=>$limit);
	// 	$this->result	=	$return_;
	// 	return $return_;
	// }

	public function set_Recursos(){
		$tabla	=	 DB_PREFIJO."op_inventario";
		$post=post();
		$post["json"]	=	json_encode($post["json"]);
		unset($post["redirect"]);
		if(post("inventario_id")>0){
			$this->db->where("inventario_id",post("inventario_id"));
			$this->db->update($tabla,$post);
		}else{
			$post["token"]=token();
			$this->db->insert($tabla,$post);
		}
	}


	function response(){
		return $this->result;
	}

}
?>
