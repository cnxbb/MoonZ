<?php  //CODE BY ZMZ
// index Controller
require_once 'BaseController.php';

class indexController extends BaseController{
    public function __construct() {
        parent::__construct();

    }
    //------------------------------------------------------------------------------------

    //首页
    public function index() {
        if( !$this->MSE->Get('uid') ||
            !$this->MSE->Get('uinfo') ||
            intval( $this->MSE->Get('uid') ) < 1 ||
            !is_array( $this->MSE->Get('uinfo') ) ) {
            header( "Location: /index/login/" );
            exit;
        }
    }
    //------------------------------------------------------------------------------------
}
