<?php  //CODE BY ZMZ
// 利用文件系统的操作锁
class CFSLock {
    private $m_lock_fn;
    private $hfile;
    public function __construct( $_lock_fn ) {
        $this->m_lock_fn = $_lock_fn;
        $this->hfile = false;
    }
    //------------------------------------------------------------------------------------

    public function __destruct() {
        if( $this->hfile ) {
            flock( $this->hfile, LOCK_UN );
            fclose( $this->hfile );
            $this->hfile = false;
        }
    }

    public function lock() {
        $this->hfile = fopen( $this->m_lock_fn, 'a+' );
        if( $this->hfile === false ) {
            return false;
        }
        flock( $this->hfile, LOCK_EX );
        return true;
    }

    public function unlock() {
        if( $this->hfile === false ) {
            return false;
        }
        flock( $this->hfile, LOCK_UN );
        fclose( $this->hfile );
        $this->hfile = false;
        return true;
    }

}

