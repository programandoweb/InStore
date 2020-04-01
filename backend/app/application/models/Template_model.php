<?php
/*
	DESARROLLO Y PROGRAMACIÓN
	PROGRAMANDOWEB.NET
	LCDO. JORGE MENDEZ
	info@programandoweb.net
*/
defined('BASEPATH') OR exit('No direct script access allowed');

class Template_model extends CI_Model {

	var $campos,$result,$message;

	public function campos_listado(){

	}

	public function Tmpl(){
		$post	=	post();
		$get	=	get();
		echo tmpl($get["view"]);
	}

	function response(){
		return $this->result;
	}

}
?>
