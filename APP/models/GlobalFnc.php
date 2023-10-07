<?php  //CODE BY ZMZ
// 全局函数
require_once 'GlobalDefine.php';

//改变文件的后缀名
function chfnext( $_fn, $_ext ) {
    $ipos = strrpos( $_fn, '.' );
    if( $ipos === false ) {
        return sprintf( "%s.%s", $_fn, $_ext );
    }
    return sprintf( "%s.%s", substr( $_fn, 0, $ipos ), $_ext );
}
//------------------------------------------------------------------------------------

//ajax判断
function isAjax() {
    if( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) &&
        strcmp( strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ), 'xmlhttprequest' ) == 0
      ) {
        return true;
    }
    return false;
}
//------------------------------------------------------------------------------------

//下载文件
function urldownload($url,$filename=''){
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
    $resp = curl_exec( $ch );
    curl_close( $ch );
    if(strlen($filename)>0){
        file_put_contents($filename,$resp);
    }else{
        return $resp;
    }
}
//------------------------------------------------------------------------------------

function IsUType( $_type ) {
    if( !isset( $_SESSION['utype'] ) ) {
        return false;
    }
    return intval( $_SESSION['utype'] ) === intval( $_type ) ? true : false;
}
//------------------------------------------------------------------------------------

//CURL请求数据
function curl_get( $_url, $_timeout = 0 ) {
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $_url );
    if( $_timeout !== 0 ) {
        curl_setopt( $ch, CURLOPT_TIMEOUT, $_timeout );
    }
    curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
    $resp = curl_exec( $ch );
    curl_close( $ch );
    return $resp;
}
function curl_post( $_url, $_post ) {
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $_url );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);  // 对认证证书来源的检查   // https请求 不验证证书和hosts
    curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
    curl_setopt( $ch, CURLOPT_POST, true );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $_post );     // Post提交的数据包
    $resp = curl_exec( $ch );
    curl_close( $ch );
    echo $resp;
    exit;
}
//------------------------------------------------------------------------------------

function PostRequest( $_url, $_post, $_timeout = 0 ) {
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $_url );
    if( $_timeout !== 0 ) {
        curl_setopt( $ch, CURLOPT_TIMEOUT, $_timeout );
    }
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);  // 对认证证书来源的检查   // https请求 不验证证书和hosts
    curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
    curl_setopt( $ch, CURLOPT_POST, true );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $_post );     // Post提交的数据包
    $resp = curl_exec( $ch );
    curl_close( $ch );
    return $resp;
}

function rangemax( $_arr ) {
    $m = max( $_arr );
    if( $m < 10 ) {
        return 10;
    }
    $length = strlen( strval( intval( $m ) ) );

    $delta = 1;
    while( strlen( $delta ) < $length ) {
        $delta = intval( strval( $delta ) . '0' );
    }
    $fi = intval( substr( strval( $m ), 0, 1 ) );
    return $fi * pow( 10, $length - 1 ) + $delta;
}
//------------------------------------------------------------------------------------

//文件绝对路径 -> URL
function fn2url( $_fn, $FullUrl = false /* 默认不带协议和域名 */ ) {
    if( strpos( $_fn, $_SERVER['DOCUMENT_ROOT'] ) !== 0 ) {
        return '';
    }
    if( $FullUrl === false ) {
        return str_replace( $_SERVER['DOCUMENT_ROOT'], '', $_fn );
    }
    return str_replace( $_SERVER['DOCUMENT_ROOT'],
                        sprintf( "%s://%s", $_SERVER['REQUEST_SCHEME'], $_SERVER['HTTP_HOST'] ),
                        $_fn );
}
//------------------------------------------------------------------------------------

//URL -> 文件绝对路径
function url2fn( $_url ) {
    $pos1 = stripos( $_url, 'http' );
    $pos2 = stripos( $_url, '/' );
    if( $pos1 !== 0 && $pos2 !== 0 ) {
        return '';
    }
    if( $pos1 === 0 ) { //已http 或 https 开头
        $search = sprintf( "%s://%s", $_SERVER['REQUEST_SCHEME'], $_SERVER['HTTP_HOST'] );
        return str_replace( $search, $_SERVER['DOCUMENT_ROOT'], $_url );
    }
    if( $pos2 === 0 ) { //URI 不带协议和域名
        return $_SERVER['DOCUMENT_ROOT'] . $_url;
    }
    return '';
}
//------------------------------------------------------------------------------------

//调用数据库 默认错误处理函数
function Default_Database_Error_Handler() {
    http_response_code(500);
    if( defined( 'MZ_Error_Page_500' ) && file_exists( MZ_Error_Page_500 ) ) {
        $GLOBALS['errstr'] = '数据库错误';
        require_once MZ_Error_Page_500;
    }
    exit;
}
//------------------------------------------------------------------------------------

//输出DUC形式的图片base64编码数据
function DUCImage( $fn, $path = '' ) {
    if( $path != '' ) {
        $fn = $path . $fn;
    }
    if( !file_exists( $fn ) ) {
        return;
    }
    echo 'data:image/png;base64,' . base64_encode( file_get_contents( $fn ) );
}

