<?php  //CODE BY ZMZ
// uncatchedController
require_once 'BaseController.php';

class uncatchedController extends BaseController{
    public function __construct() {
        parent::__construct();

    }
    //------------------------------------------------------------------------------------


    public function dispatch() {
        $this->ErrPage404();
        exit;
    }
}


