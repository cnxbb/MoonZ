<?php  //CODE BY ZMZ
// MoonZ Framework Core
require_once 'config.php';
require_once 'base.class.php';


class MZ_Default_Router extends MObject {
    public function __construct( $_router = null,
                                 $_controller = null,
                                 $_action = null,
                                 $_view = null ) {
        parent::__construct( $_router, $_controller, $_action, $_view );
    }
    //------------------------------------------------------------------------------------

    public function dispatch() {
        $uri = $_SERVER['REQUEST_URI'];

        if( strstr( $uri, '?' ) ) {
            // 获取路由
            $uri = strstr( $_SERVER['REQUEST_URI'], '?', true );
        }
        $arr = explode( '/', $uri );

        $ret = array();
        if( isset( $arr[1] ) && strlen( $arr[1] ) > 0 ) {
            $ret['controller'] = strtolower( trim( $arr[1] ) );
        } else {
            $ret['controller'] = 'index';
        }

        if( isset( $arr[2] ) && strlen( $arr[2] ) > 0 ) {
            $ret['action'] = $ret['view']  = strtolower( trim( $arr[2] ) );
        } else {
            $ret['action'] = $ret['view'] = 'index';
        }
        if( isset( $ret['controller'] ) &&
            isset( $ret['action'] ) &&
            isset( $ret['view'] ) ) {
            $ret['catched'] = true;
        }
        return $ret;
    }
    //------------------------------------------------------------------------------------
}
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------

class MZ_Controller_Base extends MObject {
    protected $view;
    public static $action_name;
    public function __construct() {

    }
    //------------------------------------------------------------------------------------

    // set view instance
    public function set_view( $_view_instance ) {
        $this->view = $_view_instance;
    }
    //------------------------------------------------------------------------------------

    // set/get action name
    public function set_action_name( $_action_name ) {
        MZ_Controller_Base::$action_name = $_action_name;
    }
    //------------------------------------------------------------------------------------
    public function getActionName() {
        return MZ_Controller_Base::$action_name;
    }
    //------------------------------------------------------------------------------------

    //400 Err
    public function Error400() {
        http_response_code(400);
        require_once MZ_Error_Page_400;
        exit;
    }
}
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------

class MZ_View_Base extends MObject{
    public function __construct() {

    }
    //------------------------------------------------------------------------------------

    public function dispatch( $str_view_fn, $bExit = false ) {
        if( file_exists( $str_view_fn ) ) {
            require $str_view_fn;
        }
        if( $bExit ) {
            exit;
        }
    }
    //------------------------------------------------------------------------------------
}
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------

class MZ_APP extends MObject {
    private $router_list;
    static public $str_ctrl_fn;
    static public $str_ctrl_class;
    static public $obj_ctrl_class;
    static public $str_action;
    static public $obj_ctrl_act;
    static public $instance_ctrl;
    static public $str_view_fn;

    public function __construct() {
        set_error_handler( 'MZ_APP::error_handler', E_ERROR | E_PARSE | E_USER_DEPRECATED );
        register_shutdown_function( 'MZ_APP::fatal_error_handler' );
        set_exception_handler( 'MZ_APP::exception_handler' );
        $this->router_list = array( new MZ_Default_Router() );
    }
    //------------------------------------------------------------------------------------

    //指定视图
    static public function set_view( $ctrl, $view ) {
        MZ_APP::$str_view_fn = sprintf( "%s%s/%s.php", MZ_View_Path, $ctrl, $view );
        if( isset( $GLOBALS["Current_Route"] ) ) {
            $GLOBALS["Current_Route"]['controller'] = $ctrl;
            $GLOBALS["Current_Route"]['view'] = $view;
        }
    }
    //------------------------------------------------------------------------------------

    //
    public function add_router( $_router ) {
        array_push(  $this->router_list, $_router );
    }
    //------------------------------------------------------------------------------------

    //
    public function get_router() {
        $def_route = array( 'controller' => 'index', 'action' => 'index', 'view' => 'index' );

        while( count( $this->router_list ) > 0 ) {
            $routeObj = array_pop( $this->router_list );
            $route = $routeObj->dispatch();
            if( isset( $route['catched'] ) && $route['catched'] === true ) {
                return $route;
            }
        }


        return $def_route;
    }
    //------------------------------------------------------------------------------------

