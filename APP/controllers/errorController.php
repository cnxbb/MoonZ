<?php  //CODE BY ZMZ
// handler error
require_once 'BaseController.php';

class errorController extends BaseController{
    public function __construct() {
        parent::__construct();
    }
    //------------------------------------------------------------------------------------

    public function error() {
        //$this->ErrPage500();
        echo 'ZError<br>';
        echo '<pre>';
        print_r( $this->err_info );
        echo '</pre>';
        exit;
    }
    //------------------------------------------------------------------------------------
}


?>