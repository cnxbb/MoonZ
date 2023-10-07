<?php  //CODE BY ZMZ
// Mysql 表操作类

class XInitTableBase {
    protected $db;
    protected $params;
    protected $pi_key;
    protected $pu_key;

    public function __construct( $_db, $_params ) {
        $this->db = $_db;
        $this->params = $_params;
        /*
        $pi_keyfile = dirname( __FILE__ ) . '/rsa_private_key.pem';
        $pu_keyfile = dirname( __FILE__ ) . '/rsa_public_key.pem';
        $this->pi_key = openssl_pkey_get_private( file_get_contents( $pi_keyfile ) );
        $this->pu_key = openssl_pkey_get_public( file_get_contents( $pu_keyfile ) );
        */
    }
    //------------------------------------------------------------------------------------

    //加密
    public function Encrypt( $src, $type='public' ) {
        $ret = '';
        $blocksize = 110;
        $curidx = 0;
        if( $type == 'public' ) {
            $key = $this->pu_key;
            $fnc = 'openssl_public_encrypt';
        } else if( $type == 'private' ) {
            $key = $this->pi_key;
            $fnc = 'openssl_private_encrypt';
        }
        $len = strlen( $src );
        $arr = array();
        do {
            $arr[] = substr( $src, $curidx, $blocksize );
            $curidx += $blocksize;
        } while( $curidx < $len );
        for( $i=0; $i<count( $arr ); $i++ ) {
            $_tmp = '';
            $fnc( $arr[$i], $_tmp, $key );
            if( $i!= 0 ) $ret .= "\n";
            $ret .= base64_encode( $_tmp );
        }
        return $ret;
    }
    //------------------------------------------------------------------------------------

    //解密
    public function Decrypt( $src, $type='private' ) {
        $arr = explode( "\n", $src );
        if( $type == 'public' ) {
            $key = $this->pu_key;
            $fnc = 'openssl_public_decrypt';
        } else if( $type == 'private' ) {
            $key = $this->pi_key;
            $fnc = 'openssl_private_decrypt';
        }
        $ret = '';
        foreach( $arr as $line ) {
            $_tmp = '';
            if( trim( $line ) != '' ) {
                $fnc( base64_decode( $line ), $_tmp, $key );
                $ret .= $_tmp;
            }
        }
        return $ret;
    }
    //------------------------------------------------------------------------------------

    //打日志函数
    public function XLog( $msg = '',  //需要记录的信息
                          $log_request_param = false ) { //记录GET 和 POST 参数
        $file = basename( $_SERVER['SCRIPT_FILENAME'] );
        if( defined( 'MZ_Global_Log_File' ) ) {
            $logfn = MZ_Global_Log_File;
        } else {
            $logfn = $_SERVER['DOCUMENT_ROOT'] . '/../APP/logs/app_log.txt';
        }
        $query = $_SERVER['QUERY_STRING'];
        $poststr = var_export( $_POST, true );

        $logstr = date( 'Y-m-d H:i:s' ) . '    ' . $_SERVER['REQUEST_URI'] . " --------------------\n";
        if( $log_request_param  ) {
            $logstr .= __FILE__ . "\n";
            $z = debug_backtrace();
            //unset($z[0]);
            if( count( $z ) > 0 ) {
                foreach( $z as $row ) {
                   $logstr .= $row['file'].':'.$row['line'].'行,调用方法:'.$row['function']."\n";
                }
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

    public function JError( $errmsg, $output_db_error_info = false ) {
        $this->XLog( $errmsg, true );
        $ret['ret'] = 0;
        $ret['errmsg'] = $errmsg;
        return $ret;
    }
    //------------------------------------------------------------------------------------

    public function JReturn( $json = NULL ) {
        if( isset( $json['ret'] ) ) {
            return $json;
        }
        if( is_null( $json ) ) {
            return array( 'ret' => 1 );
        } else if( is_string( $json ) ) {
            return array( 'ret' => 1, 'msg' => $json );
        } else if( is_array( $json ) ) {
            return array( 'ret' => 1, 'data' => $json );
        } else {
            return $json;
        }
    }
    //------------------------------------------------------------------------------------

    //获取参数
    public function GetParam( $key, $def=NULL ) {
        if( isset( $this->params[$key] ) ) {
            return $this->params[$key];
        }
        return $def;
    }
    //------------------------------------------------------------------------------------

    //处理请求
    public function Parse( $_param = null ) {
        if( is_array( $_param ) ) {
            foreach( $_param as $k => $v) {
                $this->params[$k] = $v;
            }
        }

        $act = $this->GetParam( 'act' );
        if( is_null( $act ) ) {
            return $this->JError( '未知操作' );
        }
        if( strcmp( $act, 'create' ) === 0 ) {
            return $this->JReturn( $this->Insert() );

        } else if( strcmp( $act, 'edit' ) === 0 ) {
            return $this->JReturn( $this->Edit() );

        } else if( strcmp( $act, 'delete' ) === 0 ) {
            return $this->JReturn( $this->Del() );

        } else if( strcmp( $act, 'remove' ) === 0 ) {
            return $this->JReturn( $this->Remove() );

        } else if( strcmp( $act, 'get' ) === 0 ) {
            return $this->JReturn( $this->Get() );

        } else if( strcmp( $act, 'list' ) === 0 ) {
            return $this->JReturn( $this->GetList() );
        }
        return $this->JError( '未知操作' );
    }
    //------------------------------------------------------------------------------------

}