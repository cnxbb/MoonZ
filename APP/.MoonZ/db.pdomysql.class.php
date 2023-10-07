<?php  //CODE BY ZMZ
// Double_Dream_DB_PDO_MYSQL

@ini_set('memory_limit', '1024M');

/*
写操作日志表
create table if not exists OperationLog (
    m_ID int not null auto_increment COMMENT "ID",
    m_SQL text  COMMENT "Query String",
    m_Session text COMMENT "Session",
    m_UID int default 0 COMMENT "用户ID",
    m_Err int default 0 COMMENT "错误",
    m_CreateTime timestamp not null default current_timestamp,
    primary key ( m_ID )
) engine=myisam default charset=utf8 COMMENT "写操作日志";
*/

define( 'Create_Table_OperationLog', 'create table if not exists OperationLog ( m_ID int not null auto_increment COMMENT "ID", m_SQL text COMMENT "Query String", m_Session text COMMENT "Session", m_Err int default 0 COMMENT "错误号", m_CreateTime timestamp not null default current_timestamp, primary key ( m_ID ) ) engine=myisam default charset=utf8 COMMENT "写操作日志";');


class MZ_Pdo_Mysql {
    private $m_DB;
    private $m_LastSQL;
    private $m_SelectFieldArr;
    private $m_FromArr;
    private $m_WhereAndArr;
    private $m_WhereOrArr;
    private $m_OrderArr;
    private $m_Limit;
    private $m_DBErrorInfo;
    private $m_InbeginTransaction; //是已经调用了 beginTransaction
    //是否使用SQL语句错误日志文件
    private $m_EnableErrorLog;

    //是否记录写操作
    private $m_EnableWriteLog;

    //是否使用缓存
    private $m_UseCache;

    //缓存对象
    private $m_Cache;

    //是否输出调试信息
    private $m_Debug;

    //执行SQL语句错误时 退出并输出错误信息
    private $m_ExitOnError;

    //错误时的处理函数
    private $m_ErrorHandler;

    //发生错误是否调用 m_ErrorHandler 处理
    private $m_EnableErrorHandler;

    //写错误日志
    private function db_error_log( $sql ) {
        $this->m_DBErrorInfo = $this->m_DB->errorInfo();
        if( intval( $this->m_DBErrorInfo[0] ) > 0 ) {
            $str = print_r( $this->m_DBErrorInfo, true );
        } else {
            $str = '';
        }
        $dblogfile = defined( 'MZ_Global_DBLog_File' ) ? MZ_Global_DBLog_File : $_SERVER['DOCUMENT_ROOT'] . '/../APP/logs/db.error.log';
        $fh = fopen( $dblogfile, 'a+' );
        if( $fh === false ) {
            return;
        }
        fwrite( $fh, date( 'Y-m-d H:i:s' ) . "\n" );
        fwrite( $fh, $sql . "\n" );
        if( strlen( $str ) > 0 ) {
            fwrite( $fh, $str . "\n" );
        }
        fwrite( $fh, "//------------------------------------------------------------------------------------\n\n" );
        fclose( $fh );
    }
    //------------------------------------------------------------------------------------

    //清除相关缓存
    private function clear_memcache( $sql ) {
        preg_match_all( "/table_\w+[\s|,]|table_\w+$/", $sql, $out );
        if( isset( $out[0] ) && count( $out[0] ) > 0 ) {
            $out = $out[0];
            foreach( $out as &$o ) {
                $o = trim( $o );
            }
            $delkeys = array();
            $items = $this->m_Cache->getExtendedStats('items');
            foreach( $items as $k => $a ) {
                list( $cache_host, $cache_port ) = explode( ':', $k );
                $this->Debug( "cache_host=" . $cache_host );
                $this->Debug( "cache_port=" . $cache_port );
                if( is_array( $a['items'] ) ) {
                    foreach( $a['items'] as $kk => $b ) {
                        $this->Debug( "kk=" . $kk );
                        $block = $this->m_Cache->getExtendedStats('cachedump', $kk, 0 );
                        if( is_array( $block["{$cache_host}:{$cache_port}"] ) && count( $block["{$cache_host}:{$cache_port}"] ) > 0 ) {
                            foreach( $block["{$cache_host}:{$cache_port}"] as $mk => $mv ) {
                                foreach( $out as $o ) {
                                    if( strstr( $mk, $o ) ) {
                                        $delkeys[] = $mk;
                                    }
                                }
                            }

                        }

                    }
                }
            }
            foreach( $delkeys as $k ) {
                $this->m_Cache->delete( $k, 0 );
            }
        }
    }
    //------------------------------------------------------------------------------------

