<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Checkconn extends Cl_Controller {

    public function __construct() {
        parent::__construct();
    }

  public function index() {
    $return = [
        'check' => true,
    ];
    echo json_encode($return);
  }


}