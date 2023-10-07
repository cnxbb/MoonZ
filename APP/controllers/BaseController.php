<?php  //CODE BY ZMZ
// Controller Base Class
require_once 'db.pdomysql.class.php';
require_once 'model.base.class.php';
require_once 'cache.class.php';
require_once 'MemcacheSession.class.php';

class BaseController extends MZ_Controller_Base {
    public $db = null;
    public $memcache = null;
    public $response_b64encode;
    public $display_sql_error;
    public $MSE = null;
    public function __construct() {
        header("Access-Control-Allow-Origin: *");

        parent::__construct();

        //设置 JError JReturn 函数输出是否使用Base64编码
        $this->response_b64encode = false;

        //设置 JError 函数输出是否包含最后执行的SQL语句和SQL错误信息
        $this->display_sql_error = false;

        //init pdo-mysql
        $this->db = new MZ_Pdo_Mysql( MZ_DB_Database,
                                      MZ_DB_Host,
                                      MZ_DB_User,
                                      MZ_DB_Password
                                    );
        $this->db->EnableExisOnError( 1 );
        $this->db->EnableErrorLog();
        $this->db->SetErrorHandler( 'Default_Database_Error_Handler' );
        $this->db->EnableErrorHandler();

        //init memcache
        $this->memcache = new Memcache;
		if( $this->memcache->connect( MZ_MEMCACHE_Host, MZ_MEMCACHE_Port ) === false ) {
            echo 'memcache not ready';
            $this->memcache = null;
        }

        $this->MSE = new CMemcacheSession( $this->memcache );
        $GLOBALS['MSE'] = $this->MSE;
        //如果未登陆 进行COOKIE登陆尝试
        $uid = $this->MSE->Get('uid');
        if( !$uid || intval( $uid ) < 1 ) {
            $this->CookieLogin( $this->db );
        }
    }
    //------------------------------------------------------------------------------------

    //404页面
    public function ErrPage404() {
        MObject::ZLog( 'HTTP:404', true );
        http_response_code(404);
        if( defined( 'MZ_Error_Page_404' ) && file_exists( MZ_Error_Page_404 ) ) {
            require_once MZ_Error_Page_404;
        }
        exit;
    }
    //------------------------------------------------------------------------------------

    //400页面
    public function ErrPage400() {
        MObject::ZLog( 'HTTP:400', true );
        http_response_code(400);
        if( defined( 'MZ_Error_Page_400' ) && file_exists( MZ_Error_Page_400 ) ) {
            require_once MZ_Error_Page_400;
        }
        exit;
    }
    //------------------------------------------------------------------------------------

    //500页面
    public function ErrPage500( $msg = '' ) {
        $GLOBALS['errstr'] = $msg;
        MObject::ZLog( 'HTTP:500', true );
        http_response_code(500);
        if( defined( 'MZ_Error_Page_500' ) && file_exists( MZ_Error_Page_500 ) ) {
            require_once MZ_Error_Page_500;
        }
        exit;
    }
    //------------------------------------------------------------------------------------

    //输出JSON数据
    public function JReturn( $json = NULL, $base64 = NULL ) {
        $b64 = $base64 === true || ( is_null( $base64 ) && $this->response_b64encode === true );
        if( is_null( $json ) ) {
            if( $b64 ) {
                echo base64_encode( str_replace( 'null', '""', json_encode( array( 'ret' => 1 ) ) ) );
            } else {
                echo str_replace( 'null', '""', json_encode( array( 'ret' => 1 ) ) );
            }
        } else {
            if( $b64 ) {
                echo base64_encode( str_replace( 'null', '""', json_encode( $json ) ) );
            } else {
                echo str_replace( 'null', '""', json_encode( $json ) );
            }
        }
        exit;
    }
    //------------------------------------------------------------------------------------