    //构造
    public function __construct( /* 数据库名 */     $_DBName,
                                 /* 数据库IP */     $_DBHost,
                                 /* 数据库用户名 */ $_DBUser,
                                 /* 密码 */         $_DBPassword,
                                 /* 端口 */         $_DBPort = 0,
                                 /* 缓存对象实例 */ $_Cache = NULL,
                                 /* 调试信息输出 */ $_Debug = false,
                                 /* 错误日志记录 */ $_EnableErrorLog = true,
                                 /* 写入操作日志 */ $_WriteLog = true,
                                 /* 使用缓存 */     $_UseCache = false,
                                 /* 错误时退出 */   $_ExitOnError = true
                                ) {

        $this->m_Cache = $_Cache;
        $this->m_LastSQL = "";
        $this->m_SelectFieldArr = array();
        $this->m_FromArr = array();
        $this->m_WhereAndArr = array();
        $this->m_WhereOrArr = array();
        $this->m_OrderArr = array();
        $this->m_Limit = "";

        $this->m_Debug = $_Debug;                    //默认关闭调试信息输出
        $this->m_EnableErrorLog = $_EnableErrorLog;  //默认打开错误日志记录
        $this->m_EnableWriteLog = $_WriteLog;        //默认打开写入操作日志
        $this->m_UseCache = $_UseCache;              //默认使用缓存
        $this->m_ExitOnError = $_ExitOnError;        //默认执行SQL语句错误时退出并输出错误信息
        $this->m_ErrorHandler = '';                  //发生错误时的处理函数 默认空
        $this->m_EnableErrorHandler = false;         //发生错误时调用处理函数 默认不调用
        $dsn = 'mysql:dbname=' . $_DBName . ';host=' . $_DBHost;
        if( intval( $_DBPort ) !== 0 ) {
            $dsn .= ';port=' . intval( $_DBPort );
        }
        try {
            $this->m_DB = new PDO( $dsn, $_DBUser, $_DBPassword);
            $this->m_DB->query( "set names utf8" );
            $this->m_DB->query( Create_Table_OperationLog );
        } catch ( PDOException $e ) {
            echo 'Connection failed: ' . $e->getMessage();
            exit;
        }
        $this->m_InbeginTransaction = false;

    }
    //------------------------------------------------------------------------------------

    //输出调试信息
    private function Debug( $str ) {
        if( $this->m_Debug ) {
            echo $str . "<br>\n";
        }
    }
    //打开关闭调试信息输出
    public function EnableDebug( $_enable = true ) {
        $this->m_Debug = ( bool )$_enable;
        return $this;
    }
    //------------------------------------------------------------------------------------

    //打开关闭错误日志
    public function EnableErrorLog( $_enable = true ) {
        $this->m_EnableErrorLog = ( bool )$_enable;
        return $this;
    }
    //------------------------------------------------------------------------------------

    //打开关闭写操作日志
    public function EnableWriteLog( $_enable = true ) {
        $this->m_EnableWriteLog = ( bool )$_enable;
        return $this;
    }
    //------------------------------------------------------------------------------------

    //打开关闭缓存使用
    public function EnableCache( $_enable = true ) {
        $this->m_UseCache = ( bool )$_enable;
        return $this;
    }
    //------------------------------------------------------------------------------------

