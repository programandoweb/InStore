<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notificaciones_model extends CI_Model {

	var $fields,$result,$where,$total_rows,$pagination,$search,$response,$campos,$message;

	function setNotificacion(){
		$post=post();
		$post["fecha"]=date("Y-m-d H:i:s");
		$this->db->insert(DB_PREFIJO."op_observaciones_generales",$post);
		$get_observaciones=get_observaciones($post["quien_hace_la_observacion_id"],$post["quien_recibe_va_la_observacion_id"]);
		$html	=	"";
		if (!empty($get_observaciones)) {
			foreach ($get_observaciones as $key => $value) {
				$html	.=	"<tr>";
					$html	.=	"<td>";
						$html	.=	$value->observaciones_id;
					$html	.=	"</td>";
					$html	.=	"<td>";
						$html	.=	$value->fecha;
					$html	.=	"</td>";
					$html	.=	"<td>";
						$html	.=	tipo_de_observacion($value->tipo);
					$html	.=	"</td>";
					$html	.=	"<td>";
						$html	.=	$value->observacion;
					$html	.=	"</td>";
					$html	.=	"<td>";
						$html	.=	($value->estatus==0)?"No leido":"Leido";;
					$html	.=	"</td>";
				$html	.=	"</tr>";
			}
			$return_ = array(	"html"=> $html);
			$this->result	=	$return_;
			return $return_;
		}
	}

	function response(){
		return $this->result;
	}
}
?>
