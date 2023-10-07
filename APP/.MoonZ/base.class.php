<?php  //CODE BY ZMZ
// MoonZ Base Class

trait CanDynamicCall {
    /**
     * 自动调用类中存在的方法
     */
    public function __call($name, $args) {
        if(is_callable($this->$name)){
            return call_user_func($this->$name, $args);
        }else{
            throw new RuntimeException("Method {$name} does not exist");
        }
    }
    /**
     * 添加方法
     */
    public function __set($name, $value) {
        $this->$name = is_callable($value) ? $value->bindTo($this, $this) : $value;
    }
}


class MObject {
    use CanDynamicCall;

    public function __construct() {

    }
    //------------------------------------------------------------------------------------

    /*
    public function __set( $name, $value ) {
        $this->$name = $value;
    }
    //------------------------------------------------------------------------------------

    public function __call( $name, $arguments ) {
        //注意:没用形参$name
        return call_user_func($this->$name,$arguments);//通过这个把属性的匿名方法加进来 注意:$arguments 是一个数组
    }
    //------------------------------------------------------------------------------------
    */

    static public function ZLog( $msg = '',  //需要记录的信息
                                 $log_request_param = false ) { //记录GET 和 POST 参数
                            //获取文件名
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
            foreach( $z as $row ) {
                if( isset( $row['file'] ) ) {
                    $logstr .= sprintf( "文件:%s\t", $row['file'] );
                }
                if( isset( $row['line'] ) ) {
                    $logstr .= sprintf( "行:%d\t", $row['line'] );
                }
                if( isset( $row['function'] ) ) {
                    $logstr .= sprintf( "调用方法:%s", $row['function'] );
                }

                $logstr .= "\n";
            }
            $logstr .= "File: " . $file . "\nQuery: " . $query . "\nPost: " . $poststr . "\n";
            $logstr .= "Raw input:\n" . file_get_contents( "php://input" ) . "\n";
            if( isset( $GLOBALS['RAWPOST'] ) ) {
                $logstr .= var_export( $GLOBALS['RAWPOST'], true ) . "\n";
            }
        }
        if( isset( $msg ) ) {
            if( is_string( $msg ) ) {
                $logstr .= "Msg: " . $msg;
            } else {
                $logstr .= "Msg: " . var_export( $msg, true ) ;
            }
        }

        $logstr .= "\n\n";
        $f = fopen( $logfn, "a+" );
        if( $f !== false ) {
            fwrite( $f, $logstr );
            fclose( $f );
        }
    }
    //------------------------------------------------------------------------------------

    /*
        获取参数
        type: 获取来源 rsa ps post get cookie session all
        默认获取来源顺序 rsa加密参数 ps伪静态 post get cookie session
    */
    //第一个参数是什么，第二个参数是什么 type是传参类型
    public function GetParam( $key, $def='', $type='all' ) {
        static $psarr = null;
        if( $type == 'rsa' ) {
            return isset( $GLOBALS['RAWPOST'][$key] ) ? $GLOBALS['RAWPOST'][$key] : $def;
        }
        if( $type == 'ps' ) {
            if( !$psarr ) {
                $arr = explode( '/', $_SERVER['REQUEST_URI'] );
                $i=3;
                while( isset( $arr[$i] ) && isset( $arr[$i+1] ) ) {
                    $psarr[$arr[$i]] = $arr[$i+1];
                    $i+=2;
                }

            }
            return isset( $psarr[$key] ) ? $psarr[$key] : $def;
        }

        if( $type == 'post' ) {
            return isset( $_POST[$key] ) ? $_POST[$key] : $def;
        }
        if( $type == 'get' ) {
            return isset( $_GET[$key] ) ? $_GET[$key] : $def;
        }
        if( $type == 'cookie' ) {
            return isset( $_COOKIE[$key] ) ? $_COOKIE[$key] : $def;
        }
        if( $type == 'session' ) {
            return isset( $_SESSION[$key] ) ? $_SESSION[$key] : $def;
        }
        if( $type == 'all' ) {
            $r = isset( $GLOBALS['RAWPOST'][$key] ) ? $GLOBALS['RAWPOST'][$key] : $def;

            if( !$psarr ) {
                $arr = explode( '/', $_SERVER['REQUEST_URI'] );
                $i=3;
                while( isset( $arr[$i] ) && isset( $arr[$i+1] ) ) {
                    $psarr[$arr[$i]] = $arr[$i+1];
                    $i+=2;
                }

            }
            $p = isset( $_POST[$key] ) ? $_POST[$key] : $def;

            $ps = isset( $psarr[$key] ) ? $psarr[$key] : $def;

            $g = isset( $_GET[$key] ) ? $_GET[$key] : $def;

            $c = isset( $_COOKIE[$key] ) ? $_COOKIE[$key] : $def;

            $s = isset( $_SESSION[$key] ) ? $_SESSION[$key] : $def;

            if( $r != NULL ) {
                return $r;
            }

            if( $p != NULL ) {
                return $p;
            }

            if( $ps != NULL ) {
                return $ps;
            }

            if( $g != NULL ) {
                return $g;
            }
            if( $c != NULL ) {
                return $c;
            }
            if( $s != NULL ) {
                return $s;
            }
            return $def;
        }
        return $def;
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
            if( !isset( $json['ret'] ) ) {
                $json['ret'] = 1;
            }
            return $json;
        }
    }
    //------------------------------------------------------------------------------------

    public function JError( $errmsg ) {
        $ret['ret'] = 0;
        $ret['errmsg'] = $errmsg;
        return $ret;
    }


}