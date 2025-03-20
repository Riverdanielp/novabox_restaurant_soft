<?php
/*
  ###########################################################
  # PRODUCT NAME: 	iRestora PLUS - Next Gen Restaurant POS
  ###########################################################
  # AUTHER:		Doorsoft
  ###########################################################
  # EMAIL:		info@doorsoft.co
  ###########################################################
  # COPYRIGHTS:		RESERVED BY Door Soft
  ###########################################################
  # WEBSITE:		http://www.doorsoft.co
  ###########################################################
  # This is Update Controller
  ###########################################################
 */
defined('BASEPATH') OR exit('No direct script access allowed');
class Update extends Cl_Controller {

    protected $update;
    protected $my_info;
    function __construct(){
        parent::__construct();
        $this->load->library('form_validation');
        $this->my_info = json_decode(file_get_contents(base_url(str_rot13('/nffrgf/oyhrvzc/ERFG_NCV_HI.wfba'))));
        $this->update = json_decode(file_get_contents((str_rot13($this->my_info->url))));
    }

     /**
     * update view page
     * @access public
     * @return void
     * @param no
     */
    public function index(){
    }
    /**
     * update view page
     * @access public
     * @return void
     * @param no
     */
    public function updateVerification(){
    }

    public function rrmdir($dir) {
    }
    /**
     * update view page
     * @access public
     * @return void
     * @param no
     */
    public function uninstallLicense(){
    }
     /**
     * do update after submit the button
     * @access public
     * @return void
     * @param no
     */
    function do_update(){
    }
 
     /**
     * install update after download file
     * @access public
     * @return int
     * @param no
     */
    public function install_update(){

    }
     /**
     * download file from path
     * @access public
     * @return boolean
     * @param string
     * @param string
     */
    public function downloadFile($url, $path) {
    }
     /**
     * recurse copy from destination
     * @access public
     * @return void
     * @param string
     * @param string
     */
    protected function recurse_copy($src, $dst) {
    }
     /**
     * help view page
     * @access public
     * @return void
     * @param no
     */
    public function help(){
        //generate html content for view
        echo 'contact support information will go here!';
    }
}