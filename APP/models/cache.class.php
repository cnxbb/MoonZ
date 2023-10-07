<?php  //CODE BY ZMZ
// 缓存管理
require_once 'model.base.class.php';

class Ccache extends XModel{
    private $cache_file_path;
    public function __construct( $_param, $_db, $_memcache ) {
        parent::__construct( $_param, $_db, $_memcache );
    }
    //-------------------------------------------- ----------------------------------------

    //获取缓存
    private function get_from_mem( $_key ) {
        if( !$this->memcache && !( $this->memcache instanceof Memcache ) ) {
            return false;
        }
        return $this->memcache->get( $_key );
    }
    //------------------------------------------------------------------------------------
    private function get_from_file( $_key ) {
        $fn = CACHE_FILE_PATH . $_key;
        if( !file_exists( $fn ) ) {
            return false;
        }
        $r = unserialize( file_get_contents( $fn ) );
        if( intval( $r['expire'] ) != 0 && intval( $r['t'] ) > 0 && intval( $r['t'] ) + intval( $r['expire'] ) < time() ) {
            //过期
            if( file_exists( $fn ) ) {
                unlink( $fn );
            }
            return false;
        }
        return isset( $r['data'] ) ? $r['data'] : false;
    }
    //------------------------------------------------------------------------------------
    public function get( $_key, $_type = CACHE_MEM ) {
        return $_type == CACHE_MEM ? $this->get_from_mem( $_key ) : $this->get_from_file( $_key );
    }
    //------------------------------------------------------------------------------------

    //设置缓存
    private function set_to_mem( $_key, $_val, $_expire ) {
        if( !$this->memcache && !( $this->memcache instanceof Memcache ) ) {
            return false;
        }
        return $this->memcache->set( $_key, $_val, 0, intval( $_expire ) );

    }
    //------------------------------------------------------------------------------------
    private function set_to_file( $_key, $_val, $_expire ) {
        $r['expire'] = $_expire;
        $r['t'] = time();
        $r['data'] = $_val;
        $fn = CACHE_FILE_PATH . $_key;
        return file_put_contents( $fn, serialize( $r ) );
    }
    //------------------------------------------------------------------------------------
    public function set( $_key, $_val, $_expire = 0, $_type = CACHE_MEM ) {
        return $_type == CACHE_MEM ? $this->set_to_mem( $_key, $_val, $_expire ) : $this->set_to_file( $_key, $_val, $_expire );
    }
    //------------------------------------------------------------------------------------

    //删除单个缓存
    private function del_from_mem( $_key ) {
        if( !$this->memcache && !( $this->memcache instanceof Memcache ) ) {
            return false;
        }
        return $this->memcache->delete( $_key, 0 );
    }
    //------------------------------------------------------------------------------------
    private function del_from_file( $_key ) {
        $fn = CACHE_FILE_PATH . $_key;
        if( file_exists( $fn ) ) {
            unlink( $fn );
        }
        return true;
    }
    //------------------------------------------------------------------------------------
    public function del( $_key, $_type = CACHE_MEM ) {
        return $_type == CACHE_MEM ? $this->del_from_mem( $_key ) : $this->del_from_file( $_key );
    }
    //------------------------------------------------------------------------------------

    //匹配关键字 删除多个缓存
    private function batchdel_from_mem( $_keyword ) {
        if( strlen( trim( $_keyword ) ) == 0 ) {
            return false;
        }
        if( !$this->memcache && !( $this->memcache instanceof Memcache ) ) {
            return false;
        }
        if( !defined( 'MZ_MEMCACHE_Host' ) || !defined( 'MZ_MEMCACHE_Port' ) ) {
            return false;
        }
        $items = $this->memcache->getExtendedStats('items');
        $mkey = sprintf( '%s:%s', MZ_MEMCACHE_Host, MZ_MEMCACHE_Port );
        if(!isset($items[$mkey]['items'])) {
			return false;
		}
        $items = $items[$mkey]['items'];
        foreach( $items as $k => $v ) {
            $str = $this->memcache->getExtendedStats( 'cachedump', $k, 0 );
            $line = $str[$mkey];
            if( is_array( $line ) && count( $line ) > 0 ) {
                foreach( $line as $key => $value ) {
                    if( strstr( $key, $_keyword ) !== false ) {
                        $this->memcache->delete( $key, 0 );
                    }
                }
            }
        }
        return true;
    }
    //------------------------------------------------------------------------------------
    private function batchdel_from_file( $_keyword ) {
        if( strlen( trim( $_keyword ) ) == 0 ) {
            return false;
        }
        $hDir = opendir( CACHE_FILE_PATH );
        if( $hDir === false ) {
            return false;
        }
        while( false !== ( $file = readdir( $hDir ) ) ) {
            if( $file == '.' || $file == '..' ) {
                continue;
            }
            if( strstr( $file, $_keyword ) !== false ) {
                $fn = CACHE_FILE_PATH . $file;
                if( file_exists( $fn ) ) {
                    unlink( $fn );
                }
            }
        }
        closedir( $hDir );
        return true;
    }
    //------------------------------------------------------------------------------------
    public function batchdel( $_keyword, $_type = CACHE_MEM ) {
        return $_type == CACHE_MEM ? $this->batchdel_from_mem( $_keyword ) : $this->batchdel_from_file( $_keyword );
    }
    //------------------------------------------------------------------------------------

    //清空所有缓存
    private function clear_from_mem() {
        if( !$this->memcache && !( $this->memcache instanceof Memcache ) ) {
            return false;
        }
        return $this->memcache->flush();
    }
    //------------------------------------------------------------------------------------
    private function clear_from_file() {
        $hDir = opendir( CACHE_FILE_PATH );
        if( $hDir === false ) {
            return false;
        }
        while( false !== ( $file = readdir( $hDir ) ) ) {
            if( $file == '.' || $file == '..' ) {
                continue;
            }
            $fn = CACHE_FILE_PATH . $file;
            if( file_exists( $fn ) ) {
                unlink( $fn );
            }
        }
        closedir( $hDir );
        return true;
    }
    //------------------------------------------------------------------------------------
    public function clear( $_type = CACHE_MEM ) {
        return $_type == CACHE_MEM ? $this->clear_from_mem() : $this->clear_from_file();
    }
    //------------------------------------------------------------------------------------

    // 函数说明
    public function getkeys( $pre_keyword ) {
        if( strlen( trim( $pre_keyword ) ) == 0 ) {
            return false;
        }
        if( !$this->memcache && !( $this->memcache instanceof Memcache ) ) {
            return false;
        }
        if( !defined( 'MZ_MEMCACHE_Host' ) || !defined( 'MZ_MEMCACHE_Port' ) ) {
            return false;
        }
        $items = $this->memcache->getExtendedStats('items');
        $mkey = sprintf( '%s:%s', MZ_MEMCACHE_Host, MZ_MEMCACHE_Port );
        if(!isset($items[$mkey]['items'])) {
			return false;
		}
        $items = $items[$mkey]['items'];
        $arr = array();
        foreach( $items as $k => $v ) {
            $str = $this->memcache->getExtendedStats( 'cachedump', $k, 0 );
            $line = $str[$mkey];
            if( is_array( $line ) && count( $line ) > 0 ) {
                foreach( $line as $key => $value ) {
                    if( strpos( $key, $pre_keyword ) === 0 ) {
                        $arr[] = $key;
                    }
                }
            }
        }
        return $arr;
    }
    //------------------------------------------------------------------------------------
}



