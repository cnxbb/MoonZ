<?php  //CODE BY ZMZ
// API Controller
require_once 'BaseController.php';
require_once 'model.base.class.php';

ini_set( 'memory_limit', '8G' );
class apiController extends BaseController{
    public function __construct() {
        parent::__construct();
        $this->db->EnableErrorHandler( false );
    }
    //------------------------------------------------------------------------------------

    //从请求的原始数据的只读流中获取参数
    private function get_params_from_rawjson() {
        //获取参数
        $pstr = file_get_contents( "php://input" );
        MObject::ZLog( "php://input: " . $pstr );
        // 如果有获取到传参将json数据转为数组return
        if( strlen( $pstr ) > 0 ) {
            $arr = json_decode( $pstr, true );
            if( json_last_error() === JSON_ERROR_NONE ) {
                return $arr;
            } else {
                MObject::ZLog( "json_decode fail" );
            }
        }
        return NULL;
    }
    //------------------------------------------------------------------------------------

    //从POST和GET数组中获取参数 如果同名POST优先
    private function get_params_from_postandget() {
        return array_merge( $_GET, $_POST );
    }
    //------------------------------------------------------------------------------------

    // API入口
    public function index() {
        //参数处理
        $param_arr = $this->get_params_from_rawjson();
        if( is_null( $param_arr ) ) {
            $param_arr = $this->get_params_from_postandget();
        }
        if( count( $param_arr ) === 0 ) {
            $this->JError( 'param not found' );
            exit;
        }

        $response = array();

        //调用指定的接口
        if( isset( $param_arr[0] ) ) {
            foreach( $param_arr as $item ) {
                foreach( $item as $model_name => $params ) {
                    //加载相应模块文件
                    $model_fn = sprintf( "%s/../APP/models/Function/%s.class.php", $_SERVER['DOCUMENT_ROOT'], $model_name );
                    if( !file_exists( $model_fn ) ) {
                        $this->JError( "function module : {$model_fn} not found！" );
                    }
                    require_once $model_fn;

                    //检查模块类是否存在
                    $model_class_name = "C" . $model_name;
                    if( !class_exists( $model_class_name ) ) {
                        $this->JError( "class {$model_class_name} not defined" );
                    }

                    //功能调用
                    $obj = new $model_class_name( $params, $this->db, $this->memcache );
                    $key = sprintf( '%s_%s', $model_name, $params['act'] );
                    $response[$key] = $obj->Run();
                }
            }
            if( count( $response ) == 1 ) {
                $this->JReturn( current( $response ) );
                exit;
            }
            $response['ret'] = 1;
            $this->JReturn( $response );
            exit;
        }
        foreach( $param_arr as $model_name => $params ) {
            //加载相应模块文件
            $model_fn = sprintf( "%s/../APP/models/Function/%s.class.php", $_SERVER['DOCUMENT_ROOT'], $model_name );
            //MObject::ZLog( $model_fn );
            if( !file_exists( $model_fn ) ) {
                //echo $model_fn . "<br>\n";
                $this->JError( "function module : {$model_fn} not found！" );
            }

            require_once $model_fn;

            //检查模块类是否存在
            $model_class_name = "C" . $model_name;
            //MObject::ZLog( $model_class_name );
            if( !class_exists( $model_class_name ) ) {
                $this->JError( "class {$model_class_name} not defined" );
            }

            //功能调用
            $obj = new $model_class_name( $params, $this->db, $this->memcache );
            $response[$model_name] = $obj->Run();
            //MObject::ZLog( 'after run:' . var_export( $response, true ) );
        }

        //返回数据
        if( count( $response ) == 1 ) {
            $this->JReturn( current( $response ) );
            exit;
        }
        $response['ret'] = 1;
        $this->JReturn( $response );
        exit;
    }
    //------------------------------------------------------------------------------------

    // 获取服务器当前时间戳
    public function time() {
        $ret['ret'] = 1;
        $ret['time'] = date('Y-m-d H:i:s');
        $ret['timestamp'] = time();
        $this->JReturn($ret);
        exit;
    }
    //------------------------------------------------------------------------------------
}