    //输出JSON格式错误信息
    public function JError( $errmsg, $base64 = NULL ) {
        $b64 = $base64 === true || ( is_null( $base64 ) && $this->response_b64encode === true );
        $ret['ret'] = 0;
        $ret['errmsg'] = $errmsg;
        if( $this->display_sql_error === true && !is_null( $this->db ) ) {
            $ret['sql'] = $this->db->LastSQL();
            $ret['dberr'] = $this->db->ErrorInfo();
        }
        if( $b64 ) {
            echo base64_encode( str_replace( 'null','""', json_encode( $ret ) ) );
        } else {
            echo str_replace( 'null', '""', json_encode( $ret ) );
        }
        exit;
    }
    //------------------------------------------------------------------------------------

    //创建目录
    public function CreatePath( $path ){
        if( !file_exists( $path ) ) {
            $this->CreatePath( dirname( $path ) );
            mkdir( $path, 0777 );
            chmod( $path, 0777 );
        }
    }
    //------------------------------------------------------------------------------------

    /** 验证当前用户类型
    * @param 需要具备的用户类型 可传入多个参数 无参数调用只检查登录状态
    * @param 如果参数中有string类型 'json'   : 如果验证失败输出含errmsg的json串
                                   'code'   : 如果验证失败输出403响应码
                                   'return' : 如果验证失败函数返回false
             如果未发现string类型 按 'json' 方式处理
    * @return bool;
    */
    public function RequireUT() {
        $num = func_num_args();
        $args = func_get_args();
        $utarr = array();
        $ret_type = 'json';

        foreach( $args as $argitem ) {
            if( is_int( $argitem ) ) {
                $utarr[] = $argitem;
            }
            if( is_string( $argitem ) && in_array( $argitem, array( 'json','code','return' ) ) ) {
                $ret_type = $argitem;
            }
        }
        $SE = $this->MSE->Get();
        if( !isset( $SE['utype'] ) ) {
            if( $ret_type == 'code' ) {
                header('HTTP/1.1 403 Forbidden');
                if( file_exists( $_SERVER['DOCUMENT_ROOT'] . '/403.html' ) ) {
                    require_once $_SERVER['DOCUMENT_ROOT'] . '/403.html';
                }
                exit;
            }
            if( $ret_type == 'return' ) {
                return false;
            }
            echo json_encode( array( 'ret' => 0, 'errmsg' => '权限不足' ) );
            exit;
        }

        if( count( $utarr ) > 0 && !in_array( $SE['utype'], $utarr ) ) {
            if( $ret_type == 'code' ) {
                header('HTTP/1.1 403 Forbidden');
                if( file_exists( $_SERVER['DOCUMENT_ROOT'] . '/403.html' ) ) {
                    require_once $_SERVER['DOCUMENT_ROOT'] . '/403.html';
                }
                exit;
            }
            if( $ret_type == 'return' ) {
                return false;
            }
            echo json_encode( array( 'ret' => 0, 'errmsg' => '权限不足' ) );
            exit;
        }
        return true;
    }
    //------------------------------------------------------------------------------------

    public function CookieLogin() {
        require_once 'rsa.class.php';
        $rp = $_SERVER['DOCUMENT_ROOT'] . '/../APP/pem/';
        $rsa = new RSA( file_get_contents( $rp . 'rsa_private_key.pem' ),
                        file_get_contents( $rp . 'rsa_public_key.pem' ) );

        if( isset( $_COOKIE['remember'] ) ) {
            $remember = $rsa->Decrypt( $_COOKIE['remember'] );
            list( $uname, $upass ) = explode( '|', $remember );
            $tpl = "select * from table_SysUsers where m_UserName = '%s' and m_Password = '%s' and m_Delete = 0";
            $sql = sprintf( $tpl, addslashes( $uname ), addslashes( $upass ) );
            $row = $this->db->run( $sql );
            if( $row === false || count( $row ) < 1 ) {
                return false;
            }

            $this->MSE->Set( 'uid', $row[0]['m_UID'] );
            $this->MSE->Set( 'utype', $row[0]['m_UserType'] );
            unset( $row[0]['m_Password'] );
            $this->MSE->Set( 'uinfo', $row[0] );
            return true;
        }
        return false;
    }
}


