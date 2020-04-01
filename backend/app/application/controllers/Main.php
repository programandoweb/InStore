<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {

  var $util,$user,$Response;

	public function __construct(){
        parent::__construct();
		$this->util 		= 	new Util_model();
		$this->user			=	$this->session->userdata('User');
    }

  public function index(){
		$this->util->set_title(SEO_TITLE);
		$this->load->view('Template/Header');
		if(!empty($this->user)){
			if(MODULO_X_DEFAULT){
				redirect(base_url(MODULO_X_DEFAULT));
			}else{
				$this->load->view('Template/Welcome');
			}
		}else{
			redirect(base_url("Autenticacion"));
		}
		$this->load->view('Template/Footer');
	}

}
?>
