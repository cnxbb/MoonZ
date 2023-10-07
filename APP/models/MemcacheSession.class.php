<?php  //CODE BY ZMZ
// 使用memcache 代替 php 的 session

class CMemcacheSession {
    private $memcache;  //memcache 对象
    private $ngseid;    //Nginx uid 的 cookie名称
    private $timeout;   //默认缓存时间

    public function __construct( $_memcache ) {
        $this->memcache = $_memcache;
        if( isset( $_COOKIE['ngseid'] ) ) {
            $this->ngseid = sprintf( "MSE_%s", md5( sprintf( "%s:%d_%s", $_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'], $_COOKIE['ngseid'] ) ) );
        } else {
            $this->ngseid = '';
        }
        //$this->timeout = 60 * 30; //30分钟
        $this->timeout = 60 * 60 * 24; //24小时
    }
    //------------------------------------------------------------------------------------

    public function Set( $key, $val ) {
        if( $this->ngseid == '' ) {
            return false;
        }
        $mdata = $this->memcache->get( $this->ngseid );
        if( $mdata === false ) {
            $data = [];
            $data[$key] = $val;
            return $this->memcache->set( $this->ngseid, $data, 0, $this->timeout );
        }
        $mdata[$key] = $val;
        return $this->memcache->set( $this->ngseid, $mdata, 0, $this->timeout );
    }
    //------------------------------------------------------------------------------------

    public function Get( $key = '' ) {
        if( $this->ngseid == '' ) {
            return false;
        }
        $mdata = $this->memcache->get( $this->ngseid );
        if( $mdata === false ) {
            return false;
        }
        if( $key == '' ) {
            return $mdata;
        }
        if( !isset( $mdata[$key] ) ) {
            return false;
        }
        $this->memcache->set( $this->ngseid, $mdata, 0, $this->timeout );
        return $mdata[$key];
    }
    //------------------------------------------------------------------------------------

    public function Del() {
        if( $this->ngseid == '' ) {
            return false;
        }
        $this->memcache->delete( $this->ngseid, 0 );
        return true;
    }
    //------------------------------------------------------------------------------------
}