    //打开关闭 执行SQL语句错误时退出并输出错误信息
    public function EnableExisOnError( $_enable = true ) {
        $this->m_ExitOnError = ( bool )$_enable;
        return $this;
    }
    //------------------------------------------------------------------------------------

    //设置如果执行SQL语句错误时的回调
    public function SetErrorHandler( $_handler ) {
        $this->m_ErrorHandler = $_handler;
    }
    //------------------------------------------------------------------------------------

    public function EnableErrorHandler( $_enable = true ) {
        $this->m_EnableErrorHandler = $_enable;
    }

    //获取最后执行的SQL语句
    public function LastSQL() {
        return $this->m_LastSQL;
    }
    //------------------------------------------------------------------------------------

    //获取数据库操作错误信息
    public function errorInfo() {
        if( $this->m_EnableErrorLog ) {
            return $this->m_DBErrorInfo;
        }
        return $this->m_DB->errorInfo();
    }
    //------------------------------------------------------------------------------------

    //执行SQL语句 返回结果集
    public function run( $sql,  $use_cache = NULL, $exit_on_error = NULL ) {
        $sql = trim( $sql );
        //记录错误信息到日志
        if( $this->m_EnableErrorLog ) {
            $this->db_error_log( $sql );
        }
        $this->Debug( "sql=" . $sql );
        //是否需要写入SQL操作记录
        $need = true;
        if( ( $ipos = stripos( $sql, 'select' ) ) === 0 ) {
            $this->Debug( "need = false" );
            $need = false;
        } else {
            $before = substr( $sql, 0, $ipos );
            if( trim( $before ) == '(' ) {
                $this->Debug( "need = false" );
                $need = false;
            } else {
                $this->Debug( 'need = true' );
            }
        }
        //如果需要记录SQL
        if( $need && $this->m_EnableWriteLog ) {
            $tpl = "insert into `OperationLog` ( m_SQL, m_Session, m_Err ) values ( '%s', '%s', '0' )";
            $log_sql = sprintf( $tpl, addslashes( $sql ), '' );
            $this->Debug( $log_sql );
            $this->m_DB->query( $log_sql );
            $logid = $this->m_DB->lastInsertId();
            $this->Debug( $log_sql );
        }

        //如果不是写入动作 同时 带有使用缓存标记 同时 存在缓存对象
        if( !$need &&
            $this->m_Cache &&
            ( $use_cache === true || ( is_null( $use_cache ) && $this->m_UseCache === true ) ) ) {
            //取相关表名称
            $out = array();
            preg_match_all( "/table_\w+[\s|,]|table_\w+$/", $sql, $out );
            if( isset( $out[0] ) && count( $out[0] ) > 0 ) {
                foreach( $out[0] as &$item ) {
                    $item = trim( $item );
                }
                $memkey = sprintf( "%s_%s_%s", 'XDBV3_CACHE' ,implode( '_', $out[0] ) , md5( $sql ) );
                $cache_ret = $this->m_Cache->get( $memkey );
                $this->Debug( "memkey=" . $memkey );
                if( $cache_ret !== false ) {
                    $this->Debug( "use cache" );
                    return $cache_ret;
                }
            }
        }
        if( strstr( $sql,'show tables') ) {
            $need = false;
        }

        //执行SQL
        if( $need ) {
            $ret = $this->m_DB->exec( $sql );
            //var_dump( $ret );
            //exit;
            $this->m_LastSQL = $sql;
            //记录更改数据的SQL语句
            if( $ret === FALSE ) {

                //记录操作错误到 写操作日志
                if( $need && $this->m_EnableWriteLog ) {
                    $this->m_DB->query( "update `OperationLog` set m_Err = '1' where m_ID = '{$logid}'" );
                }
                if( $exit_on_error === true || ( is_null( $exit_on_error ) && $this->m_ExitOnError === true ) ) {
                    if( $this->m_EnableErrorHandler &&
                        strlen( $this->m_ErrorHandler ) > 0  &&
                        function_exists( $this->m_ErrorHandler ) &&
                        is_callable( $this->m_ErrorHandler ) &&
                        !$this->m_InbeginTransaction ) {
                        ($this->m_ErrorHandler)();
                        exit;
                    }
                    $ret['ret'] = 0;
                    $ret['errmsg'] = '数据库错误';
                    $ret['sql'] = $sql;
                    $ret['dberrinfo'] = $this->m_DB->errorInfo();
                    echo json_encode( $ret );
                    exit;
                }
                return false;
            }
        } else {
            $sth = $this->m_DB->query( $sql );
            //记录SQL语句
            $this->m_LastSQL = $sql;
            if( $sth === false ) {
                //记录操作错误到 写操作日志
                if( $need && $this->m_EnableWriteLog ) {
                    $this->m_DB->query( "update `OperationLog` set m_Err = '1' where m_ID = '{$logid}'" );
                }
                if( $exit_on_error === true || ( is_null( $exit_on_error ) && $this->m_ExitOnError === true ) ) {
                    if( $this->m_EnableErrorHandler &&
                        strlen( $this->m_ErrorHandler ) > 0  &&
                        function_exists( $this->m_ErrorHandler ) &&
                        is_callable( $this->m_ErrorHandler ) &&
                        !$this->m_InbeginTransaction ) {
                        ($this->m_ErrorHandler)();
                        exit;
                    }
                    $ret['ret'] = 0;
                    $ret['errmsg'] = '数据库错误';
                    $ret['sql'] = $sql;
                    $ret['dberrinfo'] = $this->m_DB->errorInfo();
                    echo json_encode( $ret );
                    exit;
                }
                return false;
            }
            $ret = $sth->fetchAll( PDO::FETCH_ASSOC );
        }
        //记录数据到缓存
        if( !$need &&
            $this->m_Cache &&
            ( $use_cache === true || ( is_null( $use_cache ) && $this->m_UseCache === true ) ) ) {
            $this->m_Cache->set( $memkey, $ret, 0, 0 );
        }

        //如果是写入操作清除相关缓存
        if( $need && $this->m_Cache ) {
            $this->Debug( "clear memcache" );
            $this->clear_memcache( $sql );
        }
        return $ret;
    }
    //------------------------------------------------------------------------------------

