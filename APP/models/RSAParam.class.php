<?php  //CODE BY ZMZ
// RSA加密的参数处理
require_once 'rsa.class.php';
class RSAParam {
    public function XLog( $msg = '',  //需要记录的信息
                                 $log_request_param = false ) { //记录GET 和 POST 参数
        $file = basename( $_SERVER['SCRIPT_FILENAME'] );
        $logfn = $_SERVER['DOCUMENT_ROOT'] . '/XLog.txt';
        $query = $_SERVER['QUERY_STRING'];
        $poststr = var_export( $_POST, true );

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

   
    public static function Decrypt() {
        $rawInput = file_get_contents( "php://input" );
        if( empty( $rawInput ) ) {
            return;
        }
        $pos = strpos( $rawInput, 'key=');
        if( $pos === false || $pos != 0 ) {
            //var_dump( $pos );
            return;
        }
        $rawInput = substr( $rawInput, strlen( 'key=' ) );
        $pos = strpos( $rawInput, '&' );
        if( $pos !== false && $pos > 0 ) {
            //exit( 'pos=' . $pos );
            $rawInput = substr( $rawInput, 0, $pos );
        }
        $rawInput = urldecode( $rawInput );
        RSAParam::XLog( $rawInput);
        //echo "<hr>";
        $rp = $_SERVER['DOCUMENT_ROOT'] . '/../Application/pem/';
        $rsa = new RSA( file_get_contents( $rp . 'rsa_private_key.pem' ),
                        file_get_contents( $rp . 'rsa_public_key.pem' ) );
        $ret = $rsa->Decrypt( $rawInput, 'private' );
        RSAParam::XLog( $ret); 
        $GLOBALS['RAWPOST'] = json_decode( $ret, true );
        //echo json_last_error() . "<br>\n";
        //var_dump( $GLOBALS['RAWPOST'] );
    }
}