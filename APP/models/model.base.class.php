<?php  //CODE BY ZMZ
// 功能模块基类

abstract class API_Model_BASE{
    protected $db;
    protected $memcache;
    protected $param;
    public function __construct( $_param, $_db, $_memcache  ) {
        $this->param = $_param;
        $this->db = $_db;
        $this->memcache = $_memcache;
    }
    //------------------------------------------------------------------------------------

    //子类必须实现Run函数
    abstract protected function Run();

    //日志
    public function XLog( $msg = '',  //需要记录的信息
                          $log_request_param = false ) { //记录GET 和 POST 参数
        $file = basename( $_SERVER['SCRIPT_FILENAME'] );
        if( defined( 'MZ_Global_Log_File' ) ) {
            $logfn = MZ_Global_Log_File;
        } else {
            $logfn = $_SERVER['DOCUMENT_ROOT'] . '/../APP/logs/app_log.txt';
        }

        $query = isset( $_SERVER['QUERY_STRING'] ) ? $_SERVER['QUERY_STRING'] : '';
        $poststr = var_export( $_POST, true );
        if( !isset( $_SERVER['REQUEST_URI'] ) ) {
            $_SERVER['REQUEST_URI'] = '';
        }
        $logstr = date( 'Y-m-d H:i:s' ) . '    ' . $_SERVER['REQUEST_URI'] . " --------------------\n";
        if( $log_request_param  ) {
            $logstr .= __FILE__ . "\n";
            $z = debug_backtrace();
            //unset($z[0]);
            foreach( $z as $row ) {
               $logstr .= $row['file'].':'.$row['line'].'行,调用方法:'.$row['function']."\n";
            }
            $logstr .= "File: " . $file . "\nQuery: " . $query . "\nPost: " . $poststr . "\n";
            $logstr .= "Raw input:\n" . file_get_contents( "php://input" ) . "\n";
            if( isset( $GLOBALS['RAWPOST'] ) ) {
                $logstr .= var_export( $GLOBALS['RAWPOST'], true ) . "\n";
            }
        }
        if( isset( $msg ) ) {
            $logstr .= "Msg: " . var_export( $msg, true ) ;
        }

        $logstr .= "\n\n";
        $f = fopen( $logfn, "a+" );
        if( $f !== false ) {
            fwrite( $f, $logstr );
            fclose( $f );
        }
    }
    //------------------------------------------------------------------------------------

    //获取模块参数
    public function GetParam( $key, $default_val = '', $data_type_convert_fnc = null ) {
        if( isset( $this->param[$key] ) ) {
            if( !is_null( $data_type_convert_fnc ) ) {
                return $data_type_convert_fnc( $this->param[$key] );
            }
            return $this->param[$key];
        }
        return $default_val;
    }
    //------------------------------------------------------------------------------------

    //返回数据附带注释
    public function Info( $_info ) {
        if( isset( $_SERVER['HTTP_REFERER'] ) &&
            strcmp( $_SERVER['HTTP_REFERER'], 'text->proxy' ) == 0 &&
            defined( "GDEBUG_TAG" ) &&
            intval( GDEBUG_TAG ) == 1
        ) {
            return sprintf( "  /*%s*/", $_info );
        }
    }
    //------------------------------------------------------------------------------------

    public function JError( $errmsg, $output_db_error_info = false ) {
        $ret['ret'] = 0;
        $ret['errmsg'] = $errmsg;
        if( $output_db_error_info ) {
            $ret['dberr']['sql'] = $this->db->LastSQL();
            $ret['dberr']['info'] = $this->db->errorInfo();
        }
        return $ret;

    }
    //------------------------------------------------------------------------------------

    public function JReturn( $json = NULL ) {
        if( is_null( $json ) ) {
            return array( 'ret' => 1 );
        } else if( is_string( $json ) ) {
            return array( 'ret' => 1, 'msg' => $json );
        } else {
            //禁止输出 m_Password 字段
            $Filter = array(
                'm_Password',
                'm_Delete'
            );
            foreach( $Filter as $Fitem ) {
                if( isset( $json[$Fitem] ) ) {
                    unset( $json[$Fitem] );
                }
            }

            return $json;
        }
    }
    //------------------------------------------------------------------------------------

    public function CreatePath( $path ){
        if( !file_exists( $path ) ) {
            $this->CreatePath( dirname( $path ) );
            mkdir( $path, 0777 );
            chmod( $path, 0777 );
        }
    }
    //------------------------------------------------------------------------------------


}
//------------------------------------------------------------------------------------

class XModel extends API_Model_BASE {
    public function __construct( $_param = null, $_db = null, $_memcache = null  ) {
        parent::__construct( $_param, $_db, $_memcache );
    }
    //------------------------------------------------------------------------------------

    public function Run() {
        $act = trim( $this->GetParam( 'act' ) );
        if( strlen( $act ) < 1 ) {
            return $this->JError( '缺少参数' );
        }
        if( method_exists($this, $act ) ) {
            return $this->JReturn( $this->$act() );
        }
        return $this->JError( 'Method not found' );
    }
    //------------------------------------------------------------------------------------
}