    //取最后插入的数据ID
    public function LastID() {
        return $this->m_DB->lastInsertId();
    }
    //------------------------------------------------------------------------------------

    public function Field( $field_str ) {
        if( !in_array( $field_str, $this->m_SelectFieldArr ) ) {
            $this->m_SelectFieldArr[] = $field_str;
        }
        return $this;
    }
    //------------------------------------------------------------------------------------

    public function From( $from_item ) {
        if( !in_array( $from_item, $this->m_FromArr ) ) {
            $this->m_FromArr[] = $from_item;
        }
        return $this;
    }
    //------------------------------------------------------------------------------------

    public function Where( $where_item, $isand = true ) {
        if( $isand ) {
            if( !in_array( $where_item, $this->m_WhereAndArr ) ) {
                $this->m_WhereAndArr[] = $where_item;
            }
        } else {
            if( !in_array( $where_item, $this->m_WhereOrArr ) ) {
                $this->m_WhereOrArr[] = $where_item;
            }
        }
        return $this;
    }
    //------------------------------------------------------------------------------------

    public function OrderBy( $order_item ) {
        if( !in_array( $order_item, $this->m_OrderArr ) ) {
            $this->m_OrderArr[] = $order_item;
        }
        return $this;

    }
    //------------------------------------------------------------------------------------

    public function Limit( $limit_str ) {
        $this->m_Limit = $limit_str;
        return $this;
    }
    //------------------------------------------------------------------------------------