    // 函数说明
    static public function parse() {

        $view = new MZ_View_Base();
        $GLOBALS['MoonZ_View'] = $view;
        if( file_exists( MZ_APP::$str_ctrl_fn ) ) {
            require_once MZ_APP::$str_ctrl_fn;
            if( class_exists( MZ_APP::$str_ctrl_class ) ) {
                MZ_APP::$obj_ctrl_class = new ReflectionClass( MZ_APP::$str_ctrl_class );
                if( MZ_APP::$obj_ctrl_class->hasMethod( MZ_APP::$str_action ) ) {
                    MZ_APP::$obj_ctrl_act = new ReflectionMethod( MZ_APP::$str_ctrl_class, MZ_APP::$str_action );
                    if( MZ_APP::$obj_ctrl_act->isPublic() && !MZ_APP::$obj_ctrl_act->isStatic() ) {
                        MZ_Controller_Base::$action_name = MZ_APP::$str_action;
                        MZ_APP::$instance_ctrl = MZ_APP::$obj_ctrl_class->newInstance();
                    } else {
                        if( defined( 'MZ_EnableDebugLog' ) && MZ_EnableDebugLog == true ) {
                            MObject::ZLog( 'Class: ' . MZ_APP::$str_ctrl_class . ' Method: ' . MZ_APP::$str_action . " not public or is static" );
                        }
                        MObject::ZLog( 'HTTP:404', true );
                        http_response_code(404);
                        require_once MZ_Error_Page_404;
                        exit;
                    }
                } else {
                    if( defined( 'MZ_EnableDebugLog' ) && MZ_EnableDebugLog == true ) {
                        MObject::ZLog( 'Class: ' . MZ_APP::$str_ctrl_class . ' Method: ' . MZ_APP::$str_action . " not exists" );
                    }
                }
            }

            if( isset( MZ_APP::$instance_ctrl ) ) {
                MZ_APP::$instance_ctrl->set_view( $view );
            }
        } else {
            if( defined( 'MZ_EnableDebugLog' ) && MZ_EnableDebugLog == true ) {
                MObject::ZLog( 'File: ' . MZ_APP::$str_ctrl_fn . ' not exists' );
            }
        }

        if( isset( MZ_APP::$instance_ctrl ) ) {
            MZ_APP::$obj_ctrl_act->invoke( MZ_APP::$instance_ctrl );
        }
        if( file_exists( MZ_APP::$str_view_fn ) ) {
            $view->dispatch( MZ_APP::$str_view_fn );
        } else {
            if( defined( 'MZ_EnableDebugLog' ) && MZ_EnableDebugLog == true ) {
                MObject::ZLog( 'File: ' . MZ_APP::$str_view_fn . ' not exists' );
            }
            if( defined( 'MZ_Error_Page_404' ) && file_exists( MZ_Error_Page_404 ) ) {
                MObject::ZLog( 'HTTP:404', true );
                http_response_code(404);
                require_once MZ_Error_Page_404;
                exit;
            }
        }
    }
    //------------------------------------------------------------------------------------

