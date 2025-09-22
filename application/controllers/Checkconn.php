<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Checkconn extends Cl_Controller {

    public function __construct() {
        parent::__construct();
    }

  public function index() {
    if ($this->session->has_userdata('user_id')) {
      $return = [
          'check' => true,
      ];
    } else {
      $return = [
          'check' => false,
      ];
    }
    echo json_encode($return);
  }


}