    public function Select( $use_cache = NULL,
                            $exit_on_error = NULL
                          ) {
        if( count( $this->m_FromArr ) == 0 ) {
            return false;
        }
        $sql = "";
        if( count( $this->m_SelectFieldArr ) == 0 ) {
            $strField = "*";
        } else {
            $strField = implode( " , ", $this->m_SelectFieldArr );
        }

        $strFrom = implode( ", ", $this->m_FromArr );

        $strWhere = "";
        if( count( $this->m_WhereAndArr ) > 0 ) {
            $strWhere = implode( " and ", $this->m_WhereAndArr );
        }
        if( count( $this->m_WhereOrArr ) > 0 ) {
            if( strlen( $strWhere ) > 0 ) {
                $strWhere = sprintf( "%s or %s", $strWhere, implode( " or ", $this->m_WhereOrArr ) );
            } else {
                $strWhere = implode( " or ", $this->m_WhereOrArr );
            }
        }

        $strOrder = "";
        if( count( $this->m_OrderArr ) > 0 ) {
            $strOrder = implode( " , ", $this->m_OrderArr );
        }

        $strLimit = "";
        $strLimit = $this->m_Limit;

        $sql = "select {$strField} from {$strFrom} ";
        if( strlen( $strWhere ) > 0 ) {
            $sql .= " where {$strWhere}";
        }
        if( strlen( $strOrder ) > 0 ) {
            $sql .= " order by {$strOrder}";
        }
        if( strlen( $strLimit ) > 0 ) {
            $sql .= " Limit {$strLimit}";
        }
        return $this->run( $sql, $use_cache, $exit_on_error );
    }
    //------------------------------------------------------------------------------------

    public function Clear() {
        $this->m_SelectFieldArr = array();
        $this->m_FromArr = array();
        $this->m_WhereAndArr = array();
        $this->m_WhereOrArr = array();
        $this->m_OrderArr = array();
        $this->m_Limit = "";
        return $this;
    }
    //------------------------------------------------------------------------------------

    public function GetMemcache() {
        return $this->m_Cache;
    }
    //------------------------------------------------------------------------------------


    public function getPDOObject() {
        return $this->m_DB;
    }
    //------------------------------------------------------------------------------------

    public function beginTransaction() {
        try {
            $this->m_DB->beginTransaction();
            $this->m_InbeginTransaction = true;
        } catch ( Exception $e ) {
            throw e;
        }
    }
    //------------------------------------------------------------------------------------

    public function exec( $sql, $exit_on_error = NULL ) {
        try {
            $ret = $this->m_DB->exec( $sql );
            if( $ret === false ) {
                if( $exit_on_error === true || ( is_null( $exit_on_error ) && $this->m_ExitOnError === true ) ) {
                    if( $this->m_EnableErrorHandler &&
                        strlen( $this->m_ErrorHandler ) > 0  &&
                        function_exists( $this->m_ErrorHandler ) &&
                        is_callable( $this->m_ErrorHandler ) &&
                        !$this->m_InbeginTransaction ) {
                        ($this->m_ErrorHandler)();
                        exit;
                    }
                    $ret['ret'] = 0;
                    $ret['errmsg'] = '数据库错误';
                    $ret['sql'] = $sql;
                    $ret['dberrinfo'] = $this->m_DB->errorInfo();
                    echo json_encode( $ret );
                    exit;
                }
            }
            return $ret;
        } catch ( Exception $e ) {
            throw e;
        }
    }
    //------------------------------------------------------------------------------------

    public function rollBack() {
        $this->m_DB->rollBack();
        $this->m_InbeginTransaction = false;
    }
    //------------------------------------------------------------------------------------

    public function commit() {
        $this->m_InbeginTransaction = false;
        return $this->m_DB->commit();
    }
    //------------------------------------------------------------------------------------

    public function lock( $table, $op = 'WRITE' ) {
        $tpl = "lock tables %s %s;";
        $this->m_DB->query( sprintf( $tpl, $table, $op ) );
    }

    public function unlock() {
        $this->m_DB->query( 'unlock tables;' );
    }


}