    static public function parse_error( $err_info ) {
        MZ_APP::$str_ctrl_fn = sprintf( '%s%sController.php', MZ_Conrtoller_Path, 'error' );
        MZ_APP::$str_ctrl_class = sprintf( '%sController', 'error' );
        MZ_APP::$str_action = 'error';
        MZ_APP::$str_view_fn = sprintf( "%s/%s/%s.php", MZ_View_Path, 'error', 'error' );
        if( file_exists( MZ_APP::$str_ctrl_fn ) ) {
            require_once MZ_APP::$str_ctrl_fn;
            if( class_exists( MZ_APP::$str_ctrl_class ) ) {
                MZ_APP::$obj_ctrl_class = new ReflectionClass( MZ_APP::$str_ctrl_class );
                if( MZ_APP::$obj_ctrl_class->hasMethod( MZ_APP::$str_action ) ) {
                    MZ_APP::$obj_ctrl_act = new ReflectionMethod( MZ_APP::$str_ctrl_class, MZ_APP::$str_action );
                    if( MZ_APP::$obj_ctrl_act->isPublic() && !MZ_APP::$obj_ctrl_act->isStatic() ) {
                        MZ_Controller_Base::$action_name = MZ_APP::$str_action;
                        MZ_APP::$instance_ctrl = MZ_APP::$obj_ctrl_class->newInstance();
                        MZ_APP::$instance_ctrl->err_info = $err_info;
                    }
                }
            }
        }
        if( file_exists( MZ_APP::$str_view_fn ) ) {
            $view = new MZ_View_Base();
            if( isset( MZ_APP::$instance_ctrl ) ) {
                MZ_APP::$instance_ctrl->set_view( $view );
            }
        }
        if( isset( MZ_APP::$instance_ctrl ) ) {
            MZ_APP::$obj_ctrl_act->invoke( MZ_APP::$instance_ctrl );
        }

        if( isset( $view ) ) {
            $view->dispatch( MZ_APP::$str_view_fn );
        }

        if( !isset( MZ_APP::$instance_ctrl ) && !isset( $view ) ) {
            if( defined( 'MZ_Error_Page_404' ) && file_exists( MZ_Error_Page_404 ) ) {
                MObject::ZLog( 'HTTP:404', true );
                http_response_code(404);
                require_once MZ_Error_Page_404;
                exit;
            }
        }
    }
    //------------------------------------------------------------------------------------

    //
    public function run() {
        //get route
        $route = $this->get_router();
        $GLOBALS["Current_Route"]  = $route;
        if( defined( 'MZ_EnableDebugLog' ) && MZ_EnableDebugLog == true ) {
            MObject::ZLog( $GLOBALS["Current_Route"] );
        }
        //call controll and render
        MZ_APP::$str_ctrl_fn = sprintf( '%s%sController.php', MZ_Conrtoller_Path, $route['controller'] );
        MZ_APP::$str_ctrl_class = sprintf( '%sController', $route['controller'] );
        MZ_APP::$str_action = $route['action'];
        MZ_APP::$str_view_fn = sprintf( "%s%s/%s.php", MZ_View_Path, $route['controller'], $route['view'] );
        if( !file_exists( MZ_APP::$str_ctrl_fn ) ) {
            $str_uncatchedctrl_fn = sprintf( '%s_uncatchedController.php', MZ_Conrtoller_Path );
            if( file_exists( $str_uncatchedctrl_fn ) ) {
                require_once $str_uncatchedctrl_fn;
                if( class_exists( 'uncatchedController' ) ) {
                    $_obj_ctrl_class = new ReflectionClass( 'uncatchedController' );
                    if( $_obj_ctrl_class->hasMethod( 'dispatch' ) ) {
                        $_obj_ctrl_dispatch = new ReflectionMethod( 'uncatchedController', 'dispatch' );
                        if( $_obj_ctrl_dispatch->isPublic() && !$_obj_ctrl_dispatch->isStatic() ) {
                            $_ctlInstanc = new uncatchedController();
                            $_ctlInstanc->dispatch();
                            exit;
                        }
                    }
                }
            }
        }
        MZ_APP::parse();
    }

    //------------------------------------------------------------------------------------

    static public function error_handler( $errno, $errstr, $errfile, $errline ) {
        $err = [
            'from' => 'error_handler',
            'code' => $errno,
            'msg'  => $errstr,
            'file' => $errfile,
            'line' => $errline
        ];
        MObject::ZLog( $err );
        MZ_APP::parse_error( $err );

        exit;
    }
    //------------------------------------------------------------------------------------

    static public function exception_handler( $exception ) {
        $err = [
            'from' => 'exception_handler',
            'code' => $exception->getCode(),
            'msg'  => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ];
        MZ_APP::parse_error( $err );
    }
    //------------------------------------------------------------------------------------

    static public function fatal_error_handler() {
        $e = error_get_last();
        if( intval( $e['type'] ) == 1 || intval( $e['type'] ) == 4 ) {
            $err = [
                'from' => 'fatal_error_handler',
                'code' => $e['type'],
                'msg'  => $e['message'],
                'file' => $e['file'],
                'line' => $e['line']
            ];
            MZ_APP::parse_error( $err );
        }

    }
    //------------------------------------------------------------------------------------


}
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